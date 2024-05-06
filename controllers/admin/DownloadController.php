<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class DownloadController extends StateController {

    public function usersAction() {
        $data = array();
        $data['userState'] = $this->getState('adminUser');
        $data['paidOrders'] = $data['pendingOrders'] = $data['totProducts'] = $data['totFaqs'] = 0;
        $oCustomersModel = new \models\CustomersModel();
        $customers = $oCustomersModel->getUsersList(["offset" => 0, "perpage" => 500000000])['users'];
        $fileStr = "Sr #\tName\tEmail\t\n";
        for($c = 0; $c < COUNT($customers); $c++) {
            $fileStr .= ($c+1)."\t".$customers[$c]['first_name']." ".$customers[$c]['last_name']."\t".$customers[$c]['email']."\t\n";
        }
        //print_r($fileStr); exit;
        $filePath = "\uploads\user-downloads.xls";
        $openHandler = fopen($filePath, "w");
        $written = fwrite($openHandler, $fileStr);
        //var_dump($written);
        fclose($openHandler);
        
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: ');
        header('Content-Type: ');
        header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
        header('Content-Length: '.filesize($filePath));
        readfile($filePath);
    }
}
