<?php
$DEBUG = true;    // Activer ce flag pour logguer les requêtes dans 'services.log.html'.

// Cette fonction permet d'éviter d'utiliser des includes
// pour chaque classe que l'on souhaite utiliser.
// Si, lors d'un new, la classe n'est pas connue alors
// l'include se fera comme spécifié dans cette fonction.
function __autoload($class_name) {
  include 'php/' . $class_name . '.php';
}

/**
 * Ce fichier PHP permet d'appeler les services qui se trouvent dans le
 * sous-répertoire "svc".
 * Voici les paramètres attendus :
 *   - "s" (service) : Nom du service.
 *   - "i" (input) :
 */
ob_start();

// Utiliser le user-agent du client pour les requêtes effectuée par le serveur.
ini_set ('user_agent', $_SERVER['HTTP_USER_AGENT']);

// Cross Site Origin.
//header('Access-Control-Allow-Origin: *');
$origin = '*';
if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
  $origin = $_SERVER['HTTP_ORIGIN'];
 }
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS');
header('Access-Control-Max-Age: 900');

date_default_timezone_set("Europe/Paris");
include_once("php/db.inc");

// Protection contre les modification de quotes selon la config du serveur PHP.
if (get_magic_quotes_gpc()) {
  $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
  while (list($key, $val) = each($process)) {
    foreach ($val as $k => $v) {
      unset($process[$key][$k]);
      if (is_array($v)) {
        $process[$key][stripslashes($k)] = $v;
        $process[] = &$process[$key][stripslashes($k)];
      } else {
        $process[$key][stripslashes($k)] = stripslashes($v);
      }
    }
  }
  unset($process);
 }


@session_start();
$USER = new User();

/**
 * Retourne true si l'utilisateur possède le role $role.
 */
function hasRole($role) {
  global $USER;
  return $USER->hasRole($role);
}

function userId() {
  global $USER;
  return $USER->getId();
}

echo "<pre style='border: 1px solid black'>";
echo date("d/m/Y H:i:s");
echo " (from " . $_SERVER["REMOTE_ADDR"] . ")\n";
print_r($_REQUEST);
echo "<hr/>\n";

echo SID . "<br/>\n";

@$service = $_REQUEST['s'];
// Vérification de la validité du nom de service.
// Il ne doit contenir que des lettres, des chiffres et des points.
for ($i=0 ; $i<strlen($service) ; $i++) {
  $c = $service[$i];
  if ($c == '.') continue;
  if ($c >= '0' && $c <= '9') continue;
  if ($c >= 'a' && $c <= 'z') continue;
  if ($c >= 'A' && $c <= 'Z') continue;
  // On a détecté un nom de service invalide.
  die("Invalid service name: $service");
 }

// Paramètre d'entrée.
echo "Input = " . $_REQUEST['i'] . "\n";
@$input = json_decode($_REQUEST['i'], true);
$output = Array();

// Déclarer les objets d'accès aux donées publiques et privées.

// Inclure le code du service qui doit fournir une fonction execService().
// Il doit aussi redéfinir la variable $ROLE qui donne le role nécessaire
// à l'appel de ce service. Si $ROLE est vide, tout le monde est autorisé.
// Mais par défaut, seul l'admi l'est.
$ROLE = "ADMIN";
try {
  include "svc/$service.php";
}
catch (Exception $e) {
    error_log("FATAL: $e");
    echo "<pre>";
    echo $e->getMessage();
    echo "</pre>\n";
    $fatal = 'SYNTAX';
}

$fatal = '';

if ($USER->hasRole($ROLE)) {
  try {
    $output = execService($input);
  }
  catch (Exception $e) {
    $msg = $e->getMessage();
    echo "<pre>$msg</pre>\n";
    if (substr($msg, 0, 1) == '!') {
      $fatal = $msg;
    } else {
      $fatal = 'ERROR';
    }
  }
} else {
  // Pas de droits. Role insuffisant.
  $fatal = '!' . $ROLE;
}
// Fermer la connexion à la base de données.
$DB = null;

$garbage = ob_get_clean();

$json = json_encode($output);
if ($fatal == '') {
  // Paramètre de sortie.
  echo $json;
} else {
  error_log("fatal = '$fatal'");
  if ($DEBUG == true) {
    $garbage .= "<hr/>\n$json</pre>";
    $logfile = "pri/services.log.html";
    if (file_exists($logfile)) {
      $t = time() - filemtime($logfile);
      if ($t > 15) {
        @unlink($logfile);
        file_put_contents($logfile, "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />");
      }
    }
    file_put_contents($logfile, $garbage);
    //error_log($output, 3, $logfile);
  }
  echo $fatal;
}
?>
