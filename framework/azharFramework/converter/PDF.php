<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\converter;

class PDF{

    protected $header = 'Page {PAGENO} / {nbpg}';
    protected $footer;
    protected $html;
    protected $css = array();
    protected $oPDF;
    protected $leftMargin = 10;
    protected $rightMargin = 10;
    protected $topMargin = 15;
    protected $bottomMargin = 20;
    protected $marginHeader = 6;
    protected $marginFooter = 3;

    public function __construct($params = array()) {
        (isset($params['header']) ? $this->header = $params['header'] : $this->header = '');
        (isset($params['footer']) ? $this->footer = $params['footer'] : $this->footer = '');
    }

    public function setHeader($contents) {
        $this->header = $contents;
        return $this;
    }

    public function getHeader() {
        return $this->header;
    }

    public function setFooter($contents) {
        $this->footer = $contents;
    }

    public function getFooter() {
        $this->footer;
    }

    /* It will receive prepared html, do not send html url.
      One can use renderPartial to prepare html */

    public function setHTML($html) {
        $this->html = $html;
        return $this;
    }

    public function getHTML() {
        return $this->html;
    }

    public function setMargins($left = 10, $right = 10, $top = 15, $bottom = 20) {
        $this->setLeftMargin($left);
        $this->setRightMargin($right);
        $this->setTopMargin($top);
        $this->setBottomMargin($bottom);
        return $this;
    }

    public function setLeftMargin($left) {
        $this->leftMargin = $left;
        return $this;
    }

    public function setRightMargin($right) {
        $this->rightMargin = $right;
        return $this;
    }

    public function setTopMargin($top) {
        $this->topMargin = $top;
        return $this;
    }

    public function setBottomMargin($bottom) {
        $this->bottomMargin = $bottom;
        return $this;
    }

    private function getCSS() {
        if (!empty($this->css)) {
            $finalCss = '';
            foreach ($this->css as $css) {
                $finalCss .= $css;
            }
            return $finalCss;
        }
    }

    /* tries to load the specified css file  but do not mention .css 
     * extension. This method will forcefully added .css extension */

    public function loadCSS($cssFile) {
        $this->css[] = file_get_contents($cssFile . ".css");
        return $this;
    }

    /* Build and return mPDF Object */

    protected function buildPDF($js = '') {
        if ($this->oPDF === null) {
            require_once dirname(dirname(__DIR__)) . "/vendor/mpdf57/mpdf.php";
            //$this->oPDF = new \mPDF('utf-8', 'A4', '', '', 10, 10, 15, 20, 6, 3);
            $this->oPDF = new \mPDF('utf-8', 'A4', '', '', $this->leftMargin, $this->rightMargin, $this->topMargin, $this->bottomMargin, $this->marginHeader, $this->marginFooter);
        }
        $this->oPDF->use_kwt = true;
        $this->oPDF->SetHeader($this->header);
        $this->oPDF->SetHTMLFooter($this->footer);
        $this->oPDF->WriteHTML($this->getCSS(), 1);
        $this->oPDF->WriteHTML($this->html, 2);
        if ($js !== '') {
            $this->oPDF->SetJS($js);
        }
        return $this->oPDF;
    }

    public function browserStream($js = '') {
        $oPDF = $this->buildPDF($js);
        $oPDF->Output();
        exit;
    }

    public function saveToFile($pdfFileName = null) {
        $oPDF = $this->buildPDF();
        $oPDF->Output($pdfFileName, 'F');
        if (is_file($pdfFileName)) {
            return true;
        } else {
            return false;
        }
    }

    public function download($pdfFileName) {
        $oPDF = $this->buildPDF();
        $oPDF->Output($pdfFileName . '.pdf', 'D');
    }

    public function downloader($pdfFileName, $propsedFileName) {
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers
        header("Content-Description: File Transfer");
        header("Content-Transfer-Encoding: binary");
        header("Content-Type: application/pdf");
        header('Content-Disposition: attachment; filename="' . $propsedFileName . '"');
        header("Content-Length: " . filesize($pdfFileName));
        readfile("$pdfFileName");
    }

}
