<?php
/*
Plugin Name: jQuery Portfolio
Plugin URI: http://www.rivercitygraphix.com
Description: A plugin that allows you to add a customizable jQuery portfolio to your website.
Author: Kevin Olson
Version: 1.1
Author URI: http://www.rivercitygraphix.com
*/
define( 'JP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
require_once( JP_PLUGIN_PATH . '/options.php' );
class Portfolio {
	var $theme_options = array(
			array('type' => 'open'),
			array(
					'id' => 'item_width',
					'default' => '100',
					'label' => 'Item Width',
					'type' => 'text'
			),
			array(
					'id' => 'item_height',
					'default' => '100',
					'label' => 'Item Height',
					'type' => 'text'
			),
			array(
					'id' => 'options_style',
					'default' => 'options',
					'label' => 'Options Style',
					'type' => 'dropdown'
			),
			array('type' => 'close')
	);
	var $options;
	var $add_my_script;
	function __construct(){
		add_action('init', array(&$this, 'init'));
		add_action('after_setup_theme', array(&$this, 'after_setup_theme'));
		add_action('admin_menu', array(&$this, 'theme_options_menu'));
		$this->options = new Portfolio_Options('portfolio_options');
		add_shortcode('portfolio', array(&$this, 'shortcode_portfolio'));
		add_action('init', array(&$this, 'register_my_script'));
		add_action('wp_footer', array(&$this, 'print_my_script'));
	}
	function init(){
		add_image_size('portfolio_item', 180, 153, true);
		add_image_size('portfolio_item_full', 180, 180, true);
		register_post_type('portfolio_item',
				array(
						'capability_type' => 'post',
						'exclude_from_search' => true,
						'hierarchical' => false,
						'labels' => array(
								'name' => __('Portfolio Items'),
								'singular_name' => __('Portfolio Item'),
								'add_new' => __('Add New'),
								'add_new_item' => __('Add New Portfolio Item'),
								'edit' => __('Edit'),
								'edit_item' => __('Edit Portolio Item'),
								'new_item' => __('New Portfolio Item'),
								'view' => __('View'),
								'view_item' => __('View Portfolio Item'),
								'search_items' => __('Search Portfolio Items'),
								'not_found' => __('No portfolio items found.'),
								'not_found_in_trash' => __('No portfolio items found in trash.')
						),
						'public' => true,
						'query_var' => true,
						'rewrite' => array('slug' => 'portfolio', 'with_front' => true),
						'show_ui' => true,
						'supports' => array('title', 'author', 'thumbnail', 'editor')
				));
		register_taxonomy('portfolio_type', array('portfolio_item'), array(
				'hierarchical' => true,
				'rewrite' => array('slug' => 'type'),
				'labels' => array(
						'name' => __( 'Type'),
						'singular_name' => __( 'Type'),
						'search_items' =>  __( 'Search Types' ),
						'all_items' => __( 'All Types' ),
						'parent_item' => __( 'Parent Type' ),
						'parent_item_colon' => __( 'Parent Type:' ),
						'edit_item' => __( 'Edit Type' ),
						'update_item' => __( 'Update Type' ),
						'add_new_item' => __( 'Add New Type' ),
						'new_item_name' => __( 'New Type Name' ),
						'menu_name' => __( 'Types' ),
				)
		));
	}
	function shortcode_portfolio(){
		$this->add_my_script = true;
		?>
		<?php
		if($this->options->options_style != ''){
		    
			$options_style = $this->options->options_style;
		}
		?>
		<div id="<?php echo $options_style; ?>">
		    	<?php $alltypes = get_terms('portfolio_type'); ?>
					<ul id="filters">
					  <li><a href="#" data-filter="*">Show All</a></li>
					  <?php foreach($alltypes as $type) { ?>
					 	<li><a href="#" data-filter=".<?php echo $type->slug; ?>"><?php echo $type->name; ?></a></li>
					 <?php } ?>
					</ul>
				</div>
				<br />
		<div id="portfolio_container">
		<?php
		global $post;
		$old_post = $post;
		if($this->options->item_width != ''){
			$width = $this->options->item_width;
		}
		if($this->options->item_height != ''){
			$height = $this->options->item_height;
		}
		$portfolio_item = get_posts(array('post_type' => 'portfolio_item', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC'));
		foreach($portfolio_item as $post){
			setup_postdata($post);	
			$types = get_the_terms($post->ID,'portfolio_type');
			$theme_options = "style='width:" . $width . "px; height:" . $height . "px; margin-right:" . $margins . "px; margin-bottom:" . $margins . "px;'";
			$classes = "portfolio_item item ";
			foreach($types as $type) {
				$classes .= " " . $type->slug;
			}
			?>
						<div class="<?php echo $classes; ?>" <?php echo $theme_options;?>>
							<div class="item_inner">
								<div class="item_image"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( $portfolio_thumb_size, array('title' => ''.get_the_title().'' )); ?></a></div>
							</div>	
						</div>	
				<?php	
			}
			$post = $old_post;
			?></div><?php
		}
		function register_my_script() {
			wp_register_script('easing','/wp-content/plugins/jquery-portfolio/js/jquery.easing.1.3.min.js',array('jquery') );
			wp_register_script('portfolio','/wp-content/plugins/jquery-portfolio/js/jquery.isotope.js',array('jquery') );
			wp_register_script('main','/wp-content/plugins/jquery-portfolio/js/main.js',array('jquery') );
			wp_register_style('style','/wp-content/plugins/jquery-portfolio/css/style.css');
			wp_register_style('portfoliocss','/wp-content/plugins/jquery-portfolio/css/portfolio.css');
		}
		function print_my_script() {
			if ( ! 	$this->add_my_script )
				return;
			wp_print_scripts('easing');
			wp_print_scripts('portfolio');
			wp_print_scripts('main');
			wp_print_styles('style');
			wp_print_styles('portfoliocss');
		}
		function after_setup_theme(){
			add_theme_support( 'post-thumbnails' );
		}
	////////////////////////////////////////////HANDLE THE ADMIN MENU OPTIONS////////////////////////////////////////////
		function theme_options_menu() {
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'save'){
				foreach($this->theme_options as $value){
					if(isset($_REQUEST[$value['id']])){
						$this->options->{$value['id']} = stripslashes($_REQUEST[$value['id']]);
					}
				}
				$this->options->save();
				if(stristr($_SERVER['REQUEST_URI'], '&saved=true')){
					$location = $_SERVER['REQUEST_URI'];
				}else{
					$location = $_SERVER['REQUEST_URI'] . '&saved=true';
				}
				header("Location: $location");	
			}
			add_options_page('jQuery Portfolio Options', 'jQuery Portfolio', 'manage_options', __FILE__, array(&$this, 'portfolio_options'));
		}
		function get_style_options(){
			$style_options = array('options' => __('Default'), 'grey' => __('Grey'), 'none' => __('None'), 'dark' => __('Dark'));
			return apply_filters('jquery-portfolio-styles', $style_options);
		}	
		function portfolio_options() {
			if ( !current_user_can( 'manage_options' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			?>
				<div class="wrap">
				<?php screen_icon(); ?>
				<h2 class="alignleft"><?php _e('jQuery Portfolio Settings'); ?></h2>
				<br clear="all" />
				<?php if(isset($_REQUEST['saved']) && $_REQUEST['saved']) {?>
				<div id="message" class="updated fade"><p><strong><?php _e('Settings Saved!') ?></strong></p></div>
				<?php } ?>
				<form method="post" id="my_form" enctype="multipart/form-data">
					<div id="poststuff" class="metabox-holder">
						<div class="stuffbox">
							<h3><label><?php _e('Size Settings') ?></label></h3>
							<div class="inside">
								<table class="form-table" style="width:auto;">
									<?php 
										foreach($this->theme_options as $value){
											if(!isset($value['id'])){
												continue;
											}
											switch( $value['id']){
												case 'item_width':
												case 'item_height':
								    ?>		
								<tr>
									<th scope="row">
										<strong><?php echo $value['label']; ?></strong>
									</th>
									<td>
										<input type="text" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="<?php echo $this->options->{$value['id']}; ?>"  />
									</td>
								</tr>		
												<?php
												break;										
											}
										}
									?>
										<?php 
										foreach($this->theme_options as $value){
											if(!isset($value['id'])){
												continue;
											}
											switch( $value['id']){
												case 'show_title':  ?>
								<tr>
									<th scope="row">
										<strong><?php echo $value['label']; ?></strong>
									</th>
									<td>
										<input type="checkbox" <?php echo ($this->options->{$value['id']} > 0 ? 'checked="checked"' : ''); ?> name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="1" />
									</td>
								</tr>	
									<?php
												break;										
											}
										}
									?>
										<?php 
										foreach($this->theme_options as $value){
											if(!isset($value['id'])){
												continue;
											}
											switch( $value['id']){
												case 'options_style':  ?>
								<tr>
									<th scope="row">
										<strong><?php echo $value['label']; ?></strong>
									</th>
									<td>	
										<select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
										  <?php  
										  $style_options = $this->get_style_options();
										  foreach($style_options as $option_value => $label){
										  	?>
										  	<option value="<?php echo $option_value; ?>"<?php echo ($this->options->{$value['id']} == $option_value) ? ' selected="selected"' : ''; ?>><?php echo $label; ?></option>
										  	<?php
										  }
										  ?>
										</select>
									</td>
								</tr>	
									<?php
												break;										
											}
										}
									?>
									<tr>
										<th scope="row"></th>
									</tr>
								</table>
							</div>
						</div>
					</div>
				<input type="submit" name ="save" class="button-primary" value="<?php _e('Save Changes'); ?>" />
				<input type="hidden" name="action" value="save" />	
			</form>
		</div>
		<?php
	}	
}
$portfolio = new Portfolio();
?>