<?php
class Log {
  function __construct($type, $key=0, $content='') {
    global $DB;
    $user = new User();
    $DB->query("INSERT INTO " . $DB->table('log')
      . " (`type`, `key`, `content`, `user`)VALUES(?,?,?,?)",
      $type, intVal($key), $content, $user->getId());
  }
}
?>
