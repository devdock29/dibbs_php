<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\data;

class Paginate {

    private $url;
    private $baseUrl;
    private $urlParams;
    private $defaultLanguage;
    private $messages;

    public function __construct($params) {
        $this->url = $params['url'];
        $this->baseUrl = $params['baseURL'];
        $this->urlParams = $params['getParams'];
        $this->defaultLanguage = (isset($params['language'])) ? $params['language'] : 'en';
        $this->messages = (isset($params['messages'])) ? $params['messages'] : array('showing' => 'Showing', 'of' => 'of', 'previous' => 'Previous', 'next' => 'Next');
    }

    public function getPaging($param) {
        global $mkmlang;
        $offset = $param['offset'];
        $totalRecords = $param['totalRecords'];
        $perPage = $param['perPage'];
        $url_params = $this->urlParams;
        $currentPageTotalRecords = (!isset($param['currentPageTotalRecords']) || $param['currentPageTotalRecords'] == '' ? '' : $param['currentPageTotalRecords']);
        $pages = (!isset($param['pages']) || $param['pages'] == '' ? 'N' : $param['pages']);
        $displayNote = (!isset($param['displayNote']) || $param['displayNote'] == '' ? 'N' : $param['displayNote']);
        $messageOnly = (!isset($param['messageOnly']) || $param['messageOnly'] == '' ? 'N' : $param['messageOnly']);
        $textClass = (!isset($param['textClass']) || $param['textClass'] == '' ? '' : $param['textClass']);
        $activeStyle = (!isset($param['activeStyle']) || $param['activeStyle'] == '' ? 'active' : $param['activeStyle']);
        $showNP10 = (!isset($param['showNP10']) || $param['showNP10'] == '' ? 'N' : $param['showNP10']);
        $isReWrite = (!isset($param['rewrite']) || $param['rewrite'] == '' ? 'N' : $param['rewrite']);

        $showing = $this->messages['showing'];
        $of = $this->messages['of'];
        $previous = $this->messages['previous'];
        $next = $this->messages['next'];

        $classString = (trim($textClass) != '' ? " class='$textClass' " : '');
        $num = ($totalRecords < $perPage ? $totalRecords : $perPage);
        $msg = $str = "";
        $str .= '<div class="dataTables_paginate paging_simple_numbers" id="example5_paginate">';
        $lastNumber = 0;
        $myNum = ($offset + $num > $totalRecords ? $totalRecords : $offset + $num);

        if (trim($currentPageTotalRecords) != '') {
            $myNum = ($totalRecords > $currentPageTotalRecords + $offset ? $currentPageTotalRecords + $offset : $totalRecords);
        }

        if ($displayNote == "Y") { 
            $msg = '<div class="dataTables_info" id="example5_info" role="status" aria-live="polite">Showing ' . $this->convertNumeralsToArabic($offset + 1) . "-" . $this->convertNumeralsToArabic($myNum) . '</div>';
        } elseif ($displayNote == "All") {
            $msg = '<div class="dataTables_info" id="example5_info" role="status" aria-live="polite">Showing ' . $this->convertNumeralsToArabic($offset + 1) . "-" . $this->convertNumeralsToArabic($myNum) . " " . $of . " " . $this->convertNumeralsToArabic($totalRecords) .  '</div>';
        } elseif ($displayNote == "PageCountOnly") {
            $msg = '<div class="dataTables_info" id="example5_info" role="status" aria-live="polite">Showing ' . $this->convertNumeralsToArabic($offset + 1) . "-" . $this->convertNumeralsToArabic($myNum) .  '</div>';
        }

        if ($offset > 0) {
            $next = $offset - $perPage;
            if ($isReWrite == 'N') {
                $str .= "<a href='" . $this->fpn($next) . "' rel='previous' title='" . $previous . "' class='paginate_button previous' data-dt-idx='0' tabindex='0'> " . $previous . "</a>";
            } else {
                 $str .= "<a href='" . $this->url.'/'.$next . "' rel='previous' title='" . $previous . "' class='paginate_button previous' data-dt-idx='0' tabindex='0'> " . $previous . "</a>";
            }
        }

        if (($offset % (10 * $perPage) ) == 0 && $offset != 0) {
            $ii = ($offset / $perPage) + 1;
        } elseif ($offset > (10 * $perPage) and ( $offset % (10 * $perPage)) != 0) {
            $ii = (intval($offset / ($perPage * 10)) * 10) + 1;
        } else {
            $ii = 1;
        }

        $totalPages = ceil($totalRecords / $perPage);
        for ($i = 1; $i <= 10; $i++) {
            $nextOffset = (($ii + ($i - 1)) * $perPage) - $perPage;

            $number = ($ii + ($i - 1));
            if ($i == 1) {
                $firstOffset = $nextOffset;
                $firstNumber = $number;
            }

            if ($number <= $totalPages) {
                if (($offset + $num) / $perPage == $number || ($offset == 0 && $i == 1)) {
                    $str .= '<span><a href="javascript:void(0)" class="paginate_button current" aria-controls="example5" data-dt-idx="1" tabindex="0">' . $this->convertNumeralsToArabic($number) . '</a></span>';
                } else {
                    if ($isReWrite == 'N') {
                        $str .= '<span><a href="' . $this->fpn($nextOffset) . '" class="paginate_button" aria-controls="example5" data-dt-idx="1" tabindex="0">' . $this->convertNumeralsToArabic($number) . '</a></span>';
                    } else {
                        $str .= '<span><a href="'.$this->url.'/'.$nextOffset.'" class="paginate_button" aria-controls="example5" data-dt-idx="1" tabindex="0">' . $this->convertNumeralsToArabic($number) . '</a></span>';
                    }
                }
                $lastNumber = $number;
                $lastOffset = $nextOffset;
            }
        }

        if ($offset <= $totalRecords - ($perPage + 1)) {
            $next = ($offset + $perPage);
            if ($isReWrite == 'N') {
                $pfn_val = (isset($url_params['fpn'])) ? $url_params['fpn'] : 0;
                $nextOffset = $pfn_val + $perPage;
                $str .= '<a href="' . $this->url.'/'.$this->fpn($nextOffset) . '" class="paginate_button" aria-controls="example5" data-dt-idx="1" tabindex="0">Next</a>';
            } else {
                $str .= '<a href="'.$this->url.'/'.$next.'" class="paginate_button" aria-controls="example5" data-dt-idx="1" tabindex="0">Next</a>';
            }
        }

        $str .= '</div>';
        if ($messageOnly == "Y") {
            return $msg;
        }

        if ($totalRecords > $perPage) {
            return $msg . $str;
        } else {
            return $msg;
        }
    }

