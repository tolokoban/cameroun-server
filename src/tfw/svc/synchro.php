<?php
$ROLE = "";

include_once("./data.php");

/*
   Return codes:
   -1: Missing arg `cmd`.
   -2: Missing arg `code`.
   -3: Invalid code.
   -4: Unknown command.
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
            return execStatus( $carecenterId, $args );
        default:
            return -4;
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
 *           consultations: [
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
function execStatus( $carecenterId, $args ) {
    $structureId = \Data\Carecenter\getStructure( $carecenterId );
    $structure = \Data\Structure\get( $structureId );

    $patients = null;
    $patientIds = \Data\Carecenter\getPatients( $carecenterId );
    foreach( $patientIds as $patientId ) {
        if( $patients == null ) $patients = [];
        $patient = \Data\Patient\get( $patientId );
        $patients[$patient['id']] = [
            'admissions' => []
        ];
    }
    return [
        'patients' => $patients,
        'structure' => $structure
    ];
}


function parseCode( $code ) {
    $index = strpos( $code, '-' );
    if( $index === FALSE ) {
        error_log("[synchro] parseCode: '-' not found in '$code'!");
        return null;
    }

    $id = intval( substr( $code, 0, $index ) );
    $code = substr( $code, $index + 1 );
    error_log("[synchro] parseCode: id=$id, code='$code'");
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
