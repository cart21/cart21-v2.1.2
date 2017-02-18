<?php
class admin_group extends CI_Controller {
 
    function __construct() {
    
        parent::__construct();
        
        $this->load->model('quick_model');
        $this->load->model('adminuser_model');

        
    }
 
    function get_shape(){
   
    return array(
    			
    			"admin_group_id" => $this->data["L"]["id"],
    			"title" => $this->data["L"]["title"],
				"gl_id" => $this->data["L"]["id"],
				"language_id" => $this->data["L"]["language"]
				
			);
    }
    
    function where_work(){
    
    	if(isset($this->form_post_admin_group["language_id"])){
    		$this->db->where("language_id",$this->form_post_admin_group["language_id"]);
    	}else{
    		 
    		$this->db->where("language_id",language_id());
    	}
		
		if(is_array($this->form_post_admin_group)){
		
			$this->form_post_admin_group_where=array_diff_key($this->form_post_admin_group,$pattern=array("title"=>"1"));
			$this->db->where($this->form_post_admin_group_where);
			
			
			if(isset($this->form_post_admin_group["title"])){
				$this->db->like("lower(title)",strtolower($this->form_post_admin_group["title"]));
			}
			
		}else{
		$this->form_post_admin_group=array();
		}	
		/// post1 *///	
    
    
    }
    
	function index($action=""){
	
		$this->permission->check_permission("view");
		
		$this->quick->Header("");
	
	 	$this->shape=$this->get_shape();
	
		$this->load->model('user');
		 
		/////////////  pagination  /////////////
		$this->load->library('pagination');
		

		///post1 ///
		if( $this->input->post()){
		
			$this->form_post_admin_group=array_filter($this->input->post());
			$this->sessiondd->set_userdata('form_post_admin_group',$this->form_post_admin_group);
		}else{
		
			$this->form_post_admin_group=$this->sessiondd->userdata('form_post_admin_group') ;
		}
		
		$this->where_work();
		
		$this->data["POST"]= $this->form_post_admin_group;

		
		//// filter  ////
		
		/////////////  pagination  /////////////
		$this->data['Total']=$this->db->get('admin_group')->num_rows();//$this->user->getTotalUsers(); 
		$per_page=20;
		$choice = $this->data['Total']/ $per_page;
		
		
		$config['base_url'] = base_url().'/admin/admin_group/index';
		$config['total_rows'] = $this->data['Total'];
		$config['per_page'] = $per_page; 
		$config['use_page_numbers'] = TRUE;
		$config["uri_segment"] = 4;
		$config["num_links"] =6;// round($choice);
		//$config['cur_tag_open'] = '<b>';
		$config['last_link'] = 'Last';
		$config['first_link'] = "First";
		$config['prev_link'] = ' previous ';
		$config['next_link'] = ' next ';
		
		
		$config['full_tag_open'] = ' <div class="pagination pagination-small" style="text-align:left;"> <ul>';
		$config['full_tag_close'] = ' </ul></div>';
		
		$config['cur_tag_open'] = ' <li><a style="color:grey;font-weight: 600;">';
		$config['cur_tag_close'] = '</a></li>';
		
		$config['num_tag_open'] = ' <li>';
		$config['num_tag_close'] = '</li>';
		
		$config['first_tag_open'] = ' <li>';
		$config['first_tag_close'] = '</li>';
		
		$config['last_tag_open'] = ' <li>';
		$config['last_tag_close'] = '</li>';
		
		
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		
		
		$this->pagination->initialize($config); 
		
		$this->data["pagelink"]=$this->pagination->create_links();
		/////////////  pagination  /////////////
		
		
		
		$this->where_work();
		
		///order by///
		
		$admin_group_session=$this->sessiondd->userdata('admin_group');
		
		if (	isset($admin_group_session["orderby"])		){
		
		$this->db->order_by($admin_group_session["orderby"], $admin_group_session["orderby_order"]);
		}else{
		$this->db->order_by("admin_group_id", "asc");
		}	
		///order by///
			
			
			
		 
		//// filter*  ////
		
		$page=$this->uri->segment(4);
		
		if(!empty($page) and $page!="list" ){
 		
 		$per_page_start=$per_page*($page-1);
 		}else{
 		$page=0;
 		$per_page_start=$per_page*$page;
 		}
 		
 		
 		$admin_groups=$this->db->limit($per_page, $per_page_start)->select(implode(",",array_keys($this->shape)))->get("admin_group");   ///segment 4 page

		$this->data["admin_groups"]=$this->modules->admin_group_list($admin_groups);
		
		if($action=="list"){
		echo $this->data["admin_groups"];
		exit;
		}
		
		$this->quick->Top_menu("");
		$this->quick->Footer("");
			
	$this->smarty->view("users/admin_group",$this->data);
	 
	}

