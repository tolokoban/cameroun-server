<?php
class TP3Mail {
    function send( $to,
                   $subject,
                   $msg,
                   $cc="",
                   $bcc="",
                   $from="Trail-Passion <contact@trail-passion.net>" ) {
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= "To: $to\r\n";
        $headers .= "From: $from\r\n";
        if ($cc != "") {
            $headers .= "Cc: $cc\r\n";
        }
        if ($bcc != "") {
            $headers .= "Bcc: $cc\r\n";
        }
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
             . "<html><head>"
             . "<style>div.tfw-pointerevent{position:fixed;display:block;left:0;top:0;right:0;bottom:0;z-index:99999}
.tp3-mail{background-color:#efe;padding:32px 0;text-align:justify;background-image:url(tp3.Mail/background.png);background-repeat:no-repeat;background-position:right bottom;background-size:256px 256px}.tp3-mail-body{width:640px;padding:16px;margin:16px auto 64px auto;border:thin solid #000;color:black;font-size:12pt;background-color:#fea;box-shadow:8px 8px 4px #030;background-image:url(tp3.Mail/background.png);background-repeat:no-repeat;background-position:right top;background-size:256px 256px}.tp3-mail-body a,.tp3-mail-body a:visited,.tp3-mail-signature a,.tp3-mail-signature a:visited{color:#900;font-weight:600}.tp3-mail-box{background:#fff;margin:2px;padding:2px 8px;border:thin solid #000}.tp3-mail-signature{width:640px;margin:auto;margin-top:24px;font-size:8pt;text-align:right}
.tfw-widget-no-selection{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}.tfw-widget-hidden1.tfw-widget-hidden2.tfw-widget-hidden3{display:none}</style>"
             . "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />"
             . "<title>$subject</title></head>\n"
             . "<body class='tp3-mail'>"
             . "<div class='tp3-mail-body'>$msg</div>"
             . "<div class='tp3-mail-signature'>Follow <a href='http://www.trail-passion.net'>trail-passion.net</a> on <a href='https://twitter.com/trail_passion'>Twitter</a></div>"
             . "</body></html>";

        $content = file_get_contents( dirname(__FILE__) . '/../tpl/mail.html' );
        $content = str_replace( '{{subject}}', $subject, $content );
        $content = str_replace( '{{msg}}', $msg, $content );

        echo "<b>To:</b> $to<br/>\n";
        echo "<b>Subject:</b> $subject<br/>\n";
        echo "<b>Cc:</b> $cc<br/>\n";
        echo "<b>Bcc:</b> $bcc<br/>\n";
        echo "<b>From:</b> $from<br/>\n";
        echo "<div style='margin:8px; padding:8px; border:2px solid #000; width:800px'>$content</div>\n";
        return mail( $to, $subject, $content, $headers );
    }
}
?>
