<?php
$ROLE = "";

include_once("./data.php");

/*
   Return codes:
   -1: Missing arg `cmd`.
   -2: Missing arg `code`.
   -3: Invalid code.
   -4: Unknown command.
   -5: Missing arg `patient`.
 */
function execService( $args ) {
    if( !array_key_exists( 'cmd', $args ) ) return -1;
    $cmd = $args['cmd'];
    if( !array_key_exists( 'code', $args ) ) return -2;
    $code = $args['code'];

    $items = parseCode( $code );
    if( $items == 0 ) return -3;
    $carecenterId = $items["id"];
    $secretCode = $items["code"];

    switch( $cmd ) {
        case 'status':
            return execStatus( $carecenterId );
        case 'update':
            if( !array_key_exists( 'patient', $args ) ) return -5;
            $patient = $args['patient'];
            return execUpdate( $carecenterId, $patient );
        default:
            return -4;
    }
}


function execUpdate( $carecenterId, &$patient ) {
    $patientId = getPatientId( $carecenterId, $patient );
    updateData( $patientId, $patient['data'] );
    updateAdmissions( $patientId, $patient['admissions'] );

    return 0;
}

function updateAdmissions( $patientId, &$admissions ) {
    if( !$admissions || !is_array($admissions) ) return;
    foreach( $admissions as $admission ) {
        $enter = intval($admission['enter']);
        if( array_key_exists( 'exit', $admission ) ) {
            $exit = intval($admission['exit']);
        }
        else {
            $exit = 0;
        }
        $admissionId = getAdmissionId( $patientId, $enter );
        \Data\Admission\upd( $admissionId, ['enter' => $enter, 'exit' => $exit] );
        $consultations = $admission['visits'];
        if( is_array( $consultations ) ) {
            updateConsultations( $admissionId, $consultations );
        }
        else {
            error_log("Visits: " . json_encode($consultations));
        }
    }
}

function updateConsultations( $admissionId, &$consultations ) {
    if( !$consultations || !is_array($consultations) ) return;
    foreach( $consultations as $consultation ) {
        $enter = intval($consultation['enter']);
        if( array_key_exists( 'exit', $consultation ) ) {
            $exit = intval($consultation['exit']);
        }
        else {
            $exit = 0;
        }
        $consultationId = getConsultationId( $admissionId, $enter );
        \Data\Consultation\upd( $consultationId, ['enter' => $enter, 'exit' => $exit] );
        $data = $consultation['data'];
        updateConsultationData( $consultationId, $data );
    }
}

function updateConsultationData( $consultationId, &$data ) {
    if( !$data || !is_array( $data ) ) return;
    foreach( $data as $key => $value ) {
        \Data\begin();
        try {
            \Data\query("DELETE FROM" . \Data\Data\name()
                      . "WHERE `key`=? AND `consultation`=?",
                        $key, $consultationId);
            $fieldId = \Data\Data\add([
                'key' => $key, 'value' => $value
            ]);
            \Data\Data\linkConsultation( $fieldId, $consultationId );
            \Data\commit();
        }
        catch( Exception $ex ) {
            \Data\rollback();
            error_log("[synchro/updateConsultationData( $consultationId, data )] data = " . json_encode( $data ));
            error_log($ex->getMessage());
        }
    }
}

function getAdmissionId( $patientId, $enter ) {
    \Data\begin();
    try {
        $id = 0;
        $stm = \Data\query("SELECT id FROM" . \Data\Admission\name()
                         . "WHERE `patient`=? AND `enter`=?",
                           $patientId, $enter);
        if( $stm ) {
            $row = $stm->fetch();
            if( $row ) $id = intval( $row['id'] );
        }
        if( $id == 0 ) {
            $id = \Data\Admission\add([
                'enter' => $enter,
                'exit' => 0
            ]);
            \Data\Admission\linkPatient( $id, $patientId );
        }
        \Data\commit();

        return $id;
    }
    catch( Exception $ex ) {
        \Data\rollback();
        error_log("[synchro/getAdmissionId( $patientId, $enter )]: " . $ex->getMessage());
        throw $ex;
    }
}

function getConsultationId( $admissionId, $enter ) {
    \Data\begin();
    try {
        $id = 0;
        $stm = \Data\query("SELECT id FROM" . \Data\Consultation\name()
                         . "WHERE `admission`=? AND `enter`=?",
                           $admissionId, $enter);
        if( $stm ) {
            $row = $stm->fetch();
            if( $row ) $id = intval( $row['id'] );
        }
        if( $id == 0 ) {
            $id = \Data\Consultation\add([ 'enter' => $enter, 'exit' => 0 ]);
            \Data\Consultation\linkAdmission( $id, $admissionId );
        }
        \Data\commit();

        return $id;
    }
    catch( Exception $ex ) {
        \Data\rollback();
        error_log("[synchro/getConsultationId( $admissionId, $enter )]: " . $ex->getMessage());
        throw $ex;
    }
}

