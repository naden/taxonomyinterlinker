<div class="wrap">

  <h2><?php echo $this->name?> <em>v<?php echo $this->version?></em></h2>

  <div id="plugin-content">
    
    
    <form method="post" action="">
      
      <?php wp_nonce_field('update_options', $this->id. '-nonce'); ?>
    
      <table class="form-table">
      <tr valign="top">
        <th scope="row"><label for="max_links"><?php _e('Max number of links to parse into the tag description.', $this->id)?></label></th>
        <td><input name="max_links" type="text" id="max_links" value="<?php echo $this->options['max_links']; ?>" class="regular-text" /></td>
      </tr>
      </table>
    
      <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('save settings', $this->id)?>" /></p>
      
    </form>
    <hr>
    <h3 class="title"><?php _e('Get help and post feedback at the', $this->id)?> <a href="http://www.naden.de/blog/taxonomy-interlinker" target="_blank"><?php _e('Plugin homepage')?></a></h3>
    
  </div>
  
  <?php include_once dirname(__FILE__). '/admin-sidebar.php'; ?>

</div>