<?php
$ROLE = "USER";
$ROLE = "";

include_once("./data.php");

/**
 * {
 *   "key1": {
 *     "val1": 32,
 *     "val2": 13,
 *     "val3": 47,
 *     ...
 *   },
 *   ...
 * }
 */
function execService( $args ) {
    if( !array_key_exists( 'carecenter', $args ) ) return -1;
    $carecenterId = intVal( $args['carecenter'] );
    if( !array_key_exists( 'begin', $args ) ) return -2;
    if( !array_key_exists( 'end', $args ) ) return -3;
    $begin = intVal($args['begin']);
    $end = intVal($args['end']);

    $result = [];

    $sql = "SELECT P.id, P.key, count(*) "
         . "FROM " . \Data\Consultation\name() ." AS C, "
         . \Data\Patient\name() ." AS P, "
         . \Data\Admission\name() ." AS A "
         . "WHERE P.carecenter = ? "
         . "AND A.patient = P.id "
         . "AND C.admission = A.id "
         . "AND C.enter >= ? "
         . "AND C.enter < ? "    
         . "GROUP BY P.id";

    $stm = \Data\query( $sql, $carecenterId, $begin, $end );
    $patients = [];
    $ids = [];
    $patientKeysById = [];
    if( $stm ) {
        while( null != ($row = $stm->fetch()) ) {
            $id = intVal($row[0]);
            $key = $row[1];
            $consultations = intVal($row[2]);
            $patients[$key] = [ '$consultations' => $consultations ];
            $ids[] = $id;
            $patientKeysById[$row[0]] = $key;
        }
    }

    $sql = "SELECT D.`key`, D.`value`, P.`key` "
         . "FROM " . \Data\PatientField\name() ." AS D, "
         . \Data\Patient\name() ." AS P "
         . "WHERE P.id IN (" . implode(",", $ids) . ") "
         . "AND D.patient = P.id "
         . "ORDER BY D.`key`, D.`value`";

    $stm = \Data\query( $sql );
    if( $stm ) {
        while( null != ($row = $stm->fetch()) ) {
            $key = $row[0];
            if( $key == '#PATIENT-LASTNAME' ) continue;
            if( $key == '#PATIENT-FIRSTNAME' ) continue;
            if( $key == '#PATIENT-SECONDNAME' ) continue;
            $value = $row[1];
            $patient = $row[2];
            $patients[$patient][$key] = $value;
        }
    }

    //====================================================================
    
    $sql = "SELECT C.id, D.key, D.value, P.id, C.enter "
         . "FROM " . \Data\Consultation\name() ." AS C, "
         . \Data\Data\name() ." AS D, "
         . \Data\Patient\name() ." AS P, "
         . \Data\Admission\name() ." AS A "
         . "WHERE D.consultation = C.id "
         . "AND P.carecenter = ? "
         . "AND A.patient = P.id "
         . "AND C.admission = A.id "
         . "AND C.enter >= ? "
         . "AND C.enter < ? "
         . "ORDER BY C.enter";

    $stm = \Data\query( $sql, $carecenterId, $begin, $end );
    $data = [];
    if( $stm ) {
        while( null != ($row = $stm->fetch()) ) {
            $consultationId = intVal($row[0]);
            $key = $row[1];
            $val = $row[2];
            $patientId = $row[3];
            $enter = intVal($row[4]);
            $patientKey = $patientKeysById[$patientId];
            if( !array_key_exists( $consultationId, $data ) ) {
                $data[$consultationId] = [ "patient" => $patientKey ];
                $data[$consultationId]["date"] = $enter;
            }
            $data[$consultationId][$key] = $val;
        }
    }

    return [
        "patients" => $patients,
        "data" => $data
    ];
}
