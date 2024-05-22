<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\traits;

use \azharFramework\AzharConfigs;

trait Model {

    protected $modelsNamespace = "\models\\";
    protected $modelName;

    public function renderEmailT($file, $data = array()) {
        $oController = \azharFactory\AzharFactory::get('__controller__');
        return $oController->renderEmailT($file, $data);
    }

    public function renderEmail($file, $data = array(), $layout = true) {
        $oController = \azharFactory\AzharFactory::get('__controller__');
        return $oController->renderEmail($file, $data, $layout);
    }

    public function setModel($model) {
        $this->modelName = $model;
    }

    public function getModel() {
        return $this->modelName;
    }

    public function noSQLModel($model = null, $namespace = false, $module = false) {
        if (AzharConfigs::get('rzConfig')['mongoEnv']) {
            return NModel::model($model, $namespace, $module);
        }
        return $this->model($model, $namespace, $module);
    }

    public function model($model = null, $namespace = false, $module = false) {
        $namespaceModel = $this->modelsNamespace;
        if ($model === null) {
            $model = $namespaceModel . $this->getModel();
            return new $model;
        }
        if ($module) {
            $namespaceModel .= $this->getCalligNamespace();
        }
        if ($namespace) {
            $namespaceModel = $this->nModel($namespaceModel);
        }
        $model = $namespaceModel . $model;
        return new $model();
    }

    public function nModel($namespaceModel) {
        $appNamespace = AzharConfigs::getNthKey("app", "namespace");
        if ($appNamespace != '') {
            return $namespaceModel . $appNamespace . "\\";
        }
        return $namespaceModel;
    }

    private function getCalligNamespace() {
        $oReflection = new \ReflectionClass(get_called_class());
        return $this->parseClassName($oReflection->getNamespaceName());
    }

    private function parseClassName($rawClassName) {
        $arr = explode("\\", $rawClassName);
        return (isset($arr[1]) && $arr[1] != '' ? $arr[1] . "\\" : '');
    }

}