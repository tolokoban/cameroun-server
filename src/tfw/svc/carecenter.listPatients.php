<?php
$ROLE = "";

include_once("./data.php");


function execService( $carecenterId ) {
    global $DB;
    $DB->debug( true );
    
    $carecenterId = intval( $carecenterId );
    $carecenter = \Data\Carecenter\get( $carecenterId );
    $result = [ 'name' => $carecenter['name'], 'list' => [] ];

    $stm = \Data\query('SELECT P.`id`, F.`key`, F.`value` FROM'
                     . \Data\Patient\name() . 'As P,'
                     . \Data\PatientField\name() . 'As F '
                     . 'WHERE P.`id`=F.`patient` '
                     . 'AND P.`carecenter`=?', $carecenterId);
    $patients = [];
    while( null != ($row = $stm->fetch()) ) {
        error_log(json_encode($row));
        $id = $row['id'];
        if( !array_key_exists( $id, $patients ) ) $patients[$id] = [];
        $key = $row['key'];
        $value = $row['value'];
        $patients[$id][$key] = $value;
    }

    foreach( $patients as $id => $patient ) {
        $patient['id'] = $id;
        $result['list'][] = $patient;
    }
    
    return $result;
}
?>
