<?php
$ROLE = "";

include_once("./data.php");


/*
   Return code:
   -1: Missing argument `orga`.
   -2: Missing argument `name`.
   -3: Missing argument `code`.
   -4: Missing argument `structure`.
   -9: SQL error.
 */
function execService( $args ) {
    if( !array_key_exists( 'orga', $args ) ) return -1;
    $orgaId = intval($args['orga']);
    if( !array_key_exists( 'name', $args ) ) return -2;
    $carecenterName = $args['name'];
    if( !array_key_exists( 'code', $args ) ) return -3;
    $carecenterCode = $args['code'];
    if( !array_key_exists( 'structure', $args ) ) return -4;
    $structureId = intval($args['structure']);

    \Data\begin();
    try {
        $user = new User();
        $carecenterId = \Data\Carecenter\add([
            'name' => $carecenterName,
            'code' => $carecenterCode]);
        \Data\Structure\linkCarecenters( $structureId, $carecenterId );
        \Data\Organization\linkCarecenters( $orgaId, $carecenterId );
        \Data\User\linkCarecenters( $user->getId(), $carecenterId );
        \Data\commit();
    }
    catch( Exception $ex ) {
        error_log('[carecenter.add] ' . json_encode($args) . ' - ' . $ex->getMessage());
        \Data\rollback();
        return -9;
    }
    return $carecenterId;
}
?>
