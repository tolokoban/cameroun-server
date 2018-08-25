<?php
$ROLE = "";

include_once("./data.php");

/*
   Return codes:
   0: Success.
   -1: Not allowed to delete this organization.
   -2: Missing 'id'.
   -9: Unexpected exception.
 */
function execService($args) {
    try {
        if( !array_key_exists( 'id', $args ) ) return -2;
        $id = intval($args['id']);

        $user = new Grant();
        if( !$user->canDeleteOrga( $id ) ) return -1;

        \Data\Organization\del( $id );
        return 0;
    }
    catch( Exception $e ) {
        error_log("Exception in service orga.delete(" . json_encode($args) . ")!");
        error_log($e->getMessage());
        return -9;
    }
}
?>
