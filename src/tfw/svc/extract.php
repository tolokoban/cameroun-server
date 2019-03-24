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
    if( $stm ) {
        while( null != ($row = $stm->fetch()) ) {
            $id = intVal($row[0]);
            $key = $row[1];
            $consultations = intVal($row[2]);
            $patients[$key] = [ '$consultations' => $consultations ];
            $ids[] = $id;
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
            $value = $row[1];
            $patient = $row[2];
            $patients[$patient][$key] = $value;
        }
    }

    $sql = "SELECT D.key, D.value, count(*) AS occurences "
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
         . "GROUP BY D.key, D.value "
         . "ORDER BY D.key, D.value";

    $stm = \Data\query( $sql, $carecenterId, $begin, $end );
    $data = [];
    if( $stm ) {
        while( null != ($row = $stm->fetch()) ) {
            $key = $row[0];
            $val = $row[1];
            $occ = intVal($row[2]);
            if( !array_key_exists( $key, $data ) ) {
                $data[$key] = [ $val => $occ ];
            } else {
                $data[$key][$val] = $occ;
            }
        }
    }

    return [
        "patients" => $patients,
        "data" => $data
    ];
}
