<?php
$ROLE = "";

include_once("./data.php");


function execService() {
    $user = new User();
    $row = \Data\fetch('SELECT `dashboard` FROM '
                     . \Data\User\name()
                     . ' WHERE id=?', $user->getId());
    $dashboard = null;
    $dashboard = @json_decode($row[0]);
    if( $dashboard == null || !array_key_exists( 'panels', $dashboard ) ) {
        return [
            'options' => ['lang' => ''],
            'panels' => []
        ];
    }
    return $dashboard;
}
?>
