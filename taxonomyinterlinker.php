<?php
/*
Plugin Name: Taxonomy Interlinker
Plugin URI: http://www.naden.de/blog/taxonomy-interlinker
Description: This plugin places internal links in the tag descriptions to other tag pages. Your theme have to use <code>tag_description();</code> on the term pages. Read more about modifying your theme on <a href="http://www.naden.de/blog/taxonomy-interlinker">www.naden.de</a>.  
Author: Naden Badalgogtapeh
Version: 0.1
Author URI: http://www.naden.de
*/

class TaxonomyInterlinker {

  var $id = 'taxonomyinterlinker';
  var $name = 'Taxonomy Interlinker';
  var $version = '0.1';
  var $options;
  
  function __construct() {
    load_plugin_textdomain($this->id, false, dirname(plugin_basename(__FILE__)) . '/translations');
    /*
    $locale = get_locale();

	  if(empty($locale)) {
		  $locale = 'en_US';
    }

    load_textdomain($this->id, dirname(__FILE__). '/locale/'. $locale. '.mo');
    */
    
    $this->options = get_option($this->id);
    
    if(!$this->options) {

      $this->options = array(
        'max_links' => 5
      );

      add_option($this->id, $this->options);
    }
    
    if(is_admin()) {
    	add_action('admin_menu', array(&$this, 'adminMenu'));
      wp_enqueue_style($this->id. '-admin-css', plugins_url('admin/admin.css', __FILE__), $this->version); 
    }
    else {
      add_action('term_description', array(&$this, 'termDescriptionFilter'));
    }
  }
  /**
   * get a link tag for the requested tag according to the permalink settings
   */     
  function getLinkTag($slug, $title) {
    static $url, $tag;
    
    if(!$url) {
      $url = get_bloginfo('wpurl');
    }
    
    if(!$tag) {
      $tag = get_option('tag_base');
      
      if(empty($tag)) {
        $tag = '/tag/';
      }
    }

    return sprintf('<a href="%s%s%s/">%s</a>', $url, $tag, $slug, $title);
  }  
  /**
   * parse internal link to other term pages into the term description
   */
  function termDescriptionFilter($description) {
    
  	if(!empty($description)) {
    
      $replaced = array();  
  		
  		foreach($this->getTerms() as $slug => $title) {
  		  preg_match_all('/(?<!\S)' . $title . '(?![-\w])/i', $description, $matches);
  
  		  if(count($matches[0]) > 0) {		  

          if(count($replaced) == intval($this->options['max_links'])) {
    		    break;
          }
  
          if(in_array($title, $replaced)) {
            continue;
          }
          
          $replaced[] = $title;
  
          foreach(array_unique($matches[0]) as $match) {            
    		    $description = preg_replace("/(?<!\S)$match(?!-\w)/", $this->getLinkTag($slug, $title), $description, 1);
    		  }
    		}
  		}
  	}
  
    return $description;
  }  
  /**
   * get all terms associated with the taxonomy post_"tag"
   */ 
  function getTerms() {
  	static $terms;
  
  	if(!$terms) {
  		global $wpdb;
  
  		$terms = array();
  
  		foreach(get_terms(array('post_tag')) as $term) {
  			$terms[$term->slug] = $term->name;
  		}
  	}
  
  	return $terms;
  } 
    
  function adminMenu() {
    add_menu_page(__('Taxonomy Interlinker'), __('Taxonomy Interlinker'), 0, __FILE__, array(&$this, 'adminMenuPage'));
  }
  
  function adminMenuPage() {
    if(!current_user_can('manage_options')) {
      die('<h2>'. __('You are not allowed to access the admin area.'). '</h2>');
    }
    
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
      if(!wp_verify_nonce($_POST[$this->id. '-nonce'], $this->id. '-nonce')) {
        die('<h2>'. __('Failed to verify your request.'). '</h2>');  
      }
         
      if(array_key_exists('max_links', $_POST)) {
      
        $this->options = array(
          'max_links' => $_POST['max_links']
        );
      
        update_option($this->id, $this->options);
      }
    }
       
    include_once dirname(__FILE__). '/admin/admin.php';
  }
  
  function deactivate() {
    global $wpdb;
    delete_option($this->id);
  } 
}
 
add_action('init', create_function('$TaxonomyInterlinker', 'global $TaxonomyInterlinker; $TaxonomyInterlinker = new TaxonomyInterlinker();'));
register_deactivation_hook(__FILE__, array(&$TaxonomyInterlinker, 'deactivate'));
 
?>