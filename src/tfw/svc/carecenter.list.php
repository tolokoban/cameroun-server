<?php
$ROLE = "";

include_once("./data.php");


function execService( $args ) {
    $orgaId = intval( $args );
    $carecenterIds = \Data\Organization\getCarecenters( $orgaId );
    $stm = \Data\query("SELECT id, name FROM" . \Data\Carecenter\name()
                     . "WHERE id IN (?)", implode(',',$carecenterIds));
    $carecenters = [];
    while( null != ($row = $stm->fetch()) ) {
        $carecenters[] = [
            'id' => intval($row['id']),
            'name' => $row['name'],
            'code' => $row['code']
        ];
    }
    return $carecenters;
}
?>
