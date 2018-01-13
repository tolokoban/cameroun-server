<?php
include_once("php/Preview.php");

$type = "svg";
if (array_key_exists('type', $_REQUEST)) {
    $type = $_REQUEST["type"];
}
if ($type == 'jpeg') $type = 'jpg';
if ($type != 'jpg') $type = 'svg';
if (array_key_exists('id', $_REQUEST)) {
    $id = intVal($_REQUEST["id"]);
} else {
    $id = intVal($_REQUEST["jpg"]);
    $type = 'jpg';
}

//header('Content-Type: image/svg+xml');
header('Content-Type: image/jpeg');

$preview = new Preview( $id );
$reload = false;
if (array_key_exists('fb', $_REQUEST)) {
    $reload = true;
}
$filename = $preview->load( $reload );
if (array_key_exists('fb', $_REQUEST)) {
    // Pour les images FaceBook (600x300) il faut ajouter le suffixe ".fb".
    // Par exemple, "preview/2434.jpg" devient "preview/2434.fb.jpg".
    $filename = substr( $filename, 0, -4 ) . "-fb.jpg";
}
error_log("Returned filename: " . $filename);
readfile( $filename );
?>
