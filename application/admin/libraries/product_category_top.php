<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
  
class product_category_top {
 
    function __construct(){
       
    	$this->CI =& get_instance();
    	
    }
    
    function install($action){
    	
    	$modification=array();
    	
    	 
    	return $this->CI->modify_file($modification,$action);
    }
    
  
  
    
}

?>