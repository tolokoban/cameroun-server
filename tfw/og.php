<html prefix="og: http://ogp.me/ns#">
<?php 
/*
Open Graph is a protocol to share webpages over FaceBook: http://ogp.me/
*/
include_once("php/db.inc");
include_once("php/Preview.php");


$id = intVal($_GET["id"]);
$stm = $DB->query("SELECT * FROM " . $DB->table("trace") . " WHERE id=?", $id);
if( !$stm ) {
    $title = "Unknown trace #$id!";
}
else
{
    // Ensure the image has already been generated.
    $preview = new Preview( $id );
    $preview->load();
    
    // Load trace from DB.
    $row = $stm->fetch();
    $title = $row['name'];
    $desc = "";
    $items = Array();
    $km = floatVal( $row["km"] );
    if( $km > 0.1 ) $items[] = $km . " km";
    $asc = intVal( $row["asc"] );
    if( $asc > 50 ) $items[] = $asc . " D+";
    $dsc = intVal( $row["dsc"] );
    if( $dsc > 50 ) $items[] = $dsc . " D-";
    $dur = intVal( $row["dur"] );
    if( $dur > 60 ) {
        $ss = $dur % 60;
        $dur = intVal( ($dur - $ss) / 60 );
        $mm = $dur % 60;
        $dur = intVal( ($dur - $mm) / 60 );
        $hh = $dur;
        $txt = "";
        if( $hh > 0 ) $txt .= $hh . "h ";
        $txt .= $mm . "m ";
        $txt .= $ss . "s ";
        $items[] = $txt;
    }
    $desc = join(", ", $items);
}
?>
<head>
<title><?php echo $title; ?></title>
<meta property="fb:app_id" content="23136566560" />
<meta property="og:title" content="<?php echo $title; ?>" />
<meta property="og:description" content="<?php echo $desc; ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="https://trail-passion.net/og.php?id=<?php echo $id; ?>" />
<meta property="og:image" 
      content="https://trail-passion.net/tfw/pub/preview/<?php echo $id; ?>.fb.jpg" />
<meta property="og:image:width" content="600" />
<meta property="og:image:height" content="300" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<style>
iframe { position: absolute; left: 0; right: 0; width: 100%; height: 100%; top: 0; bottom: 0;
border: none; margin: 0; padding: 0; }
</style>
</head>
<body><iframe src="https://trail-passion.net/trace.html?id=<?php echo $id; ?>"></iframe></body>
</html>
