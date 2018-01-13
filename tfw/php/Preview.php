<?php
/*
   Pour éviter de devoir passer par cette page lorsqu'on veut récupérer une vignette,
   nous allons déposer les images dans le répertoire `tfw/pub/preview/<id>.jpg`.

   Les vignettes font 300x300 et doivent pouvoir être modifiées par l'utilisateur.

   En parallèle, on produit une image de 600x300 pour les partages FaceBook.
   `tfw/pub/preview/<id>-fb.jpg`

   Dans le processus de construction, on crée d'abord l'image en 600x315,
   puis on prend la partie gauche pour en faire la vignette en 300x300.
 */
// Taille de l'imge.
define('WIDTH_FB', 800);
define('HEIGHT_FB', 400);
// Taille de la trace.
define('WIDTH_TRACE', 500);
define('HEIGHT_TRACE', 340);
// Taille d'une vignette carrée.
define('VIGNETTE', 300);
// Taille du profil.
define('HEIGHT_PROFIL', 60);


include_once("File.php");
include_once("Tile.php");


class Preview {
    function __construct($id) {
        $this->file = new File("pri", "trace");
        $this->id = $id;
    }

    /*
       Si l'image "pub/preview/<ID>.jpg" n'exsite pas, la créer.
       @return Le chemin du fichier à partir de tfw/.
     */
    function load( $forceReload = false ) {
        $filename = "pub/preview/" . $this->id . ".jpg";
        $filenameFB = "pub/preview/" . $this->id . "-fb.jpg";
        if ($forceReload || !file_exists($filename) || !file_exists($filenameFB)) {
            if (!$this->save()) {
                return "pub/preview/0.jpg";
            }
        }
        return $filename;
    }

