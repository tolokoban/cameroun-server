<?php
$ROLE = "USER";

include_once("./data.php");


function execService( $args ) {
    if( !array_key_exists( 'carecenter', $args ) ) return -1;
    $carecenterId = intVal( $args['carecenter'] );
    if( !array_key_exists( 'field', $args ) ) return -2;
    $field = $args['field'];
    
    $sql = "SELECT D.`value`, count(D.`value`) FROM"
         . \Data\Patient\name() . "AS P,"
         . \Data\Admission\name() . "AS A,"
         . \Data\Consultation\name() . "AS C,"
         . \Data\Data\name() . "AS D "
         . "WHERE P.`carecenter`=? "
         . "AND P.`id`=A.`patient` "
         . "AND A.`id`=C.`admission` "
         . "AND C.`id`=D.`consultation` "
         . "AND D.`key`=? "
         . "GROUP BY D.`value` ";

    $stm = \Data\query( $sql, $carecenterId, $field );
    if( !$stm ) return -3;
    $result = [];
    while( null != ($row = $stm->fetch() ) ) {
        $result[$row[0]] = intVal( $row[1] );
    }
    return $result;
}
?>
