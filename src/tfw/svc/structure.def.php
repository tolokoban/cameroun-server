<?php
$ROLE = "";

include_once("./data.php");


/*
   { id: <structure id>, fld:<field name>, value:<optional> }

   Return code:
   -1: Missing argument `id`.
   -2: Missing argument `fld`.
   -9: SQL error.
 */
function execService( $args ) {
    if( !array_key_exists( 'id', $args ) ) return -1;
    $id = intval($args['id']);
    if( !array_key_exists( 'fld', $args ) ) return -2;
    $fld = $args['fld'];
    if( !array_key_exists( 'value', $args ) ) return getDef( $id, $fld );
    return setDef( $id, $fld, $args['value'] );
}


function getDef( $id, $fieldName ) {
    error_log("id = $id");
    error_log("fieldName = $fieldName");
    try {
        $row = \Data\fetch("SELECT `$fieldName` FROM"
                         . \Data\Structure\name()
                         . "WHERE id=?", $id);
    }
    catch( Exception $ex ) {
        error_log("[structure.def] " . $ex->getMessage());
        return -9;
    }
    error_log("row = " . json_encode($row));
    return $row[0];
}


function setDef( $id, $fieldName, $value ) {
    \Data\Structure\upd( $id, [$fieldName => $value] );
    return 0;
}
?>