function updateData( $patientId, &$data ) {
    if( !$data ) return;
    foreach( $data as $key => $value ) {
        \Data\begin();
        try {
            \Data\query("DELETE FROM" . \Data\PatientField\name()
                      . "WHERE `key`=? AND `patient`=?",
                        $key, $patientId);
            $fieldId = \Data\PatientField\add([
                'key' => $key, 'value' => $value
            ]);
            \Data\PatientField\linkPatient( $fieldId, $patientId );
            \Data\commit();
        }
        catch( Exception $ex ) {
            \Data\rollback();
            error_log("[synchro/updateData( $patientId, data )] data = " . json_encode( $data ));
            error_log($ex->getMessage());
        }
    }
}

/**
 * Get the patient id. If such a patient does not exist, create it and return its id.
 */
function getPatientId( $carecenterId, &$patient ) {
    \Data\begin();
    try {
        $id = 0;
        $stm = \Data\query("SELECT id, `edited` FROM" . \Data\Patient\name()
                         . "WHERE `carecenter`=? AND `key`=?",
                           $carecenterId, $patient['id']);
        if( $stm ) {
            $row = $stm->fetch();
            if( $row ) {
                $id = intval( $row['id'] );
                \Data\Patient\upd($id, [ 'edited' => intval( $patient['edited'] ) ]);
            }
        }
        if( $id == 0 ) {
            $id = \Data\Patient\add([
                'key' => $patient['id'],
                'edited' => intval( $patient['edited'] )
            ]);
            \Data\Patient\linkCarecenter( $id, $carecenterId );
        }
        \Data\commit();

        return $id;
    }
    catch( Exception $ex ) {
        \Data\rollback();
        error_log("[synchro/getParentId( $carecenterId, patient )] patient = " . json_encode( $patient ));
        error_log($ex->getMessage());
        throw $ex;
    }
}

/**
 * {
 *   patients: {
 *     Xq14: {
 *       admissions: [
 *         {
 *           enter: 1501239017,
 *           exit: ...
 *           visits: [
 *             {
 *               date: 1501239017
 *             },
 *             ...
 *           ]
 *         },
 *         ...
 *       ]
 *     },
 *     ...
 *   },
 *   structure: {
 *     exams: ..., vaccins: ..., patient: ..., forms: ..., types: ...
 *   }
 * }
 */
function execStatus( $carecenterId ) {
    $structureId = \Data\Carecenter\getStructure( $carecenterId );
    $structure = \Data\Structure\get( $structureId );

    $patients = null;
    $patientIds = \Data\Carecenter\getPatients( $carecenterId );
    foreach( $patientIds as $patientId ) {
        if( $patients == null ) $patients = [];
        $patient = \Data\Patient\get( intval($patientId) );
        $patients[$patient['key']] = [ 'edited' => intval($patient['edited']) ];
        $admissions = getAdmissions( $patientId );
        if( $admissions != null ) {
            $patients[$patient['key']]['admissions'] = $admissions;
        }
    }
    return [
        'patients' => $patients,
        'structure' => $structure
    ];
}


function getAdmissions( $patientId ) {
    $stm = \Data\query("SELECT id, `enter`, `exit` FROM"
                     . \Data\Admission\name()
                     . "WHERE `patient`=? ORDER BY `enter` DESC LIMIT 0,1",
                       $patientId);
    if( !$stm ) return null;
    $row = $stm->fetch();
    if( !$row ) return null;


    $admission = [
        'enter' => intval($row['enter']),
        'exit' => intval($row['exit'])
    ];
    return [$admission];
}


function parseCode( $code ) {
    $index = strpos( $code, '-' );
    if( $index === FALSE ) {
        error_log("[synchro] parseCode: '-' not found in '$code'!");
        return null;
    }

    $id = intval( substr( $code, 0, $index ) );
    $code = substr( $code, $index + 1 );
    $carecenter = \Data\Carecenter\get( $id );
    if( $carecenter == null ) {
        error_log("[synchro] parseCode: No care center with id='$id'!");
        return null;
    }
    if( $carecenter['code'] != $code ) {
        error_log("[synchro] parseCode: unmatching codes!\n" . $carecenter['code'] . ' != ' . $code);
        return null;
    }

    return [
        'id' => $id,
        'code' => $code
    ];
}
?>
