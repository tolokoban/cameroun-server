<?php
$ROLE = "";

include_once("./data.php");


function execService( $args ) {
    error_log("[carecenter.list] args: " . json_encode($args));
    $orgaId = intval( $args );
    $carecenterIds = \Data\Organization\getCarecenters( $orgaId );
    $stm = \Data\query("SELECT id, `name`, `code`, `structure` FROM" . \Data\Carecenter\name()
                     . "WHERE id IN (" . implode(',',$carecenterIds) . ")");
    $carecenters = [];
    while( null != ($row = $stm->fetch()) ) {
        $carecenters[] = [
            'id' => intval($row['id']),
            'name' => $row['name'],
            'code' => $row['code'],
            'structure' => intval($row['structure'])
        ];
    }
    error_log("[carecenter.list] carecenters: " . json_encode($carecenters));
    return $carecenters;
}
?>
