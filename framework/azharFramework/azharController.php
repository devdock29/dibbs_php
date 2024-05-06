<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

class azharController {

    use UtilityTrait;
    use traits\Model;

    protected $layout = "layout/main";
    protected $viewsPath;
    protected $emailPrefix = "email";
    protected $emailLayout = "layout/email/main";
    protected $twigEmailLayout = "layout/main.html";
    protected $twigPrefix = "twig";
    protected $twigViewsAdditionalPath = "";
    protected $twigLayout = "layout/main.html";
    protected $viewName;
    protected $viewsFolderName = "views";
    protected $scriptFiles = array();
    protected $cssFiles = array();
    protected $jsGlobal = array();
    protected $partial = false;
    protected $isGuest = false;
    protected $actionName;
    protected $globalData;
    protected $globalDataKey = "__data__";

    public function setGlobalData($data) {
        $this->globalData = $data;
    }

    public function getGlobalData() {
        return $this->globalData;
    }

    public function setActionName($name) {
        $this->actionName = $name;
    }

    public function getActionName() {
        return $this->actionName;
    }

    public function setTLayout($value) {
        $this->twigLayout = $value;
    }

    public function getTLayout() {
        return $this->twigLayout;
    }

    public function setLayout($value) {
        $this->layout = $value;
    }

    public function getLayout() {
        return $this->layout;
    }

    public function setEmailLayout($value) {
        $this->emailLayout = $value;
    }

    public function getEmailLayout() {
        return $this->emailLayout;
    }

    public function setViewName($name) {
        $this->viewName = $name;
    }

    public function getViewName() {
        return $this->viewName;
    }

    public function getLangIndex() {
        //considering view name and lang array page index should be same
        return $this->viewName;
    }

    public function getViewsFolderName() {
        return $this->viewsFolderName;
    }

    public function setViewsFolderName($folderName) {
        $this->viewsFolderName = $folderName;
    }

    public function getViewsPath() {
        return $this->viewsPath = getcwd() . DIRECTORY_SEPARATOR . $this->getViewsFolderName() . DIRECTORY_SEPARATOR;
    }

    public function getTwigViewsPath($addOnPath = '') {
        return $this->getViewsPath() . $this->twigPrefix . DIRECTORY_SEPARATOR . $addOnPath;
    }

    public function setTwigViewsAdditionalPath($path) {
        $this->twigViewsAdditionalPath = $path . DIRECTORY_SEPARATOR;
    }

    public function getTwigFilePath($file) {
        return $this->getViewName() . DIRECTORY_SEPARATOR . $file . '.html';
    }

    public function errorAction($params = array()) {
        if (isset($params['msg'])) {
            echo $params['msg'];
        } else {
            echo "Invalid action";
        }
    }

    public function loadController($name) {
        $controllerName = ucfirst($name) . 'Controller';
        if (class_exists($controllerName)) {
            return new $controllerName();
        } else {
            //echo "Invalid Controller";
            return false;
        }
    }

    public function loadAction($actionName, $oController, $partial = true) {
        $actionName = strtolower($actionName) . 'Action';
        if (method_exists($oController, $actionName)) {
            return $oController->$actionName();
        } else {
            return false;
        }
    }

    public function beforeRender() {

    }

    private function mergeFlashes($data) {
        if (is_array($data)) {
            $flashes = $this->state()->getAllFlashes();
            if ($flashes !== null) {
                $data = array_merge($data, $flashes);
            } return $data;
        } else {
            $flashes = $this->state()->getAllFlashes();
            if ($flashes !== null) {
                return $flashes;
            } else {
                echo "Data should be an array";
                exit;
            }
        }
    }

    public function renderT($file, $_data = array(), $addOnPath = '') {
        echo $this->obtainRenderT($file, $_data, $addOnPath);
    }

    public function renderPartial($view, $data = array()) {
        // if (strncmp($view, "//", 2) === 0) {
        // $view = ltrim($view, "/");
        // } 
        $file = $this->getViewsPath() . $view . ".php";
        return $this->renderFile($file, $data, true);
    }

    public function render($file, $data = array()) {
        $_file = $this->getViewsPath() . $this->getViewName() . DIRECTORY_SEPARATOR . $file . ".php";
        $partialContents = $this->renderFile($_file, $data, TRUE);
        $this->renderPage(array('contents' => $partialContents));
    }

    public function renderEmailT($file, $data = array()) {
        ob_start();
        $_data = $data;
        $_data['__Emaillayout__'] = $this->twigEmailLayout;
        $this->renderT($file, $_data, $this->emailPrefix . DIRECTORY_SEPARATOR);
        $contents = ob_get_contents();
        ob_clean();
        return $contents;
    }

    public function renderEmail($file, $data = array(), $layout = true) {
        $_file = $this->getViewsPath() . $this->emailPrefix . DIRECTORY_SEPARATOR . $this->getViewName() . DIRECTORY_SEPARATOR . $file . ".php";
        $partialContents = $this->renderFile($_file, $data, TRUE);
        if ($layout === false) {
            return $partialContents;
        } $_file = $this->getViewsPath() . $this->getEmailLayout() . ".php";
        return $this->renderFile($_file, ['contents' => $partialContents, 'data' => $data], TRUE);
    }

