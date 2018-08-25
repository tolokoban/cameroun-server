<?php
$ROLE = "";

include_once("./data.php");


function execService( $dashboard ) {
    $user = new User();
    \Data\User\upd( $user->getId(), ['dashboard' => json_encode($dashboard)] );
    return 0;
}
?>