    function save() {
        $raw = $this->file->load( $this->id );
        if ( $raw == null ) {
            trace("[Preview] Trace does not exist: " . $this->id . "\n");
            return false;
        }
        $data = json_decode($raw, true);
        // Empêcher les boucles infinies lorsqu'il n'y a pas de latitudes.
        if (count($data["lat"]) < 1) {
            trace("[Preview.save] Bad trace: " . $this->id);
            return false;
        }

        $zip = 0;
        if (array_key_exists("zip", $data)) {
            $zip = intVal($data["zip"]);
        }
        $latArr = $this->unzip6($zip, $data["lat"]);
        $lngArr = $this->unzip6($zip, $data["lng"]);
        $disArr = $this->unzip($zip, $data["dis"]);
        $altArr = $this->unzip($zip, $data["alt"]);

        $latMin = $latArr[0];
        $latMax = $latArr[0];
        $lngMin = $lngArr[0];
        $lngMax = $lngArr[0];
        $dis0 = $disArr[0];
        // Rechercher des bornes de la trace.
        for ($i = 0 ; $i < count($latArr) ; $i++) {
            $lat = $latArr[$i];
            $lng = $lngArr[$i];
            if ($lat < $latMin) $latMin = $lat;
            if ($lat > $latMax) $latMax = $lat;
            if ($lng < $lngMin) $lngMin = $lng;
            if ($lng > $lngMax) $lngMax = $lng;
        }
        $lngCenter = ($lngMin + $lngMax) / 2;
        $latCenter = ($latMin + $latMax) / 2;
        // Ajouter une marge de 10%;
        $inflation = 1.010;
        $lngRadius = $inflation * ($lngMax - $lngMin) / 2;
        $latRadius = $inflation * ($latMax - $latMin) / 2;
        $lngMin = $lngCenter - $lngRadius;
        $lngMax = $lngCenter + $lngRadius;
        $latMin = $latCenter - $latRadius;
        $latMax = $latCenter + $latRadius;
        // Recherche du facteur d'échelle le mieux adapté.
        // On va se baser sur la taille d'une tuile pour caler l'image avec les lat/lng.
        // On obtient des pixels en multipliant $latFactor par une latitude.
        $latTraceSize = $latMax - $latMin;
        $lngTraceSize = $lngMax - $lngMin;
        $zoom = 0;
        for ($candidateZoom = 1 ; $candidateZoom < 20 ; $candidateZoom++) {
            $tile = new Tile($latCenter, $lngCenter, $candidateZoom);
            $latPerPixel = ($tile->latN - $tile->latS) / 256;
            $lngPerPixel = ($tile->lngE - $tile->lngW) / 256;
            $traceWidth = $lngTraceSize / $lngPerPixel;
            $traceHeight = $latTraceSize / $latPerPixel;
            if ($traceWidth > WIDTH_TRACE || $traceHeight > HEIGHT_TRACE) break;
            $zoom = $candidateZoom;
        }

        $tile = new Tile($latCenter, $lngCenter, $zoom);
        $latFactor = 256 / ($tile->latN - $tile->latS);
        $lngFactor = 256 / ($tile->lngE - $tile->lngW);
        $this->factorLat = $latFactor;
        $this->factorLng = $lngFactor;

        // Créer un tableau de points pour dessiner la courbe.
        $lat = $latArr[0] - $latCenter;
        $lng = $lngArr[0] - $lngCenter;
        $this->xy($lat, $lng);
        $xx = $this->x;
        $yy = $this->y;
        $points = Array();
        for ($i = 1 ; $i < count($latArr) ; $i++) {
            $lat = $latArr[$i] - $latCenter;
            $lng = $lngArr[$i] - $lngCenter;
            $this->xy($lat, $lng);
            $dx = $xx - $this->x;
            $dy = $yy - $this->y;
            $dist = $dx * $dx + $dy * $dy;
            if ($dist > 16) {
                $points[] = Array( $this->x, $this->y );
                $xx = $this->x;
                $yy = $this->y;
            }
        }

        //=====================================================
        // Les fonds de carte sont générés en JPG.
        //-----------------------------------------------------
        $image = imagecreatetruecolor(WIDTH_FB, HEIGHT_FB);
        $color_background = imagecolorallocate($image, 30, 144, 255);
        imagefilledrectangle($image, 0, 0, WIDTH_FB, HEIGHT_FB, $color_background);

        $tileA = new Tile($latCenter + (HEIGHT_TRACE / 2) / $latFactor,
                          $lngCenter - (WIDTH_TRACE / 2) / $lngFactor,
                          $zoom);
        $tileB = new Tile($latCenter - (HEIGHT_FB - HEIGHT_TRACE / 2) / $latFactor,
                          $lngCenter + WIDTH_FB / $lngFactor,
                          $zoom);
        $this->xy($tileA->lat - $latCenter, $tileA->lng - $lngCenter);
        $x0 = floor(.5 + $this->x);
        $y0 = floor(.5 + $this->y);
        $y = $y0;
        for ($row = $tileA->row ; $row <= $tileB->row ; $row++) {
            $x = $x0;
            for ($col = $tileA->col ; $col <= $tileB->col ; $col++) {
                $tileImg = $this->loadTile($row, $col, $zoom);
                @imagecopy($image, $tileImg, $x, $y,
                           0, 0, 256, 256);
                $x += 256;
            }
            $y += 256;
        }

        // Dessiner la courbe.
        // La variable $points doit être renseignée avec les positions sur l'image.
        // Chaque point est un array de deux éléments : x et y.
        $black = imagecolorallocate( $image, 0, 0, 0 );
        $white = imagecolorallocate( $image, 255, 255, 255 );
        $semiBlack = imagecolorallocatealpha( $image, 0, 0, 0, 26 );
        $oldPoint = null;
        foreach ($points as $newPoint) {
            if( $oldPoint != null ) {
                $this->thickline(
                    $image, $oldPoint[0], $oldPoint[1], $newPoint[0], $newPoint[1], $semiBlack, 12 );
            }
            $oldPoint = $newPoint;
        }
        $oldPoint = null;
        $rr = 0; $gg = 255; $bb = 0; $aa = 30;
        $colors = Array();
        while( $rr < 256 ) {
            $colors[] = imagecolorallocatealpha( $image, $rr, $gg, $bb, $aa );
            $rr += 8;
        }
        $rr = 255;
        $colors[] = imagecolorallocatealpha( $image, $rr, $gg, $bb, $aa );
        while( $gg > 0 ) {
            $colors[] = imagecolorallocatealpha( $image, $rr, $gg, $bb, $aa );
            $gg -= 8;
        }
        $gg = 0;
        $colors[] = imagecolorallocatealpha( $image, $rr, $gg, $bb, $aa );
        $nbColors = count($colors);
        $nbPoints = count($points);
        $idx = 0;
        foreach ($points as $newPoint) {
            if( $oldPoint != null ) {
                $color = $colors[floor($idx * $nbColors / $nbPoints)];
                $this->thickline( $image, $oldPoint[0], $oldPoint[1], $newPoint[0], $newPoint[1], $color, 8 );
            }
            $oldPoint = $newPoint;
            $idx++;
        }

        $this->createVignette( $image );

        $tpLogo = imagecreatefrompng("../css/gfx/facebook-sharing-300.png");
        imagecopy( $image, $tpLogo, WIDTH_FB - imagesx( $tpLogo ), 0, 0, 0,
                   imagesx( $tpLogo ), imagesy( $tpLogo ) );

        $this->createElevationProfil( $image, $disArr, $altArr, 0, HEIGHT_FB, WIDTH_FB + 4, HEIGHT_PROFIL );

        $imageFilename = "pub/preview/" . $this->id . "-fb.jpg";
        imagejpeg($image, $imageFilename, 100);

        imagedestroy($image);
        return true;
    }

