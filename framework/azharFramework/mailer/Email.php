<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\mailer;

use helpers\Common;

class Email {

    use \azharFramework\UtilityTrait;

    public static function sendMail($params) {

        $toEmail = $params['emailAddress'];
        $subject = $params['subject'];
        $textMessage = $params['message'];
        $fromName = $params['fromName'];
        $emailFrom = $params['emailFrom'];
        $replyTo = $params['replyTo'];
        $xDomain = isset($params['xDomain']) ? 'x-' . $params['xDomain'] : 'x-Dibbs';

        /*
          if ($to == '' || $mailSubject == ""  || $fromEmail == ""  || $replyTo == ""  || $fromName == ""  || $website == ""  || $mailMessageHTML == "" ){

          return array("type"=>"err", "msg"=>"Some Fields are missing. Please send all of the required fields (To, mailSubject, fromEmail, replyTo, fromName, website, messageHTML) ");
          }
         */

        $cc = (isset($params['cc']) && $params['cc'] != '' ? $params['cc'] : '');
        $bcc = (isset($params['bcc']) && $params['bcc'] != '' ? $params['bcc'] : '');
        $toName = (isset($params['toName']) && $params['toName'] != '' ? $params['toName'] : 'User');
        $attachment = (isset($params['attachment']) && $params['attachment'] != '' ? $params['attachment'] : '');
        $html = "3";
        $userFromEmail = (isset($params['userFromEmail']) && $params['userFromEmail'] != '' ? $params['userFromEmail'] : 'N');

        $message = '';

        $replyToEmail = ($replyTo == '' ? $emailFrom : $replyTo);
        if (isset($params['noIp']) && $params['noIp'] === true) {
            $ip = '';
        } else {
            $ip = Common::getIP();
        }
        $from = "$fromName <" . $emailFrom . ">";
        $time = gmdate("d M Y H:i:s O");
        $to = "";
        if (stristr($toEmail, ",")) {
            $to .= "$toEmail";
        } else {
            $to .= "$toEmail";
            //$to = "$toName <$toEmail>";
        }

        $main_boundary = "----=_NextPartM_" . md5(rand());
        //$text_boundary = "----=_NextPartT_" . md5(rand());
        //$html_boundary = "----=_NextPartH_" . md5(rand());

        $headers = "Received: from [$ip] by " . $params['domainBrandName'] . " with HTTP; $time\n";
        $headers .= "From: $from\n";
        $headers .= "Reply-To: $replyToEmail\n";
        $headers .= (isset($cc) && $cc != "" ? "CC: $cc\n" : "");
        $headers .= (isset($bcc) && $bcc != "" ? "BCC: $bcc\n" : "");
        $headers .= "X-Originating-IP: $ip\n";
        //$headers .= $xDomain . ": 1\n";

        if ($html == 1) {
            $headers .= "MIME-Version: 1.0\n";
            $headers .= "Content-Type: text/plain;\n\tboundary=\"$main_boundary\"\n";
        } elseif ($html == 2) {
            $headers .= "MIME-Version: 1.0\nContent-type: text/html\n";
        } elseif ($html == 3) {
            $message .= "--$main_boundary\n";
            $headers .= "MIME-Version: 1.0\n";
            $headers .= "Content-Type: multipart/mixed;\n boundary=\"$main_boundary\"\n";
            $message .= "Content-Type: text/html; charset=\"utf-8\"\n";
            $message .= "Content-Transfer-Encoding: 7bit\n\n";
        }
        $message .= ($textMessage != "") ? "$textMessage" : "Text portion of HTML Email";
        if (isset($attachment) && $attachment != "") {
            $files = explode(",", $attachment);
            $totFiles = count($files);
            for ($i = 0; $i < $totFiles; $i++) {
                $attfile = trim($files[$i]);
                if (trim($attfile) != "" && file_exists($attfile)) {
                    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($fileInfo, $attfile);
                    $file_name = basename($attfile);
                    $fp = fopen($attfile, "r");
                    $fcontent = "";
                    while (!feof($fp)) {
                        $fcontent .= fgets($fp, 1024);
                    }
                    $fcontent = chunk_split(base64_encode($fcontent));
                    @fclose($fp);

                    $message .= "\n--$main_boundary\n";
                    $message .= "Content-Type: $mimeType\n";
                    $message .= "Content-Transfer-Encoding: base64\n";
                    $message .= "Content-Disposition: attachment; filename=\"$file_name\"\n";
                    $message .= "Content-ID: <$file_name>\n\n";
                    $message .= $fcontent;
                } // End of if (trim($attfile) != "")
            }
        }
        $message .= "\n--$main_boundary--\n";

        if ($message == 'Text portion of HTML Email') {
            $bt = debug_backtrace();
            mail("azharwaris@gmail.com", "Email Error:$subject:$to", $message . "DEBUG BACKTRACE: " . print_r($bt, true), $headers, "-f $emailFrom");
        } else {
            //echo "<b>Subject : </b>".$subject."<br />  <b>Message : </b>". $message."<br />  <b>To : </b>". $to."<br /> <b>From Email : </b>" . $emailFrom."<br />";
            if ($subject != '' && $message != '' && $to != '') {
                if (@mail($to, $subject, $message, $headers, "-f $emailFrom")) {
                    //echo "Email sent"; exit;
                    return true;
                } else {
                    //echo "Email not sent"; exit;
                    $bt = debug_backtrace();
                    mail("azharwaris@gmail.com", "Email Error:$subject:$to", $message . "DEBUG BACKTRACE: " . print_r($bt, true), $headers, "-f $emailFrom");
                    return false;
                }
            } else {
                $bt = debug_backtrace();
                mail("azharwaris@gmail.com", "Email Error:$subject:$to", $message . "DEBUG BACKTRACE: " . print_r($bt, true), $headers, "-f $emailFrom");
                return false;
            }
        }
        return false;
    }

}