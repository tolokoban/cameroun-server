<?php
class Elevation {
    function __construct() {}

    function get($lat, $lng) {
        $latInt = floor( $lat );
        $latDec = $lat - $latInt;
        $lngInt = floor( $lng );
        $lngDec = $lng - $lngInt;
        $latTxt = '' . abs($latInt);
        while( strlen($latTxt) < 2 ) $latTxt = '0' . $latTxt;
        $lngTxt = '' . abs($lngInt);
        while( strlen($lngTxt) < 3 ) $lngTxt = '0' . $lngTxt;

        $filename = ($lat < 0 ? 'S' : 'N') . $latTxt . ($lng < 0 ? 'W' : 'E') . $lngTxt;
        $filename = "./pri/elevation/" . $filename . '.hgt';

        // Si le fichier n'existe pas, on suppose être dans la mer.
        if (!file_exists( $filename )) {
            error_log("Elevation file is missing: $filename!");
            return -1;
        }

        // Tous les fichiers représentent un carré de 1 lat par 1 lng.
        // Mais ils n'ont pas tous la même taille en fonction de la précision.
        // Les tailles les plus courantes sont 1201x1201x2 et 3601x3601x2.
        $len = filesize( $filename );
        $side = sqrt($len / 2);

        // Trouver la position dans le tile hgt.
        $row = $side - floor( $side * $latDec );
        $col = floor( $side * $lngDec );
        error_log("col=$col, row=$row");

        // Index des 4 points autour de la position recherchée.
        // Le premier chiffre représente la longitude.
        $idx00 = 2 * ($col + $row * $side);
        $idx10 = $idx00 + 2;
        $idx01 = $idx00 - 2 * $side;
        $idx11 = $idx01 + 2;

        $fd = fopen( $filename, 'rb' );
        fseek( $fd, $idx00, SEEK_SET );
        $data = fread( $fd, 2 );
        $ele00 = ord($data[0]) * 256 + ord($data[1]);
        if ($ele00 > 10000) return -1;
        fseek( $fd, $idx01, SEEK_SET );
        $data = fread( $fd, 2 );
        $ele01 = ord($data[0]) * 256 + ord($data[1]);
        if ($ele01 > 10000) return -1;
        fseek( $fd, $idx10, SEEK_SET );
        $data = fread( $fd, 2 );
        $ele10 = ord($data[0]) * 256 + ord($data[1]);
        if ($ele10 > 10000) return -1;
        fseek( $fd, $idx11, SEEK_SET );
        $data = fread( $fd, 2 );
        $ele11 = ord($data[0]) * 256 + ord($data[1]);
        if ($ele11 > 10000) return -1;
        fclose( $fd );

        // Gérer les valeurs inconnues (-1).
        if( $ele00 < 0 ) {
            // 00 is BAD.
            if( $ele01 < 0 ) {
                // -00 -01
                if( $ele10 < 0 ) {
                    // -00 -01 -10
                    if( $ele11 < 0 ) {
                        // -00 -01 -10 -11
                        return -1;
                    } else {
                        // -00 -01 -10 +11
                        return $ele11;
                    }
                } else {
                    // -00 -01 +10
                    if( $ele11 < 0 ) {
                        // -00 -01 +10 -11
                        return $ele10;
                    } else {
                        // -00 -01 +10 +11
                        $ele00 = $ele10;
                        $ele01 = $ele11;
                    }
                }                
            } else {
                // -00 +01
                if( $ele10 < 0 ) {
                    // -00 +01 -10
                    if( $ele11 < 0 ) {
                        // -00 +01 -10 -11
                        return $ele01;
                    } else {
                        // -00 +01 -10 +11
                        $ele00 = $ele01;
                        $ele10 = $ele11;
                    }
                } else {
                    // -00 +01 +10
                    if( $ele11 < 0 ) {
                        // -00 +01 +10 -11
                        $ele00 = $ele01;
                        $ele11 = $ele10;
                    } else {
                        // -00 +01 +10 +11
                        $ele00 = ($ele01 + $ele10 + $ele11) * 0.33333333;
                    }
                }
            }
            
        } else {
            // 00 is GOOD.
            if( $ele01 < 0 ) {
                // +00 -01
                if( $ele10 < 0 ) {
                    // +00 -01 -10
                    if( $ele11 < 0 ) {
                        // +00 -01 -10 -11
                        return $ele00;
                    } else {
                        // +00 -01 -10 +11
                        $ele01 = $ele00;
                        $ele10 = $ele11;
                    }
                } else {
                    // +00 -01 +10
                    if( $ele11 < 0 ) {
                        // +00 -01 +10 -11
                        $ele01 = $ele00;
                        $ele11 = $ele10;
                    } else {
                        // +00 -01 +10 +11
                        $ele01 = ($ele00 + $ele10 + $ele11) * 0.33333333;
                    }
                }                
            } else {
                // +00 +01
                if( $ele10 < 0 ) {
                    // +00 +01 -10
                    if( $ele11 < 0 ) {
                        // +00 +01 -10 -11
                        $ele10 = $ele00;
                        $ele11 = $ele01;
                    } else {
                        // +00 +01 -10 +11
                        $ele10 = ($ele00 + $ele01 + $ele11) * 0.33333333;
                    }
                } else {
                    // +00 +01 +10
                    if( $ele11 < 0 ) {
                        // +00 +01 +10 -11
                        $ele11 = ($ele00 + $ele01 + $ele10) * 0.33333333;
                    }
                }
            }
        }

        // On va opérer une interpolation bilinéaire :
        // https://en.wikipedia.org/wiki/Bilinear_interpolation
        // On commence par interpoler deux points à longitude constante.
        $latOff = $latDec * $side;
        $latOff -= floor( $latOff );
        $lngOff = $lngDec * $side;
        $lngOff -= floor( $lngOff );

        $ele0 = (1 - $latOff) * $ele00 + $latOff * $ele01;
        $ele1 = (1 - $latOff) * $ele10 + $latOff * $ele11;
        // Puis on réalise l'interpolation finale.
        $ele = $lngOff * $ele1 + (1 - $lngOff) * $ele0;

        error_log("latOff=$latOff, lngOff=$lngOff, ele00=$ele00, ele10=$ele10");
        error_log("idx00=$idx00, idx10=$idx10, side=$side");

        return $ele;
    }
}
?>
