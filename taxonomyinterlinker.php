<?php
/*
Plugin Name: Taxonomy Interlinker
Plugin URI: http://www.naden.de/blog/taxonomy-interlinker
Description: This plugin places internal links in the taxonomy term descriptions to other term pages. Your theme have to use <code>tag_description();</code> on the term pages. Read more about modifying your theme on <a href="http://www.naden.de/blog/taxonomy-interlinker">www.naden.de</a>.
Author: Naden Badalgogtapeh
Version: 0.2
Author URI: http://www.naden.de
*/

class TaxonomyInterlinker {

  var $id = 'taxonomyinterlinker';
  var $name = 'Taxonomy Interlinker';
  var $version = '0.2';
  var $options;
  
  function __construct() {

    load_plugin_textdomain($this->id, false, dirname(plugin_basename(__FILE__)) . '/translations');

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
    return sprintf('<a href="%s">%s</a>', get_term_link($slug, 'post_tag'), $title);
  }
  /**
   * parse internal link to other term pages into the term description
   */
  function termDescriptionFilter($description) {
    
  	if(!empty($description)) {
    
      $replaced = array();

			$current = single_tag_title('', false);
  		
  		foreach($this->getTerms() as $slug => $title) {
				if($current == $title) {
					continue;
				}

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

      if(!wp_verify_nonce($_POST[$this->id. '-nonce'], 'update_options')) {
        die('<h2>'. __('Failed to verify your request.'). '</h2>');  
      }
         
      if(array_key_exists('max_links', $_POST)) {
      
        $this->options = array(
          'max_links' => $_POST['max_links']
        );
      
        update_option($this->id, $this->options);

				printf('<div id="message" class="updated"><p><strong>%s</strong></p></div>', __('Settings saved!'));
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