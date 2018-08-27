<?php
$ROLE = "";

include_once("./data.php");

/*
   Return codes:
   -1: Not allowed to create organizations.
   -2: SQL error.
 */
function execService($args) {
    $user = new User();

    if( !$user->isAdmin() && !$user->hasRole("ORGA") ) return -1;
    \Data\begin();
    try {
        $id = intval( \Data\Organization\add([ 'name' => $args['name'] ]) );
        \Data\Organization\linkAdmins( $id, $user->getId() );
    }
    catch( Exception $ex ) {
        error_log('[orga.add] ' . json_encode($args) . ' - ' . $ex->getMessage());
        \Data\rollback();
        return -2;
    }
    \Data\commit();
    return $id;
}
?>