	function add(){
	
	$this->permission->check_permission("view");
	$this->permission->check_permission("add");
	$this->data["page"]="add";
	
	
	$this->data["POST"]=$this->input->post();
	
	if( $this->input->post() and $this->permission->check_permission("add") ){
	
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
	    
		$this->form_validation->set_rules('title', 'title', 'trim|required|xss_clean');	
		
	 	
	 	if ($this->form_validation->run() 	) { 
	 		
	 	
	 		$data_admin_group=$this->input->post();
	 		
	 		$data_admin_group["language_id"]=language_id();
	 		
	 		$this->db->insert("admin_group",$data_admin_group);
	 		
	 		$ids=$this->db->insert_id() ;
	 		$this->quick_model->logs($ids." idli admin_group added !");
	 	
	 		
	 		$this->db->where("admin_group_id",$ids)->update("admin_group",array("gl_id"=>$ids));
	 			
	 	redirect("admin/admin_group");
	 	}else {
			$verrors=array_filter(explode('.',validation_errors()));
			foreach($verrors as $verror){
				$this->quick->errors[] = strip_tags($verror).".";
			}
		}
	}
	
		$this->quick->Header("");
		$this->quick->Top_menu("");
		$this->quick->Footer("");
	$this->smarty->view('users/admin_group_form',$this->data);
	
    }

    function change_status(){
    
    	$this->permission->check_permission("view");
    	$this->permission->check_permission("edit");
    	 
    	//dbg($this->input->post());
    	 
    	if($this->input->post("status")=="true"){
    
    		$status=1;
    	}else {
    
    		$status=0;
    	}
    	 
    	if($this->input->post("admin_group_id")>0){
    
    		$this->db->where("admin_group_id",$this->input->post("admin_group_id"))->update("admin_group",array("status"=>$status ));
    
    	}
    	 
    	echo 1; exit;
    	 
    }    
    
    function edit($ids,$gl_id){
    
	    $this->permission->check_permission("view");
	    $this->permission->check_permission("edit");
	    $this->data["page"]="edit";
   		$this->ids=$ids;
   	
		$this->data["POST"]=$this->input->post();
		
	if( $this->input->post() and $this->permission->check_permission("add") ){
		
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
	    
		$this->form_validation->set_rules('title', $this->language_model->language_c_key("title"), 'trim|required|xss_clean');	
		
	 	if ($this->form_validation->run() 	) { 
	 		
	 		
	 		$data_admin_group=$this->data["POST"];
	 		
	 		$this->db->where("admin_group_id",$ids)->update("admin_group",$data_admin_group);
	 		
	 		/*
	 		$intersect_key=array(
					"sort_order"=>1
			);
	 		
	 		$cl_new_data=array_intersect_key($data_admin_group,$intersect_key);
	 		
	 		foreach($this->language_model->languages()->result_array() as $l){
	 				
	 			$this->db->where(array("gl_id"=>$gl_id,"language_id"=>$l["language_id"]))->update("admin_group",$cl_new_data);
	 		}
	 		*/
	 		
	 		$this->quick->success[]=$this->language_model->language_c_key("successfuledit");
	 		
	 	}else {
			$verrors=array_filter(explode('.',validation_errors()));
			foreach($verrors as $verror){
				$this->quick->errors[] = strip_tags($verror).".";
			}
		}
	}
	
		$this->data["POST"]=$this->db->where("admin_group_id",$ids)->get("admin_group")->row_array();
		$this->admin_group_language_create();
		
		
		$this->data["gl_group"]=$this->adminuser_model->gl_group($this->data["POST"]["gl_id"])->result_array();
		
		$this->quick->Header("");
		$this->quick->Top_menu("");
		$this->quick->Footer("");
	$this->smarty->view('users/admin_group_form',$this->data);
    
	
    }
    
