<?php


namespace controllers\admin;

class AddUserController extends StateController {

    

    public function addNewAction() {
        $data = array();
        $get = $this->obtainGet();
        $oCategoriesModel = new \models\CategoriesModel();

        $data = array();
        if ($this->isPOST()) {
           

            $post = $data['posted'] = $this->obtainPost();
           
            $oUsersModel = new \models\UsersModel();
            $user_registered = $oUsersModel->register_user($post);

         
            // error_log($user_registered);

            if ($user_registered['status'] == 'success') {
              
                $user_info = $user_registered['data'];
                $this->setGuest(false);
                $session_id = md5($user_info["user_id"].\helpers\Common::genRandomId());
                $user_info['token'] = $session_id;

              
                $oStoresModel = new \models\StoresModel();
                $storeInfo = $oStoresModel->findByPK($user_info['store_id'], "store_name");

              

                $user_info["store_name"] = $storeInfo['store_name'];
                

               

                $oSessionsModel = new \models\SessionsModel();
                $oSessionsModel->insert([
                    "user_id" => $user_info['user_id'],
                    "session_id" => $session_id,
                    "session_time" => date("Y-m-d H:i:s"),
                    "user_type" => "web",
                    "ip_address" => \helpers\Common::getIP(),
                ]);

               
                $data['success'] = "Your account has been created.";
                $this->redirect(['url' => ADMIN_URL . "stores", 'data' => $data]);
            } else {
               
                if($user_registered['message']) {
                    $data['error'] = $user_registered['message'];
                } else {
                    $data['error'] = $user_registered['error'];
                }
                
              
            }
        }
       

        if (!empty($get['id'])) {
            $data['caption'] = $oCategoriesModel->findByPK($get['id']);
        }
        $data['caption']['type'] = !empty($data['caption']['type']) ? $data['caption']['type'] : "messages";
        $metaData['title'] = 'Add / Update Category -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('add', $data);
    }

   

}