    public function obtainRenderT($file, $_data = [], $addOnPath = '') {
        $this->beforeRender($_data);
        $data = $this->mergeFlashes($_data);
        // include and register Twig auto-loader
        require_once 'Twig/Autoloader.php';
        \Twig_Autoloader::register();
        try {
            // specify where to look for templates
            $tmplPath = $this->getTwigViewsPath($addOnPath);
            $loader = new \Twig\Loader\FilesystemLoader($tmplPath);
            // initialize Twig environment 
            $errorLogsMod = (AzharConfigs::getNthKey("errorLogs", "debug") == 0 ? 0 : 1);
            $attr = array();
            if ($errorLogsMod === 0) {
                $attr['debug'] = TRUE;
            } 
            $twig = new \Twig\Environment($loader, $attr);
            $localeMsgFilter = new \Twig\TwigFilter('localeMsg', array('\azharFramework\lang\Lang', 'get'));
            $twig->addFilter($localeMsgFilter);
            $rtfFilter = new \Twig\TwigFilter('rtf', array('\azharFramework\traits\App', 'rtf'), array('is_safe' => array('html')));
            $twig->addFilter($rtfFilter);
            $stripslashesFilter = new \Twig\TwigFilter('stripslashes', function($str) {
                return stripslashes($str);
            });
            $twig->addFilter($stripslashesFilter);
            $rtrimFilter = new \Twig\TwigFilter('rtrim', function($str, $character_mask = ' ') {
                return rtrim($str, $character_mask);
            });
            $twig->addFilter($rtrimFilter);
            $twigFunc = new \Twig\TwigFunction('nas', array('\azharFramework\twig\Nas', 'get'));
            $twig->addFunction($twigFunc);
            $twigEncodedFunc = new \Twig\TwigFunction('encodeIntShortcut', array('\azharFramework\twig\Nas', 'encodeIntShortcut'));
            $twig->addFunction($twigEncodedFunc);
            $twigDecodedFunc = new \Twig\TwigFunction('decodeIntShortcut', array('\azharFramework\twig\Nas', 'decodeIntShortcut'));
            $twig->addFunction($twigDecodedFunc);
            $twigConstFunc = new \Twig\TwigFunction('constant', array('\azharFramework\twig\Nas', 'constant'));
            $twig->addFunction($twigConstFunc);
            if ($errorLogsMod === 0) {
                //$twig->addExtension(new \Twig_Extension_Debug());
            } else {
                $twigFunc_dump = new \Twig\TwigFunction('dump', array('\azharFramework\twig\Nas', 'get'));
                $twig->addFunction($twigFunc_dump);
            }
            // load template
            $template = $twig->loadTemplate($this->twigViewsAdditionalPath . $this->getTwigFilePath($file));
            // set template variables
            // render template
            $data['__layout__'] = $this->twigLayout;
            $data[$this->globalDataKey] = $this->getGlobalData();
            return $template->render($data);
        } catch (Exception $e) {
            die('ERROR: ' . $e->getMessage());
        }
    }

    protected function renderPage($params) {
        $_file = $this->getViewsPath() . $this->getLayout() . ".php";
        echo $this->renderFile($_file, $params['contents'], TRUE);
    }

    protected function renderFile($file, $_data = array(), $return = false) {
        try {
            if (is_array($_data)) {
                @$flashes = $this->state()->getAllFlashes();
                if ($flashes !== null) {
                    $_data = array_merge($_data, $flashes);
                }
                extract($_data, EXTR_PREFIX_SAME, 'data');
            } else {
                $data = $_data;
            }
            if ($return) {
                ob_start();
                ob_implicit_flush(false);
                require ($file);
                return ob_get_clean();
            } else
                require ($file);
        } catch (\Exception $ex) {
            $this->errorAction($params['msg']);
        }
    }

    public function getControllerName($full = false) {
        if ($full) {
            return get_called_class();
        }
        return (new \ReflectionClass($this))->getShortName();
    }

    public function renderOtherView($proposedController, $actionName, $data = array()) {
        $controllerName = ucfirst($proposedController) . 'Controller';
        $actionName = strtolower($actionName) . 'Action';
        if (class_exists($controllerName)) {
            $oController = new $controllerName();
            $oController->setViewName($proposedController);
            if (method_exists($oController, $actionName)) {
                $result = $oController->$actionName();
            } else {
                $result = $oController->errorAction();
            }
        } else {
            echo "Invalid Controller";
        }
    }

    /** Call this function to add javascript global variables * */

    public function addJsGlobal($key, $val) {
        $this->jsGlobal[$key] = $val;
    }

    /** It will load all javascript global varible added into framework by * addJsGlobal. * */

    public function loadJsGlobal() {
        if (count($this->jsGlobal)) {
            $js = "";
            echo $js;
        }
    }

    /** It will load all CSS files added into framework by * addCss. * */

    public function loadCss() {
        $css = "";
        foreach ($this->cssFiles as $file) {
            $css .= '';
        }
        echo $css;
    }

    /** Call this function to add css files * */

    public function addCss($cssFile) {
        $this->cssFiles[] = $cssFile;
    }

    /** It will load all javascript files added into framework by * addScript. * */

    public function loadScripts() {
        $scripts = "";
        foreach ($this->scriptFiles as $file) {
            $scripts .= "";
        }
        echo $scripts;
    }

    /** Call this function to add javascript files * */

    public function addScript($scriptFile) {
        $this->scriptFiles[] = $scriptFile;
    }

    public function beforeAction() {
        
    }

    public function afterAction() {
        
    }

}
