<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

use helpers\URL;

class MetaModel extends AppModel implements \azharFramework\interfaces\MetaInterface {

    protected $googleCode; // = 'UA-181333-5';
    protected $head;
    protected $title;
    protected $keywords;
    protected $description;

    public function __construct() {
        $this->title = $this->localeMsg('1', 'site', false);
        $this->keywords = $this->localeMsg('2', 'site', false);
        $this->description = $this->localeMsg('3', 'site', false);
    }

    public function setGoogleCode($code) {
        $this->googleCode = $code;
    }

    public function getGoogleCode() {
        return $this->googleCode;
    }

    public function getHead() {
        if ($this->head === null) {
            $this->head = $this->header();
        }
        return $this->head;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getKeywords() {
        return $this->keywords;
    }

    public function getDesc() {
        return $this->description;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDesc($desc) {
        $this->description = $desc;
    }

    public function setKeywords($kw) {
        $this->keywords = $kw;
    }

    public function setHead($params) {
        $this->head = $this->header($params);
    }

    public function header($params = array()) {
        $retValue = $this->headerCommon($params);
        $retValue .= '<meta name="twitter:site" content="__TWITTER_USER__"/>';
        $retValue .= '<meta name="msvalidate.01" content="__BING_VERIFICATION__" /> '; // Bing 
        $retValue .= $this->facebookPixel($params);
        return $retValue;
    }

    public function headerCommon($params) {
        $page = (isset($params['page']) ? "article" : "website");
        $canonical = (!empty($params['canonical']) ? URL::addhttp($params['canonical']) : $this->baseURL('http:'));
        $ogLink = (!empty($params['ogLink']) ? URL::addhttp($params['ogLink']) : URL::addhttp($this->url()) );
        $previous = $params['previous'] ?: NULL;
        $next = $params['next'] ?: NULL;
        $allowIndex = (isset($params['allowIndex']) && $params['allowIndex'] == false ? false : true);

        $objConfig = new ConfigModel();
        $getConfigData = $objConfig->info();

        if (!empty($getConfigData['siteOgLogo']))
            $ogLogoDefault = $getConfigData['siteOgLogo'] . ".gif";
        else
            $ogLogoDefault = 'fb-logo.gif';
        $defaultImg = \helpers\URL::get() . '/i/' . $this->getLocale() . '/' . $ogLogoDefault;
        $ogImage = (isset($params['ogImage']) && $params['ogImage'] != '' ? $params['ogImage'] : $defaultImg); //ASSETS_URL . ASSETS_APP_FOLDER . '/i/siteIco.gif'


        $retValue = '';
        if (isset($params['favicon'])) {
            $retValue .= '<link rel="shortcut icon" href="' . $params['favicon'] . 'favicon.ico" type="image/x-icon" />';
        }
        if (isset($params['title'])) {
            $this->title = $params['title'];
        }
        if (isset($params['keywords'])) {
            $this->keywords = $params['keywords'];
        }
        if (isset($params['description'])) {
            $this->description = $params['description'];
        }
        $retValue .= "\n" . '<title>' . $this->title . '</title>';
        $retValue .= "\n" . '<meta name="keywords" content="' . $this->keywords . '" />';
        $retValue .= "\n" . '<meta name="description" content="' . $this->description . '" />';
        if (!empty($params['no-referrer'])) {
            // added for Forgot Password // reset password
            $retValue .= "\n" . '<meta name="referrer" content="no-referrer" />' . "\n";
        }
        if (isset($canonical)) {
            $retValue .= ($canonical != '' ? '<link rel="canonical" href="' . str_ireplace('/ar/', '/', $canonical) . '" />' : '');
        }
        if (isset($previous)) {
            $retValue .= ($previous != '' ? '<link rel="prev" href="' . str_ireplace('/ar/', '/', $previous) . '" />' : '');
        }
        if (isset($next)) {
            $retValue .= ($next != '' ? '<link rel="next" href="' . str_ireplace('/ar/', '/', $next) . '" />' : '');
        }
        if ($allowIndex) {
            $urlToIndex = \helpers\URL::addhttp($this->url());
            $host = explode('.', $urlToIndex);
            $allowUrls2Index = array('https://www', 'http://www');
            if (in_array($host[0], $allowUrls2Index)) {
                $retValue .= "\n" . '<meta name="robots" content="ALL, FOLLOW,INDEX" />';
            } else {
                $retValue .= "\n" . '<meta name="robots" content="noindex" />';
                $retValue .= "\n" . '<meta name="googlebot" content="noindex" />';
            }
        } else {
            $retValue .= "\n" . '<meta name="robots" content="noindex, nofollow" />';
            $retValue .= "\n" . '<meta name="googlebot" content="noindex, nofollow" />';
        }


        if (!isset($params['author'])) {
            $retValue .= "\n" . '<meta name="author" content="' . DOMAIN_BRAND_NAME . '" />
		';
        }
        $retValue .= $this->google();
        $appInfoMeta = "";

        if (!empty($params['DOMAIN_BRAND_NAME'])) {
            $DOMAIN_BRAND_NAME = $params['DOMAIN_BRAND_NAME'];
        } else {
            $DOMAIN_BRAND_NAME = DOMAIN_BRAND_NAME;
        }

        $retValue .= '<meta property="og:site_name" content="' . $DOMAIN_BRAND_NAME . '"/>
        <!-- Socializer Tags -->' . $appInfoMeta . ' 
        <meta property="og:type" content="' . ($page == 'index' ? 'website' : 'article') . '" />
        <meta property="fb:app_id" content="FB_ID"/>
        <meta property="og:title" content="' . $this->title . '"/>
        <meta property="og:url" content="' . $ogLink . '"/>';


        $desc = (isset($params['description'])) ? $params['description'] : $this->description;
        $retValue .= "\n" . '<meta property="og:description" content="' . $desc . '"/>';


        $retValue .= '<meta property="og:image" content="' . $ogImage . '" />';
        $retValue .= '<!-- Socializer Tags --> ';

        if (!defined('SHOW_APPLINKS') || (defined('SHOW_APPLINKS') && SHOW_APPLINKS == 'Y')) {
            $retValue .= $this->appLinks();
        }

        $retValue .= '<!-- Twitter Cards Tags --> 
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content="' . $this->title . '"/>';

        $retValue .= "\n" . '<meta name="twitter:description" content="' . $desc . '"/>';

        $retValue .= '<meta name="twitter:image" content="' . str_replace('cpl_', '', $ogImage) . '" />
        <!-- Twitter Cards Tags --> ';
        $getServerAdd = explode(".", $_SERVER['REMOTE_ADDR']);
        $retValue .= "<!-- " . $getServerAdd[0] . '.*.*.' . $getServerAdd[3] . ' - ' . gethostname() . " -->";

        $retValue .= $this->getAdsHeader($params);
        return $retValue;
    }

    public function facebookPixel($params) {
        if (SITE_AT == 'local' || !(\helpers\Analytics::isAllowed('facebook'))) {
            return '';
        }
        return "<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','//connect.facebook.net/en_US/fbevents.js');

fbq('init', '1672155926338290');
fbq('track', \"PageView\");
</script>
<noscript><img height=\"1\" width=\"1\" style=\"display:none\"
src=\"https://www.facebook.com/tr?id=1672155926338290&ev=PageView&noscript=1\"
/></noscript>
<!-- End Facebook Pixel Code -->";
    }

    public function google() {
        if (SITE_AT == 'local' || !(\helpers\Analytics::isAllowed('google'))) {
            return '';
        }
        $objConfig = new ConfigModel();
        $getConfigData = $objConfig->configData();
        $crazyEggFlag = $getConfigData != null ? $getConfigData[0]['crazy_egg'] : null;
        $browserCheckFlag = $getConfigData != null ? $getConfigData[0]['browser_check'] : null;
        $crazyEggScript = $browserCheckScript = '';
        if ($crazyEggFlag == 'Y') {
            $crazyEggScript = "<script type='text/javascript'>
		setTimeout(function(){var a=document.createElement('script');
		var b=document.getElementsByTagName('script')[0];
		a.src=document.location.protocol+'//script.crazyegg.com/pages/scripts/0038/6800.js?'+Math.floor(new Date().getTime()/3600000);
		a.async=true;a.type='text/javascript';b.parentNode.insertBefore(a,b)}, 1);
		</script>";
        }
        if ($browserCheckFlag == 'Y') {
            $browserCheckScript = '<script> 
                var $buoop = {vs:{i:10,f:-4,o:-4,s:8,c:-4},unsecure:false,api:4,reminder:24,reminderClosed:72,newwindow:true}; 
                function $buo_f(){ 
                 var e = document.createElement("script"); 
                 e.src = "//browser-update.org/update.min.js"; 
                 document.body.appendChild(e);
                };
                try {document.addEventListener("DOMContentLoaded", $buo_f,false)}
                catch(e){window.attachEvent("onload", $buo_f)}
                </script><style>body .buorg {position:relative;text-align:center;background-image:none !Important}</style>';
        }
        return "<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','__gaTracker');
	
	  __gaTracker('create', '" . $this->googleCode . "', 'auto');
	  __gaTracker('send', 'pageview');\n 
          </script>";
        echo $crazyEggScript . $browserCheckScript;
    }

    public function appLinks() {
        $meta = '<meta name="google-play-app" content="app-id=__GOOGLE_APP_ID__">
        <meta name="apple-itunes-app" content="app-id=__APPLE_APP_ID__">';

        return $meta;
    }

}
