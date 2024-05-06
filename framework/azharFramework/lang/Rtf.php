<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\lang;

use azharFramework\AzharConfigs;

class Rtf {

    private static $search = array('[br]', '[ul]', '[/ul]', '[li]', '[/li]', '[strong]', '[/strong]', '[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]', '[s]', '[/s]', '[code]', '[/code]', '[quote]', '[/quote]', '[/url]');
    private static $replace = array('<br />', '<ul>', '</ul>', '<li>', '</li>', '<strong>', '</strong>', '<b>', '</b>', '<em>', '</em>', '<u>', '</u>', '<del>', '</del>', '<pre>', '</pre>', '<blockquote>', '</blockquote>', '</a>');
    private static $searchRegex = array('/(\[url=)([^\]]+)(\])/', '/(\[url\])([^\]]+)(\])/', '/(\[img\])([^\[\/img\]]+)(\[\/img\])/');
    private static $replaceRegex = array('<a href="\2">', '<a href="\2">\2', '<img src="\2" alt="" />');
    private static $applicationFilters = array();

    public static function getApplicationFilters() {
        if (empty(static::$applicationFilters)) {
            static::$applicationFilters = AzharConfigs::getNthKey("filters", "constants");
        }
        return static::$applicationFilters;
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('rtf', array($this, 'bbCodeFilter'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Converts BBCode tag into HTML tags
     *
     * @param $string String source
     *
     * @return string
     */
    public static function get() {
        $arguments = func_get_args();
        $string = array_shift($arguments);
        // Parse any additional parameters to alter tha behaviour of this extension
        self::parseArguments($arguments);
        return preg_replace(static::getSearchRegex(), static::getReplaceRegex(), static::filter($string));
    }

    private static function filter($string) {
        return self::filterAppVariables(self::filterHTMLTags($string));
    }

    private static function filterHTMLTags($string) {
        return str_replace(static::getSearch(), static::getReplace(), $string);
    }

    private static function filterAppVariables($string) {
        $search = static::getApplicationFilters();
        $replacements = array();
        foreach ($search as $val) {
            $replacements[] = constant($val);
        }
        return str_replace($search, $replacements, $string);
    }

    private static function parseArguments($arguments) {
        foreach ($arguments as $argument) {
            if (is_string($argument)) {
                switch ($argument) {
                    case 'nofollow':
                        $this->replaceRegex[0] = '<a rel="nofollow" href="\2">';
                        $this->replaceRegex[1] = '<a rel="nofollow" href="\2">\2';
                        break;
                    // Can add more cases to add more functionality.
                    default:
                        break;
                }
            }
        }
    }

    // Getter functions
    private static function getSearch() {
        return static::$search;
    }

    private static function getReplace() {
        return static::$replace;
    }

    private static function getSearchRegex() {
        return static::$searchRegex;
    }

    private static function getReplaceRegex() {
        return static::$replaceRegex;
    }

    public static function getName() {
        return 'rtf';
    }

}
