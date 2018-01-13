<?php
// Gestion d'un utilisateur.
class Trace {
  public function load($id) {
    global $DB;
    $file = new File("pri", "trace");
    $data = $file->load($id);
    if ($data == null) return -1;

    $sql = "SELECT T.group, T.public, T.official, "
         . "T.user, T.view, T.type, U.name, T.pack"
         . " FROM " . $DB->table("trace") . " As T, "
         . $DB->table("user"). " As U "
         . " WHERE T.user = U.id AND T.id=?";
    $stm = $DB->query($sql, $id);
    if (!$stm) {
        return -2;
    }
    $row = $stm->fetch();
    if (!$row) {
        error_log( "[tp3.Load] No trace found with id=$id and query:" );
        error_log( $sql );
        return -1;
    }
    $trace = json_decode($data, true);
    $trace["id"] = $id;
    $trace["grp"] = $row[0];
    $trace["public"] = intval($row[1]);
    $trace["official"] = intval($row[2]);
    $trace["usr"] = intval($row[3]);
    $trace["id"] = intval($id);
    $trace["view"] = intval($row[4]);
    $trace["type"] = intval($row[5]);
    $trace["nck"] = $row[6];
    $trace["pack"] = $row[7];
    if ($row[3] != userId() && !hasRole("ADMIN")
        && !isset($_SESSION["view"]["K" . $id])) {
        // Pour éviter qu'un utilisateur duplique une trace
        // en embarquant le flag "official".
        $trace["official"] = 0;
        // Incrémenter le nombre de vues.
        $DB->query("UPDATE " . $DB->table("trace")
                 . " SET view = view + 1"
                 . " WHERE id=?",
                   $id);
        $trace["view"]++;
        // Si, au sein d'une même session, on revoit une trace,
        // son compteur ne doit pas augmenter.
        $_SESSION["view"]["K" . $id] = 1;
    }

    //-----------------------------------------------------
    // If the user is granted, we return the `live` field.
    if( hasRole("ADMIN") ) {
        $stm = $DB->query("SELECT T.live FROM "
                        . $DB->table('trace') . " AS T "
                        . 'WHERE T.id=?',
                          $id);
    } else {
        $stm = $DB->query("SELECT T.live FROM "
                        . $DB->table('trace') . " AS T "
                        . "LEFT JOIN " . $DB->table('grant') . ' AS G '
                        . 'ON T.id = G.trace '
                        . 'WHERE T.id=? AND G.grant=1 '
                        . 'AND G.usr=?',
                          $id, userId());
    }

    if( $stm ) {
        $row = $stm->fetch();
        if( $row ) {
            $trace['live'] = $row[0];
        }
    }

    //----------------------------------------
    // Check if the trace is owned by a Pack.
    if ($trace['pack'] == 'I') {
        // Yes! This trace is part of at least one INDIVIDUAL pack.
        $user = new User();
        if( $user->getId() != $trace['usr'] && !$user->isAdmin() ) {
            // You're not admin nor the owner. You have restricted accesses to this trace.
            unset( $trace['alt'] );
            unset( $trace['tim'] );
            unset( $trace['acc'] );
            unset( $trace['dis'] );
            unset( $trace['poi'] );
            unset( $trace['text'] );
        }
    }

    return $trace;
  }
}
?>