    function delete(){
    
		if($this->permission->check_permission("delete") ){
	
			
			$this->db->where_in("gl_id",$this->input->post("admin_group_id"))->delete("admin_group");
			$this->db->where_in("admin_group_id",$this->input->post("admin_group_id"))->delete("admin_to_group");
			
			$this->quick_model->logs(implode(',',$this->input->post("admin_group_id"))." id admin_group deleted !");
			$result="1";
		}else{
			$result="0";
		}
	   	echo $result;
    	exit;	
    }
    
    function get_filter_message(){
	
	
	$message="";
		foreach($this->data["POST"] as $key =>$value){
		
			if($key=="admin_group_group"){
				$group=$this->db->where_in("admin_group_group_id",$value)->get("admin_group_group")->result_array();
				$message.= "<p>".implode(",",$this->quick->array_column($group,"title") )."</p>";
			}else{
				$message.= "<p>".$this->shape[$key].":".$value."</p>";
			}
		}
		
	return $message;
	}

	function clear_filter(){
	
		$this->sessiondd->set_userdata('form_post_admin_group',"");
	redirect('admin/admin_group');
	}

	function set_orderby(){
	
		if($this->input->get("orderby")){
				
				$admin_group_session=$this->sessiondd->userdata('admin_group') ;
			
				$admin_group_session["orderby"]=$this->input->get("orderby");
				$this->sessiondd->set_userdata('admin_group',$admin_group_session);
			
				
				if(	isset($admin_group_session["orderby_order"]) ){
				
					if($admin_group_session["orderby_order"]=="asc" ){
					
						$admin_group_session["orderby_order"]="desc";
					}else{
					
						$admin_group_session["orderby_order"]="asc";
					}
					
				}else{
				
					$admin_group_session["orderby_order"]="asc";
				}
				
				$this->sessiondd->set_userdata('admin_group',$admin_group_session);
			}
	
	redirect('admin/admin_group');
	}
	

	
	function admin_group_language_create(){
	
		$sql="SELECT l.language_id FROM `language` as l
	
			left join admin_group as pc
			on pc.language_id=l.language_id and pc.gl_id=".$this->data["POST"]["gl_id"]."
	
			where l.status=1 and ( pc.admin_group_id is null)";
	
		$result=$this->db->query($sql);
	
		if($result->num_rows){
	
			$intersect_key=array(
					"gl_id"=>1,
					"title"=>1
			);
			$new_data=array_intersect_key($this->data["POST"],$intersect_key);
	
			foreach($result->result_array() as $l){
	
				$new_data["language_id"]=$l["language_id"];
				$this->db->insert("admin_group",$new_data);
			}
	
		}
	}
	
	
	
	function permission($group_id) {
	
		$this->permission->check_permission("view");
		 
		///// post
		if($this->input->post() and $this->permission->check_permission("edit") ){
	
			$this->db->where(array("admin_group_id"=>$group_id))->delete("permission_admin_section");
			 
			foreach( $this->input->post("pas") as $key=>$value ){
	
				foreach($value as $permission){
					 
					$a_result["admin_group_id"]=$this->input->post("group_id");
					$a_result["permission"]=$permission;
					$a_result["section_id"]=$key;
	
					$result[]=$a_result;
				}
	
			}
			$this->db->insert_batch("permission_admin_section",$result);
			 
		}
		 
		 
		 
		///////////////////////////////////////////////////////////
		$permissions=  $this->db->get("permission");
		$this->data["permissions"]=$permissions->result_array();
		 
		$this->data["group_id"]=$group_id;
		$this->data["group_info"]=$this->db->where(array("admin_group_id"=>$group_id))->get("admin_group")->row_array();
	
		$sections=  $this->db->get("permission_sections");
	
		foreach($sections->result_array() as $key=>$section ){
	
			$rsection[$key]=$section;
			 
			$this->db->where(array("admin_group_id"=>$group_id,"section_id"=>$section["section_id"]));
			 
			$pas=$this->db->get('permission_admin_section')->result_array();
			 
			$rsection[$key]["group_permission"]=$this->quick->array_column( $pas,"permission" );
			 
		}
	
		$this->data["sections"]=$rsection;
	
		$this->quick->Header("");
		$this->quick->Top_menu("");
		$this->quick->Footer("");
		 
		$this->smarty->view('admiuserpermission',$this->data);
	
	}
	
	
		
}