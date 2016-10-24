<?php
class login_model extends CI_Model {
		/* Created by Tejpal*/
        public $title;
        public $content;
        public $date;

        public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
				$this->load->helper('cookie');				
        }
        public function auth()
        { 
			  $this->load->model('admin_model', 'admin', TRUE);
			  $user = wp_authenticate($this->input->post('username'), $this->input->post('password'));
			  if ( ! is_wp_error($user) ){					
					if(in_array("administrator",$user->roles)){
						$newdata = array(
								'id'  => $user->ID,
								'username'  => $user->user_login,
								'display_name'  => $user->display_name,
								'email'     => $user->user_email,
								'user_status'     => $user->user_status,
								'role'     => 'administrator',
								'all_caps'     => $this->admin->filter_caps(array_keys($user->allcaps)),
								'logged_in' => TRUE
						);											
						$this->session->set_userdata($newdata);	
						//print_r($this->session);die;	
						if(isset($_POST['remember'])){
							  $cookie= array(
								  'name'   => 'username',
								  'value'  => $this->input->post('username'),								  
								  'domain' => base_url('login'),
								  'expire' => '86500',
								  'prefix' => 'inspyle_'
							  );
							  setcookie('inspyle_username', $this->input->post('username'), time() + (86400 * 30), base_url('login')); 
							  setcookie('inspyle_password', $this->input->post('password'), time() + (86400 * 30), base_url('login')); 
						}else{							
							  setcookie('inspyle_username', '', time() - (86400 * 30), base_url('login')); 
							  setcookie('inspyle_password', '', time() - (86400 * 30), base_url('login')); 
						}
						return true;
					}
			  }else{
					$this->session->sess_destroy();	
			  }			 
			  return false;			
        }
}
?>