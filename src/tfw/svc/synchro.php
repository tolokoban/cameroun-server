<?php
$ROLE = "";

include_once("./data.php");

/*
   Return codes:
   -1: Missing arg `cmd`.
   -2: Missing arg `code`.
   -3: Invalid code.
   -4: Unknown command.
 */
function execService() {
    if( !array_key_exists( 'cmd', $args ) ) return -1;
    $cmd = $args['cmd'];
    if( !array_key_exists( 'code', $args ) ) return -2;
    $code = $args['code'];

    $items = parseCode( $code );
    if( $items == 0 ) return -3;
    $carecenterId = $items["id"];
    $secretCode = $items["code"];

    switch( $cmd ) {
        case 'status':
            return execStatus( $carecenter, $args );
        default:
            return -4;
    }

}


/**
 * {
 *   patients: {
 *     Xq14: {
 *       admissions: [
 *         {
 *           enter: 1501239017,
 *           exit: ...
 *           consultations: [
 *             {
 *               date: 1501239017
 *             },
 *             ...
 *           ]
 *         },
 *         ...
 *       ]
 *     },
 *     ...
 *   }
 * }
 */
function execStatus( $carecenter, $args ) {

}


function parseCode( $code ) {
    $index = strpos( $code, '-' );
    if( $index === FALSE ) return null;

    $id = intval( substr( $code, 0, $index ) );
    $code = substr( $code, $index + 1 );
    $carecenter = \Data\Carecenter\get( $id );
    if( $carecenter == null ) return null;
    if( $carecenter['code'] != $code ) return null;

    return [
        'id' => $id,
        'code' => $code
    ];
}
?>
