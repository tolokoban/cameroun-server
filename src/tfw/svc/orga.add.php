<?php
$ROLE = "";

include_once("./data.php");

/*
   Return codes:
   -1: Not allowed to create organizations.
*/
function execService($args) {
    $user = new User();
    
    if( !$user->isAdmin() || !$user->hasRole("ORGA") ) return -1;
    $id = intval( \Data\Organization\add( $args ) );
    \Data\Organization\linkAdmins( $id, $user->getId() );
    return $id;
}
?>
