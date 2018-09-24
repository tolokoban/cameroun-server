<?php
$ROLE = '';

include_once("./data.php");

/**
 * -1: Missing arg `login`.
 * -2: Unknown login.
 * -3: No email.
 * -9: SQL error.
 */
function execService( $args ) {
    if( !array_key_exists( 'login', $args ) ) return -1;
    $login = $args['login'];

    $stm = \Data\query('SELECT `id` FROM' . \Data\User\name()
                     . 'WHERE `login`=?', $login);
    if( !$stm ) return -9;
    $row = $stm->fetch();
    if( !$row ) {
        // Wait for 3 seconds in order to prevent multiple tries.
        sleep( 3 );
        return -2;
    }


    return -3;
}
?>
