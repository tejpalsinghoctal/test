<?php
class admin_model extends CI_Model {

        public $title;
        public $content;
        public $date;

        public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();									
        }
		public function auth_caps($caps){
			$session=$this->session->userdata();
			$all_caps=$session['all_caps'];
			//print_r($all_caps);die;
			if(is_array($caps)){
				$success=0;
				foreach($caps as $cap){
					if(in_array($caps,$all_caps)){						
						$success++;
					}
				}
				if($sucess=='0'){
					redirect(base_url('admin'));
				}
			}else{
				if(!in_array($caps,$all_caps)){
					redirect(base_url('admin'));
				}
			}
		}
		public function get_categories($taxonomy = 'categories'){			
			$args=array(
			  'hide_empty' => false,
			  'orderby' => 'name',
			  'order' => 'ASC',
			  'parent' => '0'
			);
			return $categories = get_terms($taxonomy,$args);
		}
		public function filter_caps($all_caps){			
			$caps=$this->get_caps();
			$caps_key=array_keys($caps);
			$response=array();
			foreach($caps_key as $key){
				if(in_array($key,$all_caps))
				$response[]=$key;
			}
			return $response;			
		}
		public function get_post_categories($id=null){
			$args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all');
			return $terms = wp_get_post_terms( $id, 'categories', $args );		
		}
		public function get_caps(){
			return array(										
										"admin_manage_admin_users"=>"Manage Admin Users",
										"admin_manage_users"=>"Manage Users",
										"admin_manage_categories"=>"Manage Categories",
										"admin_manage_posts"=>"Manage Posts",
										"admin_manage_comments"=>"Manage Comments",
										"admin_manage_violation_reports"=>"Manage Violation Reports",
										"admin_manage_static_contents"=>"Manage Static Reports",
									);
		}
		public function total_pages(){
			$args=array(
				'post_type'=>'app_page',
				'post_per_page'=>-1,
				'post_status'=>array('publish','private')
			);
			return count(get_posts($args));		
		}
		public function total_categories(){			
			return count($this->get_categories());		
		}
		public function total_violation_reports(){
			global $wpdb;
			$violations=$wpdb->get_results("SELECT count(*) as count from ".$wpdb->prefix."violation_reports where status='1'");			
			return $violations[0]->count;
		}
		public function total_users($role='subscriber')
        {
			$args = ($role!='')?array('role'=> $role,'orderby' => 'login','order'=> 'ASC','fields'=> 'all'):array('role__in'=> array('subscriber','administrator'),'orderby' => 'login','order'=> 'ASC','fields'=> 'all'); 
			$users = get_users($args);
			return count($users);
		}
		public function total_posts($post_type='post',$post_status='publish')
        {
			return $result = wp_count_posts($post_type)->publish;					
		}
		public function total_comments($comment_status='all')
        {
			 $args = array('post_type' => 'creation','status'=> $comment_status,'orderby' => '','order'=> 'DESC');
			 $comments = get_comments($args);	
			 return count($comments);			
		}
		public function get_post_meta($post_id=null){
			$post_meta=array_map( function( $a ){ return $a[0]; }, get_post_meta( $post_id ) );	
			return  $post_meta;	
		}
		public function get_total_favourites($user_id){
			global $wpdb;
			$result=$wpdb->get_results("SELECT count(*) as count from ".$wpdb->prefix."favourites WHERE user_id=".$user_id);
			return $result[0]->count;
		}
		public function get_total_savesOf($post_id){
			global $wpdb;
			$result=$wpdb->get_results("SELECT count(*) as count from ".$wpdb->prefix."favourites WHERE creation_id=".$post_id);
			return $result[0]->count;
		}
		public function get_favourites_by($post_id){
			global $wpdb;
			$results=$wpdb->get_results("SELECT user_id from ".$wpdb->prefix."favourites WHERE creation_id=".$post_id);
			$response=array("0");
			foreach($results as $row){
				$response[]=$row->user_id;
			}
			return $response;
		}
		public function get_favourites($user_id){
			global $wpdb;
			$results=$wpdb->get_results("SELECT creation_id as post_id from ".$wpdb->prefix."favourites WHERE user_id=".$user_id);
			$response=array("0");
			foreach($results as $row){
				$response[]=$row->post_id;
			}
			return $response;
		}
		public function get_total_my_followers($user_id){
			global $wpdb;
			$result=$wpdb->get_results("SELECT count(*) as count from ".$wpdb->prefix."trackuser WHERE author_id=".$user_id);
			return $result[0]->count;
		}
		public function get_followers($user_id){
			global $wpdb;
			$results=$wpdb->get_results("SELECT currentuser_id as user_id from ".$wpdb->prefix."trackuser WHERE author_id=".$user_id);
			$response=array("0");
			foreach($results as $row){
				$response[]=$row->user_id;
			}
			return $response;
		}
		public function get_total_following_me($user_id){
			global $wpdb;
			$result=$wpdb->get_results("SELECT count(*) as count from ".$wpdb->prefix."trackuser WHERE currentuser_id=".$user_id);
			return $result[0]->count;
		}
		public function get_following($user_id){
			global $wpdb;
			$results=$wpdb->get_results("SELECT author_id as user_id from ".$wpdb->prefix."trackuser WHERE currentuser_id=".$user_id);
			$response=array("0");
			foreach($results as $row){
				$response[]=$row->user_id;
			}
			return $response;
		}
		public function get_users($role='',$user_status='')
        {			
			 $args = array(							
							'orderby' => 'login',
							'order'=> 'ASC',
							'fields'=> 'all'
			 );
			 if($role!=''){
				$args['role']=$role;
			 }else{
				$args['role__in']=array('subscriber','administrator');
			 }
			 $followers_of=(isset($_GET['my_followers']))?$_GET['my_followers']:'';			 
			 if($followers_of!=''){
				 $followers=$this->get_followers($followers_of);			
				 $args['include'] = $followers;
			 }
			 $following_by=(isset($_GET['following_me']))?$_GET['following_me']:'';
			 if($following_by!=''){
				 $following=$this->get_following($following_by);			
				 $args['include'] = $following;
			 }
			 $favourites_by=(isset($_GET['favourites_by']))?$_GET['favourites_by']:'';
			 if($favourites_by!=''){
				 $favouritesby=$this->get_favourites_by($favourites_by);			
				 $args['include'] = $favouritesby;
			 }
			 $users = get_users($args);
			 $result=array();
			 foreach($users as $user){
				$user->user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta( $user->ID ) );				
				$user->user_posts = count_user_posts( $user->ID , 'creation' );				
				$user->user_favourites = $this->get_total_favourites($user->ID);				
				$user->my_followers = $this->get_total_my_followers($user->ID);				
				$user->following_me = $this->get_total_following_me($user->ID);				
				if($user_status!=''){
					if($user_status==$user->user_status)
					$result[]=$user;
				}else{
					$result[]=$user;
				}
			}
			return $result;			
		}
		public function get_comments($comment_status='all')
        {			
			 $args = array('post_type' => 'creation','status'=> $comment_status,'orderby' => '','order'=> 'DESC');
			 $comments = get_comments($args);			 
			 return $comments;			
		}
		public function get_posts($cat_id,$post_type='creation')
        {	
			 $author=(isset($_GET['author']))?$_GET['author']:'';
			 $args = array( 
							'posts_per_page'   => -1,
							'offset'           => 0,							
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							//'include'          => '',
							'exclude'          => '',
							'meta_key'         => '',
							'meta_value'       => '',
							'post_type'        => $post_type,
							'post_mime_type'   => '',
							'post_parent'      => '',
							'author'		   => $author,
							'author_name'	   => '',
							//'post_status'      => 'publish',
							'suppress_filters' => true 
			 );
			 $in_favourite_of=(isset($_GET['in_favourite_of']))?$_GET['in_favourite_of']:'';
			 if( $in_favourite_of!=''){
				 $favourites=$this->get_favourites($in_favourite_of);	
				 $args['include'] = $favourites;				 
			 }
			 if($cat_id!=''){
				$args['tax_query']=array(
									array(
										'taxonomy' => 'categories',
										'terms' => $cat_id,
										'include_children' => true // Remove if you need posts from term 7 child terms
									)
								);
			 }
			 $postData = get_posts($args);
			 $start_date = ($_POST['start_date'])?strtotime($this->input->post('start_date')):0;
			 $end_date = ($_POST['end_date'])?strtotime($this->input->post('end_date')):0;
			 $posts=array();
			 if($start_date && $end_date){
				foreach($postData as $post){
					$post_date=strtotime(date('d-m-Y',strtotime($post->post_date)));
					if($start_date<=$post_date && $end_date>=$post_date)
					$posts[]=$post;
				 }
			 }else{
				$posts=$postData;
			 }			 
			 return $posts;			
		}
}
?>