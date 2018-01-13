<html><head>
    <script>
     function init() {
         var arr =
         <?php
         /*
            Open Graph is a protocol to share webpages over FaceBook: http://ogp.me/
          */
         include_once("php/db.inc");
         include_once("php/Preview.php");

         $id = intVal($_GET["id"]);
         $stm = $DB->query("SELECT id, name FROM " . $DB->table("trace"));
         $arr = Array();
         if( $stm ) {
             while( $row = $stm->fetch() ) {
                 $id = $row['id'];
                 $name = $row['name'];
                 $arr[] = Array( $id, $name );
             }
             echo json_encode( $arr );
         }
         ?>;
         var img = document.getElementById( "image" );
         var name = '';
         
         function next() {
             if( arr.length === 0 ) return;
             var item = arr.pop();
             var id = item[0];
             name = item[1];
             console.log( id, name );
             img.src = "preview.php?id=" + id + "&fb=1";
         }

         img.onerror = function() {
             console.error(img.src);
             next();
         };
         img.onload = function() {
             document.getElementById("name").textContent = name;
             next();
         };

         next();
     }</script></head><body onload="init()">
        <center>
            <h1 id="name">Loading...</h1>
            <img id="image"/>
        </center>
     </body></html>