    public function convertNumeralsToArabic($str, $lang = 'en') {
        $site_language = $this->defaultLanguage;
        if ($site_language == 'ar' || $lang == 'ar') {
            $en = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
            $ar = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
            $newStr = str_replace($en, $ar, $str);
        } else {
            $newStr = $str;
        }
        return $newStr;
    }

    private function fpn($pageNumber) {
        $url = $this->url;
        $baseUrl = $this->baseUrl;

        $fpnPos = strpos($url, '?fpn=');
        if ($fpnPos === false) {
            $strPos = strpos($url, '?');
            $delim = ($strPos === false) ? '/?fpn=' : '&fpn=';
        } else {
            $delim = '/?fpn=';
        }
        $urlParams = explode($baseUrl, $url);
        $cleanUrl = rtrim($urlParams[1], "/");
        $newUrl = $baseUrl . $cleanUrl;
        $pageFpn_arr = explode($delim, $newUrl);
        $main_url = '';
        if (count($pageFpn_arr) > 0) {
            //removing fpn
            foreach ($pageFpn_arr as $key => $value) {
                if (!is_numeric($value)) {
                    $main_url .= $value;
                }
            }
            $main_url .= $delim . $pageNumber;
        } else {
            $main_url = $url . $delim . $pageNumber;
        } 
        return $main_url;
    }

}
