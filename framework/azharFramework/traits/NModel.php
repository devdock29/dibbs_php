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
use AzharConstants;

class NModel {

    protected static $modelsNamespace = "\models\\noSQL\\";
    protected static $modelName;

    public static function setNModel($model) {
        static::$modelName = $model;
    }

    public static function getNModel() {
        return static::$modelName;
    }

    public static function model($model = null, $namespace = false, $module = false) {
        $namespaceNModel = static::$modelsNamespace;
        if ($model === null) {
            $model = $namespaceNModel . static::getNModel();
            return new $model;
        }
        if ($module) {
            $namespaceNModel .= static::getCalligNamespace();
        }
        if ($namespace) {
            $namespaceNModel = static::nNModel($namespaceNModel);
        }
        $model = $namespaceNModel . $model;
        return new $model();
    }

    public static function nNModel($namespaceNModel) {
        $appNamespace = AzharConfigs::getNthKey("app", "namespace");
        if ($appNamespace != '') {
            return $namespaceNModel . $appNamespace . "\\";
        }
        return $namespaceNModel;
    }

    private function getCalligNamespace() {
        $oReflection = new \ReflectionClass(get_called_class());
        return static::parseClassName($oReflection->getNamespaceName());
    }

    private function parseClassName($rawClassName) {
        $arr = explode("\\", $rawClassName);
        return (isset($arr[1]) && $arr[1] != '' ? $arr[1] . "\\" : '');
    }

}