    function createVignette( $image, $quality=80 ) {
        $vignette = imagecreatetruecolor( VIGNETTE, VIGNETTE );
        $dstX = 0;
        $dstY = 0;
        $srcX = 0.5 * (WIDTH_TRACE - VIGNETTE);
        $srcY = 0.5 * (HEIGHT_TRACE - VIGNETTE);
        imagecopy(
            $vignette, $image,
            $dstX, $dstY,
            $srcX, $srcY,
            VIGNETTE, VIGNETTE);
        $imageFilename = "pub/preview/" . $this->id . ".jpg";
        imagejpeg($vignette, $imageFilename, $quality);
        imagedestroy($vignette);
    }

    function createElevationProfil( $image, $dis, $alt, $x0, $y0, $width, $height ) {
        $points = $this->getProfilPoints( $dis, $alt, $x0, $y0, $width, $height );
        $background = imagecolorallocatealpha( $image, 0, 255, 0, 64 );
        imagefilledpolygon( $image, $points, count($points) / 2, $background );

        $black = imagecolorallocatealpha( $image, 0, 64, 0, 32 );
        $xA = $points[2];
        $yA = $points[3];

        $topPoints = array_slice( $points, 2, count($points) - 4 );
        $polygonPoints = createThickPolyline( $topPoints, 4 );
        imagefilledpolygon( $image, $polygonPoints, count($polygonPoints) / 2, $black );
    }

    function getProfilPoints( $dis, $alt, $x0, $y0, $width, $height ) {
        $margin = 0.1;  // 10%

        $points = Array( $x0, $y0 );
        $indexes = $this->getIndexesByDistance( $dis, $width / 4 );
        $x = $x0;
        $altitudes = Array();
        $realAltitudes = Array();
        for( $k = 0 ; $k < count( $indexes ) ; $k++ ) {
            $realAlt = $this->getAverageAltitude( $alt, $indexes, $k );
            $realAltitudes[] = $realAlt;
            $altitudes[] = 20 * floor( $realAlt * 0.05 );
        }
        for( $loops=0 ; $loops<2 ; $loops++ ) {
            $altitudes = blurArray( $altitudes );
        }

        list( $minAlt, $maxAlt ) = $this->computeMinMax( $altitudes );
        if( $maxAlt <= $minAlt ) $maxAlt = $minAlt + 1;

        for( $k = 0 ; $k < count( $indexes ) ; $k++ ) {
            $realAlt = $altitudes[$k];
            $normalizedAlt = ($realAlt - $minAlt) / ($maxAlt - $minAlt);
            $y = $margin * $height + floor(0.5 + (1 - $margin) * $height * $normalizedAlt);
            $points[] = $x;
            $points[] = $y0 - $y;
            $x += 4;
        }
        $points[] = $x0 + $width;
        $points[] = $y0;
        return $points;
    }

    function getAverageAltitude( $alt, $indexes, $k ) {
        if( $k <= 0 || $k >= count($indexes) - 1 ) return $alt[$indexes[$k]];

        $accumulator = 0;
        $counter = 0;
        $a = $indexes[$k - 1];
        $b = $indexes[$k + 1];

        if( $b >= $a ) return $alt[$a];

        while( $a <= $b ) {
            $counter++;
            $accumulator += $alt[$a];
        }
        return $accumulator / $counter++;
    }

    /**
     * Retourne un tableau de `$size` index de points équidistants.
     */
    function getIndexesByDistance( $dis, $size ) {
        $min = $dis[0];
        $max = $dis[count($dis) - 1];
        $step = ($max - $min) / ($size - 1);
        $nextMilestone = $step;
        $disIdx = 0;
        $indexes = Array( 0 );

        for( $k = 1 ; $k < $size - 1 ; $k++ ) {
            while( $dis[$disIdx] - $min < $nextMilestone ) $disIdx++;
            $nextMilestone += $step;
            $indexes[] = $disIdx;
        }
        $indexes[] = count($dis) - 1;
        return $indexes;
    }

    function computeMinMax( $arrayOfFloats ) {
        $max = $arrayOfFloats[0];
        $min = $arrayOfFloats[0];
        for( $k = 1 ; $k < count( $arrayOfFloats ) ; $k++ ) {
            $v = $arrayOfFloats[ $k ];
            $max = max( $max, $v );
            $min = min( $min, $v );
        }
        return Array( $min, $max );
    }

