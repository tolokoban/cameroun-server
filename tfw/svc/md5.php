<?php
$ROLE = "";

/**
 * $input : Id ou pseudo d'un utilisateur.
 * @return Le hash MD5 de son adresse e-mail.
 */
function execService($input) {
  global $DB;
  
  $id = intVal($input);
  if ($id > 0) {
    $stm = $DB->query("SELECT login FROM " . $DB->table("user")
                      . " WHERE id=?",
                      $id);
  } else {
    $stm = $DB->query("SELECT login FROM " . $DB->table("user")
                      . " WHERE name=?",
                      $input);
  }
  $row = $stm->fetch();
  if ($row) {
    return md5($row['login']);
  } else {
    return "NOT-FOUND";
  }
}

?>
