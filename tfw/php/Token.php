<?php
class Token {
  var $DB;

  function __construct($DB) {
    $this->DB = $DB;
  }

  function createToken($userId) {
    $key = '';
    for ($i=0 ; $i<32 ; $i++) {
      $key .= substr("abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                     rand( 0, 62 ),
                     1 );
    }
    $DB = $this->DB;
    $DB->query("INSERT INTO " . $DB->table("token")
               . "(usr, `key`)"
               . "VALUES(?,?)",
               intVal($userId),
               $key);
    $tkn = $DB->lastId();
    $stm = $DB->query("SELECT login FROM " . $DB->table("user")
                      . " WHERE id=?", $userId);
    $row = $stm->fetch();
    $email = '';
    if ($row) {
      $email = $row[0];
    }
    return "tkn=$tkn&key=$key&usr=" . urlencode($email);    
  }
}
?>

