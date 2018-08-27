<?php
$ROLE = "";

include_once("./data.php");


/*
   Return code:
   -1: Missing argument `id`.
   -2: Missing argument `name`.
   -9: SQL error.
 */
function execService( $args ) {
    if( !array_key_exists( 'id', $args ) ) return -1;
    $orgaId = intval($args['id']);
    if( !array_key_exists( 'name', $args ) ) return -2;
    $structureName = $args['name'];

    \Data\begin();
    try {
        $structureId = \Data\Structure\add([
            'name' => $structureName,
            'exams' => '',
            'vaccins' => '',
            'patient' => '',
            'forms' => '',
            'types' => '']);
        \Data\Organization\linkStructures( $orgaId, $structureId );
        \Data\commit();
    }
    catch( Exception $ex ) {
        error_log('[structure.add] ' . json_encode($args) . ' - ' . $ex->getMessage());
        \Data\rollback();
        return -9;
    }
    return $structureId;
}
?>
