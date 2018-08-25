<?php
$ROLE = "";

include_once("./data.php");

/*
   Return codes:
   0: Success.
   -1: Not allowed to rename this organization.
   -2: Missing 'id'.
   -3: Missing 'name'.
   -9: Unexpected exception.
 */
function execService($args) {
    try {
        if( !array_key_exists( 'id', $args ) ) return -2;
        $id = intval($args['id']);
        if( !array_key_exists( 'name', $args ) ) return -3;
        $name = $args['name'];

        $user = new Grant();
        if( !$user->canRenameOrga( $id ) ) return -1;

        \Data\Organization\upd( $id, ['name' => $name] );
        return 0;
    }
    catch( Exception $e ) {
        error_log("Exception in service orga.rename(" . json_encode($args) . ")!");
        error_log($e->getMessage());
        return -9;
    }
}
?>
