<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\services;

class HomeController extends \controllers\AppController {

    public function contactUsAction(){
        $params = [];
    	$params = $this->obtainPost();
        
        $oContactUsModel = new \models\ContactUsModel();
        $result = $oContactUsModel->insertData($params);
        if ($result) {
            $params['type'] = 'contactus';
            $response = \helpers\Common::sendMail($params);
            if ($response) {
                header('Content-Type:application/json');
                echo json_encode($result);
            }
        }
    }

    public function getImagesAction() {
        $params = [];
    	$params = $this->obtainGet();
        $search = str_replace(" ", "%20", $params['term']);
        //$search = $params['term'];
        $page = $params['page'] ?: 1;
        //echo 'https://api.pexels.com/v1/search?query='.$search.'&per_page=10&page='.$page;
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.pexels.com/v1/search?query='.$search.'&per_page=10&page='.$page.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: 563492ad6f917000010000015f6701ee092d4d8f93df7114673be6f0',
            'Cookie: __cf_bm=AG8cPHVqc9usjlsgFWZ8Mkf4WE_Pw.a0qjdd4GRvixA-1665224679-0-AWTovMyxs3n1n2TNAouRDG3yA9/EGjQWHwbDX5O6GPYMQje7xKhHQppm9EE/eU3ZZxkto72IdT5v0Rg5Uev4ggo='
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        $response = (array) json_decode($response);
        $photos = (array) $response['photos'];
        //$photos = [];
        //print_r($photos); exit;
        $result = [];
        if(!empty($photos)) {
            for($p = 0; $p < COUNT($photos); $p++) {
                $photos[$p] = (array) $photos[$p];
                $result[$p]['value'] = $photos[$p]['src']->large;
                //$result[$p]['label'] = "<img src='".$photos[$p]['src']->small."' style='max-width: 100px; min-width: 20px;'><span>" . $photos[$p]['alt']."</span>";
                $result[$p]['label'] = "<img src='".$photos[$p]['src']->small."' style='max-width: 100px; min-width: 20px;'><span>Select Photo</span>";
            }
            $result[$p+1]['value'] = 'load_more';
            $result[$p+1]['label'] = "<img src='".SITE_URL."assets/images/more.png' style='max-width: 100px; min-width: 20px;' onclick=\"this->closest('img')..attr('src','".SITE_URL."assets/images/more.png');setTimeout(this.closest('div').hide(), 3000);\">" . "<u>Load More</u>";
        } else {
            $search = str_replace(" ", "-", $params['term']);
            //echo 'https://api.unsplash.com/search/photos/?client_id=u6GD0XvKYWCrDk3G5OC1lsXW8XBFfUx4Ejf5dZTKb1s&page='.$page.'&per_page=10&query='.$search." == ";
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://api.unsplash.com/search/photos/?client_id=u6GD0XvKYWCrDk3G5OC1lsXW8XBFfUx4Ejf5dZTKb1s&page='.$page.'&per_page=10&query='.$search.'',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array(
              ),
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);
            $response = (array) json_decode($response);
            $photos = (array) $response['results'];
            //print_r($photos);
            if(!empty($photos)) {
                for($p = 0; $p < COUNT($photos); $p++) {
                    $photos[$p] = (array) $photos[$p];
                    $photos[$p]['urls'] = (array) $photos[$p]['urls'];
                    $result[$p]['value'] = $photos[$p]['urls']['full'];
                    //$result[$p]['label'] = "<img src='".$photos[$p]['urls']['full']."' style='max-width: 100px; min-width: 20px;'><span>" . $photos[$p]['description']."</span>";
                    $result[$p]['label'] = "<img src='".$photos[$p]['urls']['full']."' style='max-width: 100px; min-width: 20px;'><span>Select Photo</span>";
                }
                $result[$p+1]['value'] = 'load_more';
                $result[$p+1]['label'] = "<img src='".SITE_URL."assets/images/more.png' style='max-width: 100px; min-width: 20px;' onclick=\"this->closest('img')..attr('src','".SITE_URL."assets/images/more.png');setTimeout(this.closest('div').hide(), 3000);\">" . "<u>Load More</u>";
            } else {
                $search = str_replace(" ", "%20", $params['term']);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                  CURLOPT_URL => 'https://api.pexels.com/v1/search?query='.$search.'&per_page=10&page='.$page.'',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'GET',
                  CURLOPT_HTTPHEADER => array(
                    'Authorization: 563492ad6f917000010000015f6701ee092d4d8f93df7114673be6f0',
                    'Cookie: __cf_bm=AG8cPHVqc9usjlsgFWZ8Mkf4WE_Pw.a0qjdd4GRvixA-1665224679-0-AWTovMyxs3n1n2TNAouRDG3yA9/EGjQWHwbDX5O6GPYMQje7xKhHQppm9EE/eU3ZZxkto72IdT5v0Rg5Uev4ggo='
                  ),
                ));
                
                $response = curl_exec($curl);
                
                curl_close($curl);
                $response = (array) json_decode($response);
                $photos = (array) $response['photos'];
                $photos = [];
                //print_r($photos); exit;
                
                $result = [];
                if(!empty($photos)) {
                    for($p = 0; $p < COUNT($photos); $p++) {
                        $photos[$p] = (array) $photos[$p];
                        $result[$p]['value'] = $photos[$p]['src']->large;
                        //$result[$p]['label'] = "<img src='".$photos[$p]['src']->small."' style='max-width: 100px; min-width: 20px;'><span>" . $photos[$p]['alt']."</span>";
                        $result[$p]['label'] = "<img src='".$photos[$p]['src']->small."' style='max-width: 100px; min-width: 20px;'><span>Select Photo</span>";
                    }
                    $result[$p+1]['value'] = 'load_more';
                    $result[$p+1]['label'] = "<img src='".SITE_URL."assets/images/more.png' style='max-width: 100px; min-width: 20px;' onclick=\"this->closest('img')..attr('src','".SITE_URL."assets/images/more.png');setTimeout(this.closest('div').hide(), 3000);\">" . "<u>Load More</u>";
                } 
            }
        }
        //prnit_r($result); exit;

        header('Content-Type:application/json');
        echo json_encode($result);
    }
}
