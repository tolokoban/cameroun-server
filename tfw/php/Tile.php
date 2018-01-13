<?php
/*
   Permet de déterminer le tile de 256x256 pixels qui se trouve en `$lat`, `$lng` pour le zoom `$zoom`.

   $this->col: Colonne de la tuile.
   $this->row: Ligne de la tuile.


 */

// http://wiki.openstreetmap.org/wiki/Slippy_map_tilenames
class Tile {
    function __construct($lat=0, $lng=0, $zoom=10) {
        $n = pow(2, $zoom);
        $col = floor($n * (($lng + 180) / 360));
        $latRad = M_PI * $lat / 180;
        $row = floor($n * (1 - (log(tan($latRad) + 1 / cos($latRad)) / M_PI)) / 2);
        $this->zoom = $zoom;
        $this->col = $col;
        $this->row = $row;
        $this->lng = $col / $n * 360 - 180;
        $this->lat = rad2deg(atan(sinh(pi() * (1 - 2 * $row / $n))));
        $this->lngW = $this->lng;
        $this->lngE = ($col + 1) / $n * 360 - 180;
        $this->latN = $this->lat;
        $this->latS = rad2deg(atan(sinh(pi() * (1 - 2 * ($row + 1) / $n))));
    }
}
?>