    function loadTile($row, $col, $zoom) {
        $tilePath = $zoom . '/' . $col . '/';
        $tileName = $row . '.png';
        $tmpFile = dirname(__file__) . '/../pub/MAPS/' . $tilePath;
        if (!file_exists( $tmpFile )) {
            @mkdir($tmpFile, 0777, true);
        }
        $tmpFile .= $tileName;

        if (!file_exists( $tmpFile )) {
            // If the file is not in cache, CURL it!
            $url = 'http://a.tile.openstreetmap.com/' . $tilePath . $tileName;
            $ch = curl_init($url);
            $fd = fopen($tmpFile, "wb");
            curl_setopt($ch, CURLOPT_FILE, $fd);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fd);
            return imagecreatefrompng( $tmpFile );
        } else {
            trace("Taking tile from cache: " . $tmpFile);
        }
        $tileImg = imagecreatefrompng($tmpFile);
        return $tileImg;
    }

    function xy($lat, $lng) {
        $this->x = WIDTH_TRACE / 2 + ($this->factorLng * $lng);
        $this->y = HEIGHT_TRACE / 2 - ($this->factorLat * $lat);
    }

    function unzip($zip, &$arr) {
        if ($zip == 0) return $arr;
        $val = floatVal($arr[0]);
        $out = Array($val);
        for ($i = 1 ; $i < count($arr) ; $i++) {
            $val += floatVal($arr[$i]);
            $out[] = $val;
        }
        return $out;
    }

    function unzip6($zip, &$arr) {
        if ($zip == 0) return $arr;
        $val = floatVal($arr[0]);
        $out = Array($val * 0.000001);
        for ($i = 1 ; $i < count($arr) ; $i++) {
            $val += floatVal($arr[$i]);
            $out[] = $val * 0.000001;
        }
        return $out;
    }

    /**
     * Retourne la distance en mètre entre deux positions géographiques.
     */
    function distance($lat1, $lng1, $lat2, $lng2) {
        $R = 6371; // Rayon moyen de la terre en km.
        $dLat  = M_PI*($lat2 - $lat1)/180;
        $dLong = M_PI*($lng2 - $lng1)/180;

        $a = sin($dLat/2) * sin($dLat/2) +
        cos(M_PI*($lat1)/180)
            * cos(M_PI*($lat2)/180)
                * sin($dLong/2)
                    * sin($dLong/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $d = floor(($R * $c)*1000);
        return $d;
    }

    function thickline( $img, $x1, $y1, $x2, $y2, $color, $thickness ) {
        $radius = $thickness * .5;
        $vx = $x2 - $x1;
        $vy = $y2 - $y1;
        $steps = ceil( .5 + max( abs($vx), abs($vy) ) );
        $vx /= $steps;
        $vy /= $steps;
        $x = $x1;
        $y = $y1;
        while( $steps --> 0 ) {
            imagefilledellipse( $img, $x, $y, $radius, $radius, $color );
            $x += $vx;
            $y += $vy;
        }
    }
}


function createThickPolyline( $linePoints, $thickness ) {
    $halfThickness = $thickness * 0.5;
    $polygonPoints = Array();
    $len = count( $linePoints );
    for( $k=0 ; $k<$len ; $k+=2 ) {
        $polygonPoints[] = $linePoints[$k];
        $polygonPoints[] = $linePoints[$k + 1] - $halfThickness;
    }
    for( $k=$len - 1 ; $k>=0 ; $k-=2 ) {
        $polygonPoints[] = $linePoints[$k - 1];
        $polygonPoints[] = $linePoints[$k] + $halfThickness;
    }
    return $polygonPoints;
}


function blurArray( $altitudes ) {
    if( count( $altitudes ) < 6 ) return $altitudes;
    $size = count( $altitudes );
    $output = Array(
        ($altitudes[0] + $altitudes[1] + $altitudes[2]) / 3,
        ($altitudes[0] + $altitudes[1] + $altitudes[2] + $altitudes[3]) / 4,
    );

    $acc = $altitudes[0] + $altitudes[1] + $altitudes[2] + $altitudes[3] + $altitudes[4];
    for( $k = 2 ; $k < $size - 2 ; $k++ ) {
        $output[] = $acc * 0.2;
        $acc += $altitudes[$k + 2] - $altitudes[$k - 2];
    }
    $output[] =
    0.25 * ($altitudes[$size - 1] + $altitudes[$size - 2] + $altitudes[$size - 3] + $altitudes[$size - 4]);
    $output[] =
    ($altitudes[$size - 1] + $altitudes[$size - 2] + $altitudes[$size - 3]) / 3;

    return $output;
}


function trace( $msg ) {
    error_log( $msg );
}
?>
