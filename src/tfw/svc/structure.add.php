<?php
$ROLE = "";

include_once("./data.php");


/*
   Return code:
   -1: Missing argument `id`.
   -2: Missing argument `name`.
 */
function execService( $args ) {
    if( !array_key_exists( 'id', $args ) ) return -1;
    $orgaId = intval($args['id']);
    if( !array_key_exists( 'name', $args ) ) return -2;
    $structureName = $args['name'];

    $structureId = \Data\Structure\add([
        'name' => $structureName,
        'exams' => '',
        'vaccins' => '',
        'patient' => '',
        'forms' => '',
        'types' => '']);

    \Data\Organization\linkStructures( $orgaId, $structureId );
}
?>
