<?php
$ROLE = '';  // Everyone can access this service.

/**
   First step:
   -----------
   Call tfw.Install("cameroun_").
   If the result is 0, everything is already installed.
   Otherwise, the result will be -9.

   Second step:
   ------------
   Call tfw.Install({
   token: "HCHERBBHEC",
   host: "127.0.0.1",
   name: "database-name",
   dbUsr: "root",
   dbPwd: "database-password",
   usr: "Website-user",
   pwd: "user-password"
   })
   The prefix is own in the session, that's why we don't provide it again.
   Tee file "pri/install.sql" will be applyed to the database. This file is a MySQL file with these placeholders: "${PREFIX}"

   Here are the possible results :
 *  0: Installation done successfully.
 * -1: Missing mandatory argument.
 * -2: Unknown host.
 * -3: Database not found.
 * -4: Invalid user name or password.
 * -5: Missing "pri/install.sql" file.
 * -6: DB is not empty.
 */
function execService($args) {
    //if( is_string( $args ) ) -9;

    $configFile = "./php/@db.cfg.inc";
    if( file_exists( $configFile ) ) return 0;

    $mandatoryArguments = Array('prefix', 'host', 'name', 'dbUsr', 'dbPwd', 'usr', 'pwd');
    foreach( $mandatoryArguments as $name ) {
        if( !isset($args[$name]) ) {
            error_log("[tfw.Install] Missing argument `$name`!");
            error_log(json_encode( $args ));
            return -1;
        }
    }

    $prefix = $args['prefix'] . '_';
    $host = $args['host'];
    $name = $args['name'];
    $dbUsr = $args['dbUsr'];
    $dbPwd = $args['dbPwd'];

    try {
        $cnx = new PDO("mysql:host=$host;dbname=$name", $dbUsr, $dbPwd);
        $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (Exception $e) {
        $errCode = $e->getCode();
        switch( $errCode ) {
            case '2002': return -2; // Unknown host.
            case '1049': return -3; // Database not found.
            case '1045': return -4; // Bad usr/pwd.
        }
        error_log("host: $host");
        error_log("name: $name");
        error_log("dbUsr: $dbUsr");
        error_log("Error #" . $e->getCode());
        error_log($e->getMessage());
        return $e->getMessage();
    }

    $installFile = "./pri/install.sql";
    if( !file_exists( $installFile ) ) return -5;

    $date = date('Ymd');
    $usr = $args['usr'];
    $pwd = $args['pwd'];

    $sql = file_get_contents( $installFile );

    $sql = str_replace( '${PREFIX}', $prefix, $sql );
    $sql = str_replace( '${DATE}', $date, $sql );
    $sql = str_replace( '${USER}', $usr, $sql );
    $sql = str_replace( '${PASSWORD}', $pwd, $sql );

    try {
        $cnx->exec( $sql );
    }
    catch( Exception $e ) {
        $errCode = $e->getCode();
        switch( $errCode ) {
            case '42S01': return -6;  // DB is not empty.
        }
        error_log("Error #" . $e->getCode());
        error_log($e->getMessage());
        return $e->getMessage();
    }

    file_put_contents(
        $configFile,
        "<?php $DB_CFG=Array('host'=>'$host',"
        . "'name'=>'$name',"
        . "'usr'=>'$usr',"
        . "'pwd'=>'$pwd',"
        . "'prefix'=>'$prefix');?>"
    );

    return 0;
}


function makeToken( $prefix ) {
    $token = md5(rand(1000000000, 9999999999));
    $_SESSION["token"] = $token;
    $_SESSION["prefix"] = $prefix;
    return $token;
}
?>
