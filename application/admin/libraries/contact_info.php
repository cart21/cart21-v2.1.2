<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
  
class contact_info {
 
    function __construct(){
       
    	$this->CI =& get_instance();
    	
    }
    
    function install($action){
    	
    	$modification=array();
    	
    	 
    	return $this->CI->modify_file($modification,$action);
    }
    
  
  
    
}

?>