<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

trait Renderer {

    protected $viewsFolderName = "views";
    protected $viewName;

    public function getViewsFolderName() {
        return $this->viewsFolderName;
    }

    public function setViewsFolderName($folderName) {
        $this->viewsFolderName = $folderName;
    }

    public function getViewsPath() {
        return $this->viewsPath = getcwd() . DIRECTORY_SEPARATOR . $this->getViewsFolderName() . DIRECTORY_SEPARATOR;
    }

    public function setViewName($name) {
        $this->viewName = $name;
    }

    public function getViewName() {
        return $this->viewName;
    }

    public function render($file, $data = array()) {
        $_file = $this->getViewsPath() . $this->getViewName() . DIRECTORY_SEPARATOR . $file . ".php";
        $partialContents = $this->renderFile($_file, $data, TRUE);
        $this->renderPage(array('contents' => $partialContents));
    }

    protected function renderPage($params) {
        $_file = $this->getViewsPath() . $this->getLayout() . ".php";
        echo $this->renderFile($_file, $params['contents'], TRUE);
    }

    protected function renderFile($file, $_data = array(), $return = false) {
        try {
            if (is_array($_data)) {
                $flashes = $this->state()->getAllFlashes();
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

}
