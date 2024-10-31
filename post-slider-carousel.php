<?php
/*
  Plugin Name: Post Sliders & Post Grids
  Plugin URI:https://www.i13websolution.com/product/wordpress-post-sliders-and-post-grids/
  Author URI:https://www.i13websolution.com/
  Description:Post Sliders and Grids is beautiful responsive post thumbnail image slider as well as post grid.It support post exclusion,Categort exclusion and also support custom post type.
  Author:I Thirteen Web Solution
  Version:1.0.21
  Text Domain:post-slider-carousel
  Domain Path: /languages
 */
// error_reporting(0);

add_theme_support( 'post-thumbnails' );
add_filter( 'widget_text', 'do_shortcode' );
add_action( 'admin_menu', 'psc_admin_menu' );
register_activation_hook( __FILE__, 'psc_install_post_slider_carousel' );
register_deactivation_hook( __FILE__, 'psc_post_slider_and_grid_remove_access_capabilities' );
add_action( 'wp_enqueue_scripts', 'psc_post_slider_carousel_load_styles_and_js' );
add_shortcode( 'psc_print_post_slider_carousel', 'psc_print_post_slider_carousel_func' );
add_shortcode( 'psc_print_post_grid', 'psc_print_post_grid_func' );
add_action( 'admin_notices', 'psc_post_slider_carousel_admin_notices' );
add_filter( 'user_has_cap', 'psc_post_slider_and_grid_admin_cap_list', 10, 4 );
add_action( 'plugins_loaded', 'psc_post_slider_carousel_load_lang' );

function psc_my_Categ_tree( $termID, $post_categorySelected, $TermName = '', $separator = '', $parent_shown = true ) {

			$output = '';
			$args = 'hierarchical=1&taxonomy=' . $TermName . '&hide_empty=0&orderby=id&parent=';
	if ( $parent_shown ) {
		$term = get_term( $termID, $TermName );

		$checked = '';
		if ( in_array( $term->term_id, $post_categorySelected ) ) {

			$checked = "checked='checked'";
		}
		$output = "<input value='{$term->term_id}' $checked type='checkbox' name='post_category[]' />  $separator  $term->name  ($term->taxonomy) <br/>";
		$parent_shown = false;

	}
		$separator .= '-';
				$newargs=array( 'hierarchical'=>1, 'taxonomy'=>$TermName, 'hide_empty'=>0, 'orderby'=>'id', 'parent'=>$termID );
		//$terms = get_terms( $TermName, $args . $termID );
				$terms = get_terms( $newargs );
	if ( count( $terms ) > 0 ) {
		foreach ( $terms as $term ) {
			// $selected = ($cat->term_id=="22") ? " selected": "";
			// $output .=  '<option value="'.$category->term_id.'" '.$selected .'>'.$separator.$category->cat_name.'</option>';
			$checked = '';
			if ( in_array( $term->term_id, $post_categorySelected ) ) {

				$checked = "checked='checked'";
			}
				$output .= " $separator <input value='{$term->term_id}' $checked type='checkbox' name='post_category[]' /> $term->name  ($term->taxonomy) <br/>";
				$output .= psc_my_Categ_tree( $term->term_id, $post_categorySelected, $TermName, $separator, $parent_shown );

		}
	}
		return $output;
}

function psc_post_slider_carousel_load_lang() {

		load_plugin_textdomain( 'post-slider-carousel', false, basename( __DIR__ ) . '/languages/' );
		add_filter( 'map_meta_cap', 'map_psc_post_slider_and_grid_meta_caps', 10, 4 );
}

function map_psc_post_slider_and_grid_meta_caps( array $caps, $cap, $user_id, array $args ) {

	if ( ! in_array(
		$cap,
		array(
			'psc_post_slider_settings',
			'psc_preview_post_slider',
			'psc_post_grid_settings',
			'psc_preview_post_grid',

		),
		true
	) ) {

		return $caps;
	}

	   $caps = array();

	switch ( $cap ) {

		case 'psc_post_slider_settings':
			$caps[] = 'psc_post_slider_settings';
			break;

		case 'psc_preview_post_slider':
			   $caps[] = 'psc_preview_post_slider';
			break;

		case 'psc_post_grid_settings':
				 $caps[] = 'psc_post_grid_settings';
			break;

		case 'psc_preview_post_grid':
				  $caps[] = 'psc_preview_post_grid';
			break;

		default:
			   $caps[] = 'do_not_allow';
			break;
	}
			
		  /** map plugin permissions **/  
	  return apply_filters( 'psc_post_slider_and_grid_meta_caps', $caps, $cap, $user_id, $args );
}


function psc_post_slider_and_grid_admin_cap_list( $allcaps, $caps, $args, $user ) {

	if ( ! in_array( 'administrator', $user->roles ) ) {

		return $allcaps;
	} else {

		if ( ! isset( $allcaps['psc_post_slider_settings'] ) ) {

			 $allcaps['psc_post_slider_settings'] = true;
		}

		if ( ! isset( $allcaps['psc_preview_post_slider'] ) ) {

			  $allcaps['psc_preview_post_slider'] = true;
		}

		if ( ! isset( $allcaps['psc_post_grid_settings'] ) ) {

			$allcaps['psc_post_grid_settings'] = true;
		}
		if ( ! isset( $allcaps['psc_preview_post_grid'] ) ) {

			$allcaps['psc_preview_post_grid'] = true;
		}
	}

	   return $allcaps;
}

function psc_post_slider_and_grid_add_access_capabilities() {

	// Capabilities for all roles.
	$roles = array( 'administrator' );
	foreach ( $roles as $role ) {

			$role = get_role( $role );
		if ( empty( $role ) ) {
				continue;
		}

		if ( ! $role->has_cap( 'psc_post_slider_settings' ) ) {

				$role->add_cap( 'psc_post_slider_settings' );
		}

		if ( ! $role->has_cap( 'psc_preview_post_slider' ) ) {

				$role->add_cap( 'psc_preview_post_slider' );
		}

		if ( ! $role->has_cap( 'psc_post_grid_settings' ) ) {

				$role->add_cap( 'psc_post_grid_settings' );
		}

		if ( ! $role->has_cap( 'psc_preview_post_grid' ) ) {

				$role->add_cap( 'psc_preview_post_grid' );
		}
	}

	$user = wp_get_current_user();
	$user->get_role_caps();
}

function psc_post_slider_and_grid_remove_access_capabilities() {

	$wp_roles = new WP_Roles();

	foreach ( $wp_roles->roles as $role => $details ) {
			$role = $wp_roles->get_role( $role );
		if ( empty( $role ) ) {
				continue;
		}

			$role->remove_cap( 'psc_post_slider_settings' );
			$role->remove_cap( 'psc_preview_post_slider' );
			$role->remove_cap( 'psc_post_grid_settings' );
			$role->remove_cap( 'psc_preview_post_grid' );

	}

	// Refresh current set of capabilities of the user, to be able to directly use the new caps.
	$user = wp_get_current_user();
	$user->get_role_caps();
}

function psc_post_slider_carousel_admin_notices() {

	if ( is_plugin_active( 'post-slider-carousel/post-slider-carousel.php' ) ) {

		$uploads = wp_upload_dir();
		$baseDir = $uploads['basedir'];
		$baseDir = str_replace( '\\', '/', $baseDir );
		$pathToImagesFolder = $baseDir . '/post-slider-carousel';

		if ( file_exists( $pathToImagesFolder ) && is_dir( $pathToImagesFolder ) ) {

			if ( ! is_writable( $pathToImagesFolder ) ) {
				echo "<div class='updated'><p>" . esc_html( __( 'Post Slider Carousel is active but does not have write permission on', 'post-slider-carousel' ) ) . '</p><p><b>' . esc_html( $pathToImagesFolder ) . '</b>' . esc_html( __( ' directory.Please allow write permission.', 'post-slider-carousel' ) ) . '</p></div> ';
			}
		} else {

			wp_mkdir_p( $pathToImagesFolder );
			if ( ! file_exists( $pathToImagesFolder ) && ! is_dir( $pathToImagesFolder ) ) {
				echo "<div class='updated'><p>" . esc_html( __( 'Post Slider Carousel is active but plugin does not have permission to create directory', 'post-slider-carousel' ) ) . '</p><p><b>' . esc_html( $pathToImagesFolder ) . '</b> ' . esc_html( __( '.Please create post-slider-carousel directory inside upload directory and allow write permission.', 'post-slider-carousel' ) ) . '</p></div> ';
			}
		}
	}
}

function psc_post_slider_carousel_load_styles_and_js() {

	if ( ! is_admin() ) {

		wp_register_style( 'p_s_c_bx', plugins_url( '/css/p_s_c_bx.css', __FILE__ ), array(), '1.0.15' );
		wp_register_style( 'psc_grid', plugins_url( '/css/psc_grid.css', __FILE__ ), array(), '1.0.12' );
		wp_register_style( 'font-awesome.min', plugins_url( '/css/font-awesome/css/font-awesome.min.css', __FILE__ ), array(), '1.0.12' );
		wp_register_script( 'p_s_c_bx', plugins_url( '/js/p_s_c_bx.js', __FILE__ ), array( 'jquery' ), '1.0.15' );
		wp_register_script( 'psc_grid_min', plugins_url( '/js/psc_grid_min.js', __FILE__ ), array( 'jquery' ), '1.0.18' );

	}
}

function psc_install_post_slider_carousel() {

	global $wpdb;

	$psc_slider_settings = array(
		'linkimage' => '1',
		'open_link_in' => 0,
		'min_post' => 1,
		'max_post' => 3,
		'max_post_retrive' => '-1',
		'postype' => '',
		'post_category' => '',
		'post_exclude' => '',
		'show_caption' => 1,
		'show_pager' => 0,
		'pauseonmouseover' => 1,
		'auto' => 0,
		'speed' => 1000,
		'pause' => 1000,
		'circular' => 1,
		'imageheight' => '',
		'imagewidth' => '',
		'imageMargin' => 15,
		'scroll' => 1,
		'sort_by' => 'date',
		'sort_direction' => 2,
		'scollerBackground' => '#FFFFFF',
		'postype_include_exclude' => 0,
		'categories_include_exclude' => 0,
	);

	$existingopt = get_option( 'psc_slider_settings' );

	if ( ! is_array( $existingopt ) ) {

		 update_option( 'psc_slider_settings', $psc_slider_settings );
	} else {
		  $flag = false;
		if ( ! isset( $existingopt['postype_include_exclude'] ) ) {

			$flag = true;
			$existingopt['postype_include_exclude'] = 0;
		}

		if ( ! isset( $existingopt['categories_include_exclude'] ) ) {

			$flag = true;
			$existingopt['categories_include_exclude'] = 0;
		}

		if ( true == $flag ) {

			update_option( 'psc_slider_settings', $existingopt );
		}
	}

	$psc_pgrid_settings = array(
		'cols' => '4',
		'cols1024' => '3',
		'cols800' => '2',
		'cols640' => '1',
		'heading_cl' => '#444444',
		'post_meta_cl' => '#999999',
		'content_cl' => '#777',
		'read_more_cl' => '#aaaaaa',
		'read_more_hcl' => '#777777',
		'postype' => '',
		'post_category' => '',
		'post_exclude' => '',
		'max_post_retrive' => '-1',
		'readMore_text' => 'Read More',
		'show_pager' => 0,
		'sort_by' => 'date',
		'sort_direction' => 2,
		'postype_include_exclude' => 0,
		'categories_include_exclude' => 0,

	);

	$existingopt = get_option( 'psc_pgrid_settings' );

	if ( ! is_array( $existingopt ) ) {

		 update_option( 'psc_pgrid_settings', $psc_pgrid_settings );
	} else {

		   $flag = false;
		if ( ! isset( $existingopt['postype_include_exclude'] ) ) {

			$flag = true;
			$existingopt['postype_include_exclude'] = 0;
		}

		if ( ! isset( $existingopt['categories_include_exclude'] ) ) {

			$flag = true;
			$existingopt['categories_include_exclude'] = 0;
		}

		if ( true == $flag ) {

			update_option( 'psc_pgrid_settings', $existingopt );
		}
	}

	$uploads = wp_upload_dir();
	$baseDir = $uploads['basedir'];
	$baseDir = str_replace( '\\', '/', $baseDir );
	$pathToImagesFolder = $baseDir . '/psc_post_slider_carousel';
	wp_mkdir_p( $pathToImagesFolder );
	psc_post_slider_and_grid_add_access_capabilities();
}

function psc_admin_menu() {

	$hook_suffix_c_r_l = add_menu_page( __( 'Post Slider & Grid', 'post-slider-carousel' ), __( 'Post Slider & Grid', 'post-slider-carousel' ), 'psc_post_slider_settings', 'psc_post_slider_carousel', 'psc_post_slider_carousel_options_func' );
	$hook_suffix_r_l_2 = add_submenu_page( 'psc_post_slider_carousel', __( 'Preview Slider', 'post-slider-carousel' ), __( 'Preview Slider', 'post-slider-carousel' ), 'psc_preview_post_slider', 'psc_post_slider_carousel_preview', 'psc_post_slider_carousel_preview_func' );
	$hook_suffix_r_l_3 = add_submenu_page( 'psc_post_slider_carousel', __( 'Post Grid Settings', 'post-slider-carousel' ), __( 'Post Grid Settings', 'post-slider-carousel' ), 'psc_post_grid_settings', 'psc_post_slider_grid', 'psc_post_grid_options_func' );
	$hook_suffix_r_l_4 = add_submenu_page( 'psc_post_slider_carousel', __( 'Preview Post Grid', 'post-slider-carousel' ), __( 'Preview Post Grid', 'post-slider-carousel' ), 'psc_preview_post_grid', 'psc_post_slider_grid_preview', 'psc_post_grid_preview_func' );
	add_action( 'load-' . $hook_suffix_c_r_l, 'psc_admin_init' );
	add_action( 'load-' . $hook_suffix_r_l_2, 'psc_admin_init' );
	add_action( 'load-' . $hook_suffix_r_l_3, 'psc_admin_init' );
	add_action( 'load-' . $hook_suffix_r_l_4, 'psc_admin_init' );
}

function psc_admin_init() {

	$url = plugin_dir_url( __FILE__ );
	wp_enqueue_script( 'jquery.validate', $url . 'js/jquery.validate.js', array( 'jquery' ), '1.0.21' );
	wp_enqueue_style( 'p_s_c_bx', plugins_url( '/css/p_s_c_bx.css', __FILE__ ), array(), '1.0.21' );
	wp_enqueue_style( 'psc_grid', plugins_url( '/css/psc_grid.css', __FILE__ ), array(), '1.0.21' );
	wp_enqueue_style( 'font-awesome.min', plugins_url( '/css/font-awesome/css/font-awesome.min.css', __FILE__ ), array(), '1.0.21' );

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'p_s_c_bx', plugins_url( '/js/p_s_c_bx.js', __FILE__ ), array( 'jquery' ), '1.0.21' );
	wp_enqueue_script( 'psc_grid_min', plugins_url( '/js/psc_grid_min.js', __FILE__ ), array( 'jquery' ), '1.0.21' );
	wp_enqueue_style( 'admin-psc-css-slider', plugins_url( '/css/admin-css.css', __FILE__ ), array(), '1.0.21' );

	psc_post_slider_carousel_admin_scripts_init();
}


function psc_post_slider_carousel_options_func() {

	if ( ! current_user_can( 'psc_post_slider_settings' ) ) {

		wp_die( esc_html( __( 'Access Denied', 'post-slider-carousel' ) ) );

	}

	if ( isset( $_POST['btnsave'] ) ) {

		if ( ! check_admin_referer( 'action_settings_add_edit', 'add_edit_nonce' ) ) {

				wp_die( 'Security check fail' );
		}

			$show_caption = isset( $_POST['show_caption'] ) ? intval( $_POST['show_caption'] ) : 0;
			$show_pager = isset( $_POST['show_pager'] ) ? intval( $_POST['show_pager'] ) : 0;

			$scollerBackground = isset( $_POST['scollerBackground'] ) ? sanitize_hex_color( $_POST['scollerBackground'] ) : '#000000';

		if ( isset( $_POST['circular'] ) ) {
			$circular = 1;
		} else {
			$circular = 0;
		}

		if ( isset( $_POST['pauseonmouseover'] ) ) {
			$pauseonmouseover = 1;
		} else {
			$pauseonmouseover = 0;
		}

			$auto = isset( $_POST['isauto'] ) ? sanitize_text_field( $_POST['isauto'] ) : 'auto';

		if ( 'auto' == $auto ) {
			$auto = 1;
		} else if ( 'manuall' == $auto ) {
			$auto = 0;
		} else {
			$auto = 2;
		}

			$speed = isset( $_POST['speed'] ) ? intval( $_POST['speed'] ) : 1000;

		if ( isset( $_POST['pause'] ) && '' == $_POST['pause'] ) {

			$pause = 1000;

		} else {

			$pause = intval( $_POST['pause'] );
		}

				$min_post = isset( $_POST['min_post'] ) ? intval( $_POST['min_post'] ) : 3;
				$max_post = isset( $_POST['max_post'] ) ? intval( $_POST['max_post'] ) : 3;
				$max_post_retrive = isset( $_POST['max_post_retrive'] ) ? intval( $_POST['max_post_retrive'] ) : 10;

							   
			$postype = '';
		if ( isset( $_POST['postype'] ) && is_array( $_POST['postype'] ) ) {

								$postype = array_map( 'sanitize_text_field', $_POST['postype'] );
																$postype=implode(',', $postype);

		}
				
			$post_category = '';
		if ( isset( $_POST['post_category'] ) && is_array( $_POST['post_category'] ) ) {
					
						 $post_category = array_map( 'sanitize_text_field', $_POST['post_category'] );
												 
												 $post_category=implode(',', $post_category);
												 

		}

			$post_exclude = isset( $_POST['post_exclude'] ) ? sanitize_text_field( $_POST['post_exclude'] ) : '';

			$sort_by = isset( $_POST['sort_by'] ) ? sanitize_text_field( $_POST['sort_by'] ) : '';

			$sort_direction = isset( $_POST['sort_direction'] ) ? sanitize_text_field( $_POST['sort_direction'] ) : '';

		if ( 'desc' == $sort_direction ) {

			$sort_direction = 2;

		} else {
			$sort_direction = 1;

		}

		if ( isset( $_POST['linkimage'] ) ) {
			$linkimage = 1;
		} else {
			$linkimage = 0;
		}

		if ( isset( $_POST['open_link_in'] ) ) {
			$open_link_in = 1;
		} else {
			$open_link_in = 0;
		}

			$imageheight = isset( $_POST['imageheight'] ) ? intval( $_POST['imageheight'] ) : 200;
			$imagewidth = isset( $_POST['imagewidth'] ) ? intval( $_POST['imagewidth'] ) : 200;

			$scroll = isset( $_POST['scroll'] ) ? intval( $_POST['scroll'] ) : 1;

			$imageMargin = isset( $_POST['imageMargin'] ) ? intval( $_POST['imageMargin'] ) : 0;

			$postype_include_exclude = isset( $_POST['postype_include_exclude'] ) ? intval( $_POST['postype_include_exclude'] ) : '';
			$categories_include_exclude = isset( $_POST['categories_include_exclude'] ) ? intval( $_POST['categories_include_exclude'] ) : '';

			$options = array();

		 $options['linkimage'] = $linkimage;
		 $options['open_link_in'] = $open_link_in;
		 $options['min_post'] = $min_post;
		 $options['max_post'] = $max_post;
		 $options['max_post_retrive'] = $max_post_retrive;
		 $options['postype'] = $postype;
		 $options['post_category'] = $post_category;
		 $options['post_exclude'] = $post_exclude;
		 $options['show_caption'] = $show_caption;
		 $options['show_pager'] = $show_pager;
		 $options['pauseonmouseover'] = $pauseonmouseover;
		 $options['auto'] = $auto;
		 $options['speed'] = $speed;
		 $options['pause'] = $pause;
		 $options['circular'] = $circular;
		 $options['imageheight'] = $imageheight;
		 $options['imagewidth'] = $imagewidth;
		 $options['imageMargin'] = $imageMargin;
		 $options['scroll'] = $scroll;
		 $options['sort_by'] = $sort_by;
		 $options['sort_direction'] = $sort_direction;
		 $options['scollerBackground'] = $scollerBackground;
		 $options['postype_include_exclude'] = $postype_include_exclude;
		 $options['categories_include_exclude'] = $categories_include_exclude;

		 $settings = update_option( 'psc_slider_settings', $options );
		 $psc_messages = array();
		 $psc_messages['type'] = 'succ';
		 $psc_messages['message'] = 'Settings saved successfully.';
		 update_option( 'psc_messages', $psc_messages );

	}

	  $settings = get_option( 'psc_slider_settings' );
	  $postypeSelected = array();
	  $post_categorySelected = array();
		  
	if ( '' != $settings['postype'] ) {

		 $postypeSelected = explode( ',', $settings['postype'] );
	}

	if ( '' != $settings['post_category'] ) {

		$post_categorySelected = explode( ',', $settings['post_category'] );

	}

	?>      
<div id="poststuff" > 
   <div id="post-body" class="metabox-holder columns-2" >  
	  <div id="post-body-content">
		  <style>
			#cat_list{height: 200px;overflow: auto}
			#namediv input {
				width: auto;
			}

			#cat_list .children {
				padding-left: 11px;
				padding-top: 8px;
			}

			cat_list.ul {
				padding: 0;
				margin: 0;
				list-style-type: none;
				position: relative;
			  }
			   li[id*="category"] {
				list-style-type: none;
				border-left: 2px solid #000;
				margin-left: 1em;
				margin-bottom: 0px;
			  }
			  li[id*="category"] label {
				padding-left: 1em;
				position: relative;
			  }
			  li[id*="category"] label::before {
				content:'';
				position: absolute;
				top: 0;
				left: -2px;
				bottom: 50%;
				width: 0.75em;
				border: 2px solid #000;
				border-top: 0 none transparent;
				border-right: 0 none transparent;
			  }
			  ul > li[id*="category"]:last-child {
				border-left: 2px solid transparent;
				margin-bottom: 0px;
				vertical-align:unset;
			  }
			  .selectit{vertical-align: top}

				.fieldsetAdmin {
					margin: 10px 0px;
					padding: 10px;
					border: 1px solid rgb(221, 221, 221);
					font-size: 15px;
				}
					.fieldsetAdmin legend {
						font-weight: bold;
						color: #222222;

					}
		</style>
		  <div class="wrap">
			  <table><tr>
					   <td>
							<div class="fb-like" data-href="https://www.facebook.com/i13websolution" data-layout="button" data-action="like" data-size="large" data-show-faces="false" data-share="false"></div>
							<div id="fb-root"></div>
							  <script>(function(d, s, id) {
								var js, fjs = d.getElementsByTagName(s)[0];
								if (d.getElementById(id)) return;
								js = d.createElement(s); js.id = id;
								js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2&appId=158817690866061&autoLogAppEvents=1';
								fjs.parentNode.insertBefore(js, fjs);
							  }(document, 'script', 'facebook-jssdk'));</script>
						</td>
					  <td>
						  <a target="_blank" title="Donate" href="http://www.i13websolution.com/donate-wordpress_image_thumbnail.php">
							  <img id="help us for free plugin" height="30" width="90" src="<?php echo esc_url( plugins_url( 'images/paypaldonate.jpg', __FILE__ ) ); ?>" border="0" alt="help us for free plugin" title="help us for free plugin">
						  </a>
					  </td>
				  </tr>
			  </table>

			  <?php
				  $messages = get_option( 'psc_messages' );
				  $type = '';
				  $message = '';
				if ( isset( $messages['type'] ) && '' != $messages['type'] ) {

					$type = $messages['type'];
					$message = $messages['message'];

				}

				if ( trim( $type ) == 'err' ) {
					echo "<div class='notice notice-error is-dismissible'><p>";
					echo esc_html( $message );
					echo '</p></div>';} else if ( trim( $type ) == 'succ' ) {
					echo "<div class='notice notice-success is-dismissible'><p>";
					echo esc_html( $message );
					echo '</p></div>';}

					update_option( 'psc_messages', array() );
					?>
					
			  <span><h3 style="color: blue;"><a target="_blank" href="https://www.i13websolution.com/product/wordpress-post-sliders-and-post-grids/"><?php echo esc_html( __( 'UPGRADE TO PRO VERSION', 'post-slider-carousel' ) ); ?></a></h3></span>
			  <h2><?php echo esc_html( __( 'Slider Settings', 'post-slider-carousel' ) ); ?></h2>
			  <div id="poststuff">
				  <div id="post-body" class="metabox-holder columns-2">
					  <div id="post-body-content">
						  <form method="post" action="" id="scrollersettiings" name="scrollersettiings" >
										
										  <fieldset class="fieldsetAdmin">
											<legend><?php echo esc_html( __( 'Slider Settings', 'post-slider-carousel' ) ); ?></legend>
											
											
										  
											<div class="stuffbox" id="namediv" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Show Caption ?', 'post-slider-carousel' ) ); ?></label></h3>
											<div class="inside">
												<table>
													<tr>
														<td>
															<input style="width:20px;" type='radio' 
															<?php
															if ( true == $settings['show_caption'] ) {
																echo "checked='checked'";
															}
															?>
															  name='show_caption' value='1' ><?php echo esc_html( __( 'Yes', 'post-slider-carousel' ) ); ?> &nbsp;<input style="width:20px;" type='radio' name='show_caption' 
															<?php
															if ( false == $settings['show_caption'] ) {
																echo "checked='checked'";
															}
															?>
															 value='0' ><?php echo esc_html( __( 'No', 'post-slider-carousel' ) ); ?>
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>
													<div style="clear:both"></div>
												</div>
											</div>
											
										  <div class="stuffbox" id="Show_Pager_div" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Show Pager ?', 'post-slider-carousel' ) ); ?></label></h3>
											<div class="inside">
												<table>
													<tr>
														<td>
															<input style="width:20px;" type='radio' 
															<?php
															if ( 1 == $settings['show_pager'] ) {
																echo "checked='checked'";
															}
															?>
														  name='show_pager' value='1' ><?php echo esc_html( __( 'Yes', 'post-slider-carousel' ) ); ?> &nbsp;<input style="width:20px;" type='radio' name='show_pager' 
														<?php
														if ( 0 == $settings['show_pager'] ) {
															echo "checked='checked'";
														}
														?>
														 value='0' ><?php echo esc_html( __( 'No', 'post-slider-carousel' ) ); ?>
															<div style="clear:both"></div>
															<div></div>
														</td>
													</tr>
												</table>
												<div style="clear:both"></div>
											</div>
										</div>

										<div class="stuffbox" id="namediv" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Slider Background color', 'post-slider-carousel' ) ); ?></label></h3>
											<div class="inside">
												<table>
													<tr>
														<td>
															<input type="text" id="scollerBackground" size="30" name="scollerBackground" value="<?php echo esc_attr( ( '' != $settings['scollerBackground'] && null != $settings['scollerBackground'] ) ? $settings['scollerBackground'] : '#ffffff' ); ?>"  style="width:100px;">
															<div style="clear:both"></div>
															<div></div>
														</td>
													</tr>
												</table>

												<div style="clear:both"></div>
											</div>
										</div>
											<div class="stuffbox" id="Circular_Slider" style="width:100%;">
												<h3><label ><?php echo esc_html( __( 'Circular Slider ?', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="checkbox" id="circular" size="30" name="circular" value="" 
																<?php
																if ( true == $settings['circular'] ) {
																	echo "checked='checked'";
																}
																?>
															 style="width:20px;">&nbsp;<?php echo esc_html( __( 'Circular Slider ?', 'post-slider-carousel' ) ); ?> 
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>
													<div style="clear:both"></div>

												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Pause On Mouse Over ?', 'post-slider-carousel' ) ); ?> </label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="checkbox" id="pauseonmouseover" size="30" name="pauseonmouseover" value="" 
																<?php
																if ( true == $settings['pauseonmouseover'] ) {
																	echo "checked='checked'";
																}
																?>
															 style="width:20px;">&nbsp;<?php echo esc_html( __( 'Pause On Mouse Over ?', 'post-slider-carousel' ) ); ?> 
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>
													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="Auto_Scroll" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Auto Scroll ?', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input style="width:20px;" type='radio' 
																<?php
																if ( 1 == $settings['auto'] ) {
																	echo "checked='checked'";
																}
																?>
															  name='isauto' value='auto' ><?php echo esc_html( __( 'Auto', 'post-slider-carousel' ) ); ?> &nbsp;<input style="width:20px;" type='radio' name='isauto' 
															<?php
															if ( 0 == $settings['auto'] ) {
																echo "checked='checked'";
															}
															?>
															 value='manuall' ><?php echo esc_html( __( 'Scroll By Left & Right Arrow', 'post-slider-carousel' ) ); ?> &nbsp; &nbsp;<input style="width:20px;" type='radio' name='isauto' 
															<?php
															if ( 2 == $settings['auto'] ) {
																echo "checked='checked'";
															}
															?>
															 value='both' ><?php echo esc_html( __( 'Scroll Auto With Arrow', 'post-slider-carousel' ) ); ?>
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>
													<div style="clear:both"></div>
												</div>
											</div>

											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label ><?php echo esc_html( __( 'Speed', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="speed" size="30" name="speed" value="<?php echo esc_attr( $settings['speed'] ); ?>" style="width:100px;">
																<div style="clear:both;margin-top:3px" id="speed_example"><?php echo esc_html( __( 'Example 1000', 'post-slider-carousel' ) ); ?></div>
																<div></div>
															</td>
														</tr>
													</table>
													<div style="clear:both"></div>

												</div>
											</div>
											<div class="stuffbox" id="Pause_div" style="width:100%;">
												<h3><label ><?php echo esc_html( __( 'Pause', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="pause" size="30" name="pause" value="<?php echo esc_attr( $settings['pause'] ); ?>" style="width:100px;">
																<div style="clear:both;margin-top:3px"><?php echo esc_html( __( 'Example 1000', 'post-slider-carousel' ) ); ?></div>
																<div></div>
															</td>
														</tr>
													</table>
													<div style="clear:both"><?php echo esc_html( __( 'The amount of time (in ms) between each auto transition', 'post-slider-carousel' ) ); ?></div>
												</div>
											</div>
											
										</fieldset>
										<fieldset class="fieldsetAdmin">
											<legend><?php echo esc_html( __( 'Post Settings', 'post-slider-carousel' ) ); ?></legend>
											<div class="stuffbox" id="namediv" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Min Post', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="min_post" size="30" name="min_post" value="<?php echo esc_attr( $settings['min_post'] ); ?>" style="width:100px;">
																<div style="clear:both"><?php echo esc_html( __( 'This will decide your slider width in responsive layout.It will show number of post at time.For example 2', 'post-slider-carousel' ) ); ?></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>

												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Max Post', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="max_post" size="30" name="max_post" value="<?php echo esc_attr( $settings['max_post'] ); ?>" style="width:100px;">
																<div style="clear:both"><?php echo esc_html( __( 'This will decide your slider width automatically.It will show number of post at time.For example 5', 'post-slider-carousel' ) ); ?></div>
																<div></div>
															</td>
														</tr>
													</table>
													<?php echo esc_html( __( 'specifies the number of items visible at all times within the slider.', 'post-slider-carousel' ) ); ?>
													
													<div style="clear:both"></div>

												</div>
											</div> 
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Maximum Post To be Retrieve From', 'post-slider-carousel' ) ); ?> </label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="max_post_retrive" size="30" name="max_post_retrive" value="<?php echo esc_attr( $settings['max_post_retrive'] ); ?>" style="width:100px;">
																<div style="clear:both"><?php echo esc_html( __( '-1 will retrieve all post from wp_query', 'post-slider-carousel' ) ); ?></div>
																<div></div>
															</td>
														</tr>
													</table>
													<?php echo esc_html( __( 'specifies the number of post to be retrieved from WP_Query', 'post-slider-carousel' ) ); ?>
													
													<div style="clear:both"></div>

												</div>
											</div> 
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Post Types exclude or include?', 'post-slider-carousel' ) ); ?> </label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select id="postype_include_exclude" name="postype_include_exclude" style="min-width:200px" >
																	<option value="1" 
																	<?php
																	if ( 1 == $settings['postype_include_exclude'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Include', 'post-slider-carousel' ) ); ?></option>    
																	<option value="0" 
																	<?php
																	if ( 0 == $settings['postype_include_exclude'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Exclude', 'post-slider-carousel' ) ); ?></option>    

																  ?>
																</select>
															</td>
														</tr>
													</table>
												</div>
												<h3 id="post_type_to_include_lbl" style="display:none" ><label><?php esc_html( __( 'Post Types to Include', 'post-slider-carousel' ) ); ?> </label></h3>
												<h3 id="post_type_to_exclude_lbl" style="display:none"><label><?php echo esc_html( __( 'Post Types to Exclude', 'post-slider-carousel' ) ); ?> </label></h3>
											 
												<div class="inside">
													<table>
														<tr>
															<td>
																<ul id="cat_list_">
																<?php

																  $args = array(
																	  'public'   => true,

																  );

																	 $post_types = get_post_types( $args );

																	?>
																	<select id="postype" name="postype[]" style="min-width:200px" multiple="multiple">
																	  <option value=""><?php echo esc_html( __( 'Select', 'post-slider-carousel' ) ); ?></option>    
																		<?php foreach ( $post_types as $key => $p ) : ?>
																		  <option 
																			<?php
																			if ( in_array( $p, $postypeSelected ) ) :
																				?>
   selected="selected" <?php endif; ?>  value="<?php echo esc_attr( $p ); ?>"><?php echo esc_html( $p ); ?></option>  
																		<?php endforeach; ?> 
																	?>
																  </select>
																</ul>
																
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>

												</div>
											</div> 
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Categories to exclude or include?', 'post-slider-carousel' ) ); ?> </label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select id="categories_include_exclude" name="categories_include_exclude" style="min-width:200px" >
																	<option value="1" 
																	<?php
																	if ( 1 == $settings['categories_include_exclude'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Include', 'post-slider-carousel' ) ); ?></option>    
																	<option value="0" 
																	<?php
																	if ( 0 == $settings['categories_include_exclude'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Exclude', 'post-slider-carousel' ) ); ?></option>    

																  ?>
																</select>
															</td>
														</tr>
													</table>
												</div>
												
												<h3 id="categories_to_include_lbl" style="display:none" ><label><?php echo esc_html( __( 'Categories To Include', 'post-slider-carousel' ) ); ?> </label></h3>
												<h3 id="categories_to_exclude_lbl" style="display:none"><label><?php echo esc_html( __( 'Categories To Exclude', 'post-slider-carousel' ) ); ?> </label></h3>
											   
												<div class="inside">
													<table>
														<tr>
															<td>
																<ul id="cat_list">
																 <?php
																	foreach ( $post_types as $pst ) {

																			   $taxonomies = get_object_taxonomies( $pst );

																		if ( false != $taxonomies && null != $taxonomies && count( $taxonomies ) > 0 ) {

																			echo wp_kses_post( "<b style='margin-top:10px'>" . ucfirst( $pst ) . '</b><br/>' );
																			foreach ( $taxonomies as $tx ) {

																				$tx_ob = get_taxonomy( $tx );
																				if ( $tx_ob->public ) {
																					// if(strpos($tx, 'cat')!==false or strpos($tx, 'category')!==false){
																					foreach ( get_terms(
																						array(
																																														'taxonomy'=>$tx,
																							'hide_empty' => 0,
																							'parent' => 0,
																						)
																					) as $each ) {
																																											
																																											  $default_attribs = array(
																																												'id' => array(),
																																												'class' => array(),
																																												'title' => array(),
																																												'style' => array(),
																																												'data' => array(),
																																												'data-mce-id' => array(),
																																												'data-mce-style' => array(),
																																												'data-mce-bogus' => array(),
																																											  );

																																											  $allowed_tags = array(
																																												'div'           => $default_attribs,
																																												'span'          => $default_attribs,
																																												'p'             => $default_attribs,
																																												'a'             => array_merge( $default_attribs, array(
																																													'href' => array(),
																																													'target' => array( '_blank', '_top' ),
																																												) ),
																																												'u'             =>  $default_attribs,
																																												'i'             =>  $default_attribs,
																																												'q'             =>  $default_attribs,
																																												'ul'            => $default_attribs,
																																												'ol'            => $default_attribs,
																																												'li'            => $default_attribs,
																																												'<br>'            => $default_attribs,
																																												'hr'            => $default_attribs,
																																												'strong'        => $default_attribs,
																																												'blockquote'    => $default_attribs,
																																												'del'           => $default_attribs,
																																												'strike'        => $default_attribs,
																																												'em'            => $default_attribs,
																																												'code'          => $default_attribs,
																																												'input'          => array( 'type'=>'checkbox', 'name'=>'post_category[]', 'value'=>'*', 'checked'=>'*' ),
																																												'br'          => $default_attribs,
																																												'b'          => array( 'style'=>'margin-top:10px' ),
																																											  );


																																											   
																																											  echo wp_kses( psc_my_Categ_tree( $each->term_id, $post_categorySelected, $each->taxonomy, '', true ), $allowed_tags );
																					}
																					// }
																				}
																			}
																		}
																	}
																	 // echo wp_category_checklist(0,0,$post_categorySelected,false,null,false) ;
																	?>
																</ul>
																
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>

												</div>
											</div> 
											
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Posts To Exclude', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input id="post_exclude" value="<?php echo esc_attr( $settings['post_exclude'] ); ?>"  size="30" name="post_exclude" value="" type="text">
																
																<div></div>
															</td>
														</tr>
													</table>
													 <?php echo esc_html( __( 'comma separated post id\'s to exclude.', 'post-slider-carousel' ) ); ?>    
													<div style="clear:both"></div>

												</div>
											</div> 
											<div class="stuffbox" id="namediv" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Sort By', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select name="sort_by" id="sort_by">
																	<option value="date" 
																	<?php
																	if ( 'date' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?> ><?php echo esc_html( __( 'Date', 'post-slider-carousel' ) ); ?></option>
																	<option value="ID" 
																	<?php
																	if ( 'ID' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>   ><?php echo esc_html( __( 'ID', 'post-slider-carousel' ) ); ?></option>
																	<option value="author" 
																	<?php
																	if ( 'author' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Author', 'post-slider-carousel' ) ); ?></option>
																	<option value="title" 
																	<?php
																	if ( 'title' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Title', 'post-slider-carousel' ) ); ?></option>
																	<option value="name" 
																	<?php
																	if ( 'name' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Name', 'post-slider-carousel' ) ); ?></option>
																	<option value="rand" 
																	<?php
																	if ( 'rand' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Random', 'post-slider-carousel' ) ); ?></option>
																	<option value="menu_order" 
																	<?php
																	if ( 'menu_order' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Menu order', 'post-slider-carousel' ) ); ?></option>
																	<option value="comment_count" 
																	<?php
																	if ( 'comment_count' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Comment count', 'post-slider-carousel' ) ); ?></option>
																  </select>
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Sort Direction', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select name="sort_direction" id="sort_direction">
																<option value="asc" 
																<?php
																if ( '1' == $settings['sort_direction'] ) :
																	?>
  selected="selected" <?php endif; ?> ><?php echo esc_html( __( 'Ascending', 'post-slider-carousel' ) ); ?></option>
																<option value="desc" 
																<?php
																if ( '2' == $settings['sort_direction'] ) :
																	?>
  selected="selected" <?php endif; ?> ><?php echo esc_html( __( 'Descending', 'post-slider-carousel' ) ); ?></option>
															  </select>
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											
										</fieldset>  
										 <fieldset class="fieldsetAdmin">
										   <legend><?php echo esc_html( __( 'Image Settings', 'post-slider-carousel' ) ); ?></legend>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Link images with url ?', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="checkbox" id="linkimage" size="30" name="linkimage" value="" 
																<?php
																if ( true == $settings['linkimage'] ) {
																		echo "checked='checked'";
																}
																?>
																	 style="width:20px;">&nbsp;<?php echo esc_html( __( 'Add link to image ?', 'post-slider-carousel' ) ); ?> 
																<div style="clear:both;margin-top:3px"><?php echo esc_html( __( 'Add link to image? On click user will redirect to post url', 'post-slider-carousel' ) ); ?></div>
																<div></div>
															</td>
														</tr>
													</table>
													<div style="clear:both"></div>
												</div>
											</div>
										   <div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Open Post Link In New Tab ?', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="checkbox" id="open_link_in" size="30" name="open_link_in" value="" 
																<?php
																if ( true == $settings['open_link_in'] ) {
																		echo "checked='checked'";
																}
																?>
																	 style="width:20px;">&nbsp;<?php echo esc_html( __( 'Open Link In New Tab? ', 'post-slider-carousel' ) ); ?>
																
																<div></div>
															</td>
														</tr>
													</table>
													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Thumbnail Image Height', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="imageheight" size="30" name="imageheight" value="<?php echo esc_attr( $settings['imageheight'] ); ?>" style="width:100px;">
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Thumbnail Image Width', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="imagewidth" size="30" name="imagewidth" value="<?php echo esc_attr( $settings['imagewidth'] ); ?>" style="width:100px;">
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>


											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Scroll', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="scroll" size="30" name="scroll" value="<?php echo esc_attr( $settings['scroll'] ); ?>" style="width:100px;">
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>
													<?php echo esc_html( __( 'You can specify the number of items to scroll when you click the next or prev buttons.', 'post-slider-carousel' ) ); ?>
													<div style="clear:both"></div>
												</div>
											</div>


											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Image Margin', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="imageMargin" size="30" name="imageMargin" value="<?php echo esc_attr( $settings['imageMargin'] ); ?>" style="width:100px;">
																<div style="clear:both;padding-top:5px"><?php echo esc_html( __( 'Gap between two images', 'post-slider-carousel' ) ); ?> </div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
										 </fieldset>
										 
										<?php wp_nonce_field( 'action_settings_add_edit', 'add_edit_nonce' ); ?>       
										<input type="submit"  name="btnsave" id="btnsave" value="<?php echo esc_html( __( 'Save Changes', 'post-slider-carousel' ) ); ?>" class="button-primary">

									</form> 
									<script type="text/javascript">

										jQuery(document).ready(function() {
										//jQuery('input[type=radio][name=is_continues]').trigger("change")    
										jQuery("#scrollersettiings").validate({
										rules: {
										show_caption: {
												required:true,
												number:true
											  
										},
										show_pager: {
												required:true,
												number:true
											  
										},
										scollerBackground:{
												required:true,
												maxlength:7  
											},
										isauto: {
												required:true


										},     
										speed: {
												 required:true,
												 number:true
											   
										},     
										pause: {
												 required:true,
												 number:true
											   
										},     
										min_post: {
												 required:true,
												 number:true
											   
										},     
										max_post: {
												 required:true,
												 number:true
											   
										},     
										max_post_retrive: {
												 required:true,
												 number:true
											   
										},     
										imageheight: {
												 required:true,
												 number:true
											   
										},     
										imagewidth: {
												 required:true,
												 number:true
											   
										},     
										scroll: {
												 required:true,
												 number:true
											   
										},     
										imageMargin: {
												 required:true,
												 number:true
											   
										}    
									   
									},
												errorClass: "image_error",
												errorPlacement: function(error, element) {
												error.appendTo(element.next().next());
												}


										});

											jQuery('#scollerBackground').wpColorPicker();
												

											jQuery( "#postype_include_exclude" ).change(function() {


												if(jQuery( "#postype_include_exclude" ).val().toString()=="1"){

													  jQuery("#post_type_to_exclude_lbl").hide();
													  jQuery("#post_type_to_include_lbl").show();

												}
												else{

													   jQuery("#post_type_to_exclude_lbl").show();
													   jQuery("#post_type_to_include_lbl").hide();


												}

											});

											jQuery( "#categories_include_exclude" ).change(function() {


												if(jQuery( "#categories_include_exclude" ).val().toString()=="1"){

													  jQuery("#categories_to_exclude_lbl").hide();
													  jQuery("#categories_to_include_lbl").show();

												}
												else{

													   jQuery("#categories_to_exclude_lbl").show();
													   jQuery("#categories_to_include_lbl").hide();


												}

											});

										   jQuery( "#postype_include_exclude" ).trigger('change'); 
										   jQuery( "#categories_include_exclude" ).trigger('change'); 
									   
										});
									</script> 
									

					  </div>
				  </div>
			  </div>  
		  </div>      
	  </div>
	  <div id="postbox-container-1" class="postbox-container" > 

		<div class="postbox"> 
			  <h3 class="hndle"><span></span><?php echo esc_html( __( 'Access All Themes In One Price', 'post-slider-carousel' ) ); ?></h3> 
			  <div class="inside">
				  <center><a href="http://www.elegantthemes.com/affiliates/idevaffiliate.php?id=11715_0_1_10" target="_blank"><img border="0" src="<?php echo esc_url( plugins_url( 'images/300x250.gif', __FILE__ ) ); ?>" width="250" height="250"></a></center>

				  <div style="margin:10px 5px">

				  </div>
			  </div></div>
		   <div class="postbox"> 
			<h3 class="hndle"><span></span><?php echo esc_html( __( 'Google For Business Coupon', 'post-slider-carousel' ) ); ?></h3> 
				<div class="inside">
					<center><a href="https://goo.gl/OJBuHT" target="_blank">
							<img src="<?php echo esc_url( plugins_url( 'images/g-suite-promo-code-4.png', __FILE__ ) ); ?>" width="250" height="250" border="0">
						</a></center>
					<div style="margin:10px 5px">
					</div>
				</div>

			</div>

	  </div>      
	 <div class="clear"></div>
  </div>  
 </div> 
	<?php
}
function psc_post_grid_options_func() {

	if ( ! current_user_can( 'psc_post_grid_settings' ) ) {

		wp_die( esc_html( __( 'Access Denied', 'post-slider-carousel' ) ) );

	}

	if ( isset( $_POST['btnsave'] ) ) {

		if ( ! check_admin_referer( 'action_settings_add_edit', 'add_edit_nonce' ) ) {

				wp_die( 'Security check fail' );
		}

			$cols = isset( $_POST['cols'] ) ? intval( sanitize_text_field( $_POST['cols'] ) ) : 4;
			$cols1024 = isset( $_POST['cols1024'] ) ? intval( sanitize_text_field( $_POST['cols1024'] ) ) : 3;
			$cols800 = isset( $_POST['cols800'] ) ? intval( sanitize_text_field( $_POST['cols800'] ) ) : 2;
			$cols640 = isset( $_POST['cols640'] ) ? intval( sanitize_text_field( $_POST['cols640'] ) ) : 1;
			$heading_cl = isset( $_POST['heading_cl'] ) ? sanitize_hex_color( $_POST['heading_cl'] ) : '#444444';
			$post_meta_cl = isset( $_POST['post_meta_cl'] ) ? sanitize_hex_color( $_POST['post_meta_cl'] ) : '#999999';
			$content_cl = isset( $_POST['content_cl'] ) ? sanitize_hex_color( $_POST['content_cl'] ) : '#777777';
			$read_more_cl = isset( $_POST['read_more_cl'] ) ? sanitize_hex_color( $_POST['read_more_cl'] ) : '#aaaaaa';
			$read_more_hcl = isset( $_POST['read_more_hcl'] ) ? sanitize_hex_color( $_POST['read_more_hcl'] ) : '#777777';
			$readMore_text = isset( $_POST['readMore_text'] ) ? sanitize_text_field( $_POST['readMore_text'] ) : 'Read More';
			$show_pager = isset( $_POST['show_pager'] ) ? intval( sanitize_text_field( $_POST['show_pager'] ) ) : 1;
					$max_post_retrive = isset( $_POST['max_post_retrive'] ) ? intval( sanitize_text_field( $_POST['max_post_retrive'] ) ) : -1;
			$postype_include_exclude = isset( $_POST['postype_include_exclude'] ) ? intval( $_POST['postype_include_exclude'] ) : 0;
			$categories_include_exclude = isset( $_POST['categories_include_exclude'] ) ? intval( $_POST['categories_include_exclude'] ) : 0;

			$postype = '';
		if ( isset( $_POST['postype'] ) && is_array( $_POST['postype'] ) ) {

								$postype = array_map( 'sanitize_text_field', $_POST['postype'] );
																$postype=implode(',', $postype);

		}
				
			$post_category = '';
		if ( isset( $_POST['post_category'] ) && is_array( $_POST['post_category'] ) ) {
					
						 $post_category = array_map( 'sanitize_text_field', $_POST['post_category'] );
												 
												 $post_category=implode(',', $post_category);
												 

		}

						$post_exclude = isset( $_POST['post_exclude'] ) ? sanitize_text_field( $_POST['post_exclude'] ) : '';

			$sort_by = isset( $_POST['sort_by'] ) ? sanitize_text_field( $_POST['sort_by'] ) : '';

			$sort_direction = isset( $_POST['sort_direction'] ) ? sanitize_text_field( $_POST['sort_direction'] ) : '';

		if ( 'desc' == $sort_direction ) {

				$sort_direction = 2;
		} else {
				$sort_direction = 1;
		}

			$options = array();

		 $options['cols'] = $cols;
		 $options['cols1024'] = $cols1024;
		 $options['cols800'] = $cols800;
		 $options['cols640'] = $cols640;
		 $options['heading_cl'] = $heading_cl;
		 $options['post_meta_cl'] = $post_meta_cl;
		 $options['content_cl'] = $content_cl;
		 $options['read_more_cl'] = $read_more_cl;
		 $options['read_more_hcl'] = $read_more_hcl;
		 $options['readMore_text'] = $readMore_text;
		 $options['max_post_retrive'] = $max_post_retrive;
		 $options['postype'] = $postype;
		 $options['post_category'] = $post_category;
		 $options['post_exclude'] = $post_exclude;
		 $options['sort_by'] = $sort_by;
		 $options['sort_direction'] = $sort_direction;
		 $options['show_pager'] = $show_pager;
		 $options['postype_include_exclude'] = $postype_include_exclude;
		 $options['categories_include_exclude'] = $categories_include_exclude;

		 $settings = update_option( 'psc_pgrid_settings', $options );
		 $psc_messages = array();
		 $psc_messages['type'] = 'succ';
		 $psc_messages['message'] = 'Settings saved successfully.';
		 update_option( 'psc_messages', $psc_messages );

	}

	 $psc_pgrid_settings = array(
		 'cols' => '4',
		 'cols1024' => '3',
		 'cols800' => '2',
		 'cols640' => '1',
		 'heading_cl' => '#444444',
		 'post_meta_cl' => '#999999',
		 'content_cl' => '#777',
		 'read_more_cl' => '#aaaaaa',
		 'read_more_hcl' => '#777777',
		 'postype' => '',
		 'post_category' => '',
		 'max_post_retrive' => '-1',
		 'readMore_text' => 'Read More',
		 'post_exclude' => '',
		 'show_pager' => 0,
		 'sort_by' => 'date',
		 'sort_direction' => 2,
		 'postype_include_exclude' => 0,
		 'categories_include_exclude' => 0,

	 );

	 if ( ! get_option( 'psc_pgrid_settings' ) ) {

		 update_option( 'psc_pgrid_settings', $psc_pgrid_settings );
	 }

	  $settings = get_option( 'psc_pgrid_settings' );

	  $postypeSelected = array();
	  $post_categorySelected = array();

	 if ( '' != $settings['postype'] ) {

		  $postypeSelected = explode( ',', $settings['postype'] );
	 }

	 if ( '' != $settings['post_category'] ) {

		 $post_categorySelected = explode( ',', $settings['post_category'] );

	 }

		?>
			  
<div id="poststuff" > 
   <div id="post-body" class="metabox-holder columns-2" >  
	  <div id="post-body-content">
		  <style>
			#cat_list{height: 200px;overflow: auto}
			#namediv input {
				width: auto;
			}

			#cat_list .children {
				padding-left: 11px;
				padding-top: 8px;
			}

			cat_list.ul {
				padding: 0;
				margin: 0;
				list-style-type: none;
				position: relative;
			  }
			   li[id*="category"] {
				list-style-type: none;
				border-left: 2px solid #000;
				margin-left: 1em;
				margin-bottom: 0px;
			  }
			  li[id*="category"] label {
				padding-left: 1em;
				position: relative;
			  }
			  li[id*="category"] label::before {
				content:'';
				position: absolute;
				top: 0;
				left: -2px;
				bottom: 50%;
				width: 0.75em;
				border: 2px solid #000;
				border-top: 0 none transparent;
				border-right: 0 none transparent;
			  }
			  ul > li[id*="category"]:last-child {
				border-left: 2px solid transparent;
				margin-bottom: 0px;
				vertical-align:unset;
			  }
			  .selectit{vertical-align: top}

				.fieldsetAdmin {
					margin: 10px 0px;
					padding: 10px;
					border: 1px solid rgb(221, 221, 221);
					font-size: 15px;
				}
					.fieldsetAdmin legend {
						font-weight: bold;
						color: #222222;

					}
		</style>
		  <div class="wrap">
			  <table><tr>
					   <td>
							<div class="fb-like" data-href="https://www.facebook.com/i13websolution" data-layout="button" data-action="like" data-size="large" data-show-faces="false" data-share="false"></div>
							<div id="fb-root"></div>
							  <script>(function(d, s, id) {
								var js, fjs = d.getElementsByTagName(s)[0];
								if (d.getElementById(id)) return;
								js = d.createElement(s); js.id = id;
								js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2&appId=158817690866061&autoLogAppEvents=1';
								fjs.parentNode.insertBefore(js, fjs);
							  }(document, 'script', 'facebook-jssdk'));</script>
						</td>
					  <td>
						  <a target="_blank" title="Donate" href="http://www.i13websolution.com/donate-wordpress_image_thumbnail.php">
							  <img id="help us for free plugin" height="30" width="90" src="<?php echo esc_url( plugins_url( 'images/paypaldonate.jpg', __FILE__ ) ); ?>" border="0" alt="help us for free plugin" title="help us for free plugin">
						  </a>
					  </td>
				  </tr>
			  </table>

			  <?php
				  $messages = get_option( 'psc_messages' );
				  $type = '';
				  $message = '';
				if ( isset( $messages['type'] ) && '' != $messages['type'] ) {

					$type = $messages['type'];
					$message = $messages['message'];

				}

				if ( trim( $type ) == 'err' ) {
					echo "<div class='notice notice-error is-dismissible'><p>";
					echo esc_html( $message );
					echo '</p></div>';} else if ( trim( $type ) == 'succ' ) {
					echo "<div class='notice notice-success is-dismissible'><p>";
					echo esc_html( $message );
					echo '</p></div>';}

					update_option( 'psc_messages', array() );
					?>
					
			  <span><h3 style="color: blue;"><a target="_blank" href="https://www.i13websolution.com/product/wordpress-post-sliders-and-post-grids/"><?php echo esc_html( __( 'UPGRADE TO PRO VERSION', 'post-slider-carousel' ) ); ?></a></h3></span>
			  <h2><?php echo esc_html( __( 'Post Grid Settings', 'post-slider-carousel' ) ); ?></h2>
			  <div id="poststuff">
				  <div id="post-body" class="metabox-holder columns-2">
					  <div id="post-body-content">
						  <form method="post" action="" id="scrollersettiings" name="scrollersettiings" >
										
										  
										<fieldset class="fieldsetAdmin">
											<legend><?php echo esc_html( __( 'Post Settings', 'post-slider-carousel' ) ); ?></legend>
											
											<div class="stuffbox" id="namediv" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Post Grid Columns', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select name="cols" id="cols" style="width:200px">
																	<option value="1" 
																	<?php
																	if ( '1' == $settings['cols'] ) :
																		?>
  selected="selected" <?php endif; ?> ><?php echo esc_html( __( '1', 'post-slider-carousel' ) ); ?></option>
																	<option value="2" 
																	<?php
																	if ( '2' == $settings['cols'] ) :
																		?>
  selected="selected" <?php endif; ?>   ><?php echo esc_html( __( '2', 'post-slider-carousel' ) ); ?></option>
																	<option value="3" 
																	<?php
																	if ( '3' == $settings['cols'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '3', 'post-slider-carousel' ) ); ?></option>
																	<option value="4" 
																	<?php
																	if ( '4' == $settings['cols'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '4', 'post-slider-carousel' ) ); ?></option>
																	<option value="5" 
																	<?php
																	if ( '5' == $settings['cols'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '5', 'post-slider-carousel' ) ); ?></option>
																  </select>
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Post Grid Columns (1024)', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select name="cols1024" id="cols1024" style="width:200px">
																	<option value="1" 
																	<?php
																	if ( '1' == $settings['cols1024'] ) :
																		?>
  selected="selected" <?php endif; ?> ><?php echo esc_html( __( '1', 'post-slider-carousel' ) ); ?></option>
																	<option value="2" 
																	<?php
																	if ( '2' == $settings['cols1024'] ) :
																		?>
  selected="selected" <?php endif; ?>   ><?php echo esc_html( __( '2', 'post-slider-carousel' ) ); ?></option>
																	<option value="3" 
																	<?php
																	if ( '3' == $settings['cols1024'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '3', 'post-slider-carousel' ) ); ?></option>
																	<option value="4" 
																	<?php
																	if ( '4' == $settings['cols1024'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '4', 'post-slider-carousel' ) ); ?></option>
																	<option value="5" 
																	<?php
																	if ( '5' == $settings['cols1024'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '5', 'post-slider-carousel' ) ); ?></option>
																  </select>
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Post Grid Columns (800)', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select name="cols800" id="cols800" style="width:200px">
																	<option value="1" 
																	<?php
																	if ( '1' == $settings['cols800'] ) :
																		?>
  selected="selected" <?php endif; ?> ><?php echo esc_html( __( '1', 'post-slider-carousel' ) ); ?></option>
																	<option value="2" 
																	<?php
																	if ( '2' == $settings['cols800'] ) :
																		?>
  selected="selected" <?php endif; ?>   ><?php echo esc_html( __( '2', 'post-slider-carousel' ) ); ?></option>
																	<option value="3" 
																	<?php
																	if ( '3' == $settings['cols800'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '3', 'post-slider-carousel' ) ); ?></option>
																	<option value="4" 
																	<?php
																	if ( '4' == $settings['cols800'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '4', 'post-slider-carousel' ) ); ?></option>
																	<option value="5" 
																	<?php
																	if ( '5' == $settings['cols800'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '5', 'post-slider-carousel' ) ); ?></option>
																  </select>
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Post Grid Columns (640)', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select name="cols640" id="cols640" style="width:200px">
																	<option value="1" 
																	<?php
																	if ( '1' == $settings['cols640'] ) :
																		?>
  selected="selected" <?php endif; ?> ><?php echo esc_html( __( '1', 'post-slider-carousel' ) ); ?></option>
																	<option value="2" 
																	<?php
																	if ( '2' == $settings['cols640'] ) :
																		?>
  selected="selected" <?php endif; ?>   ><?php echo esc_html( __( '2', 'post-slider-carousel' ) ); ?></option>
																	<option value="3" 
																	<?php
																	if ( '3' == $settings['cols640'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '3', 'post-slider-carousel' ) ); ?></option>
																	<option value="4" 
																	<?php
																	if ( '4' == $settings['cols640'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '4', 'post-slider-carousel' ) ); ?></option>
																	<option value="5" 
																	<?php
																	if ( '5' == $settings['cols640'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( '6', 'post-slider-carousel' ) ); ?></option>
																  </select>
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Heading Color', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="heading_cl" size="30" name="heading_cl" value="<?php echo esc_attr( ( '' != $settings['heading_cl'] && null != $settings['heading_cl'] ) ? $settings['heading_cl'] : '#444444' ); ?>"  style="width:100px;">
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Post Meta Color', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="post_meta_cl" size="30" name="post_meta_cl" value="<?php echo esc_attr( ( '' != $settings['post_meta_cl'] && null != $settings['post_meta_cl'] ) ? $settings['post_meta_cl'] : '#999999' ); ?>"  style="width:100px;">
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Content Color', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="content_cl" size="30" name="content_cl" value="<?php echo esc_attr( ( '' != $settings['content_cl'] && null != $settings['content_cl'] ) ? $settings['content_cl'] : '#777777' ); ?>"  style="width:100px;">
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Readmore Color', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="read_more_cl" size="30" name="read_more_cl" value="<?php echo esc_attr( ( '' != $settings['read_more_cl'] && null != $settings['read_more_cl'] ) ? $settings['read_more_cl'] : '#aaaaaa' ); ?>"  style="width:100px;">
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Readmore Hover Color', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="read_more_hcl" size="30" name="read_more_hcl" value="<?php echo esc_attr( ( '' != $settings['read_more_hcl'] && null != $settings['read_more_hcl'] ) ? $settings['read_more_hcl'] : '#777777' ); ?>"  style="width:100px;">
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Read More Text', 'post-slider-carousel' ) ); ?> </label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="readMore_text" size="30" name="readMore_text" value="<?php echo esc_attr( $settings['readMore_text'] ); ?>" style="width:100px;">
																<div style="clear:both"><?php echo esc_html( __( 'Translate Readmore to your language', 'post-slider-carousel' ) ); ?></div>
																<div></div>
															</td>
														</tr>
													</table>
													<div style="clear:both"></div>

												</div>
											</div> 
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Maximum Post To be Retrieve From', 'post-slider-carousel' ) ); ?> </label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input type="text" id="max_post_retrive" size="30" name="max_post_retrive" value="<?php echo esc_attr( $settings['max_post_retrive'] ); ?>" style="width:100px;">
																<div style="clear:both"><?php echo esc_html( __( '-1 will retrieve all post from wp_query. If you would like to show pagination, then set page size and set pagination to true below last option.', 'post-slider-carousel' ) ); ?></div>
																<div></div>
															</td>
														</tr>
													</table>
													<?php echo esc_html( __( 'specifies the number of post to be retrieved from WP_Query', 'post-slider-carousel' ) ); ?>
													<div style="clear:both"></div>

												</div>
											</div> 
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Post Types exclude or include?', 'post-slider-carousel' ) ); ?> </label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select id="postype_include_exclude" name="postype_include_exclude" style="min-width:200px" >
																	<option value="1" 
																	<?php
																	if ( 1 == $settings['postype_include_exclude'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Include', 'post-slider-carousel' ) ); ?></option>    
																	<option value="0" 
																	<?php
																	if ( 0 == $settings['postype_include_exclude'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Exclude', 'post-slider-carousel' ) ); ?></option>    

																  ?>
																</select>
															</td>
														</tr>
													</table>
												</div>
												<h3 id="post_type_to_include_lbl" style="display:none" ><label><?php echo esc_html( __( 'Post Types to Include', 'post-slider-carousel' ) ); ?> </label></h3>
												<h3 id="post_type_to_exclude_lbl" style="display:none"><label><?php echo esc_html( __( 'Post Types to Exclude', 'post-slider-carousel' ) ); ?> </label></h3>
											 
												<div class="inside">
													<table>
														<tr>
															<td>
																<ul id="cat_list_">
																<?php

																  $args = array(
																	  'public'   => true,

																  );

																	 $post_types = get_post_types( $args );

																	?>
																	<select id="postype" name="postype[]" style="min-width:200px" multiple="multiple">
																	  <option value=""><?php echo esc_html( __( 'Select', 'post-slider-carousel' ) ); ?></option>    
																		<?php foreach ( $post_types as $key => $p ) : ?>
																		  <option 
																			<?php
																			if ( in_array( $p, $postypeSelected ) ) :
																				?>
   selected="selected" <?php endif; ?>  value="<?php echo esc_html( $p ); ?>"><?php echo esc_html( $p ); ?></option>  
																		<?php endforeach; ?> 
																	?>
																  </select>
																</ul>
																
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>

												</div>
											</div> 
											<div class="stuffbox" id="namediv" style="width:100%;">
												 <h3><label><?php echo esc_html( __( 'Categories to exclude or include?', 'post-slider-carousel' ) ); ?> </label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select id="categories_include_exclude" name="categories_include_exclude" style="min-width:200px" >
																	<option value="1" 
																	<?php
																	if ( 1 == $settings['categories_include_exclude'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Include', 'post-slider-carousel' ) ); ?></option>    
																	<option value="0" 
																	<?php
																	if ( 0 == $settings['categories_include_exclude'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Exclude', 'post-slider-carousel' ) ); ?></option>    

																  ?>
																</select>
															</td>
														</tr>
													</table>
												</div>
												
												<h3 id="categories_to_include_lbl" style="display:none" ><label><?php echo esc_html( __( 'Categories To Include', 'post-slider-carousel' ) ); ?> </label></h3>
												<h3 id="categories_to_exclude_lbl" style="display:none"><label><?php echo esc_html( __( 'Categories To Exclude', 'post-slider-carousel' ) ); ?> </label></h3>
											   
												<div class="inside">
													<table>
														<tr>
															<td>
																<ul id="cat_list">
																 <?php

																	foreach ( $post_types as $pst ) {

																			   $taxonomies = get_object_taxonomies( $pst );

																		if ( false != $taxonomies && null != $taxonomies && count( $taxonomies ) > 0 ) {

																			echo "<b style='margin-top:10px'>" . esc_html( ucfirst( $pst ) ) . '</b><br/>';
																			foreach ( $taxonomies as $tx ) {

																				$tx_ob = get_taxonomy( $tx );
																				if ( $tx_ob->public ) {
																					// if(strpos($tx, 'cat')!==false or strpos($tx, 'category')!==false){
																					foreach ( get_terms(
																																												array(
																																														'taxonomy'=>$tx,
																							'hide_empty' => 0,
																							'parent' => 0,
																						)
																					) as $each ) {
																						
																																											  $default_attribs = array(
																																												'id' => array(),
																																												'class' => array(),
																																												'title' => array(),
																																												'style' => array(),
																																												'data' => array(),
																																												'data-mce-id' => array(),
																																												'data-mce-style' => array(),
																																												'data-mce-bogus' => array(),
																																											  );

																																											  $allowed_tags = array(
																																												'div'           => $default_attribs,
																																												'span'          => $default_attribs,
																																												'p'             => $default_attribs,
																																												'a'             => array_merge( $default_attribs, array(
																																													'href' => array(),
																																													'target' => array( '_blank', '_top' ),
																																												) ),
																																												'u'             =>  $default_attribs,
																																												'i'             =>  $default_attribs,
																																												'q'             =>  $default_attribs,
																																												'ul'            => $default_attribs,
																																												'ol'            => $default_attribs,
																																												'li'            => $default_attribs,
																																												'<br>'            => $default_attribs,
																																												'hr'            => $default_attribs,
																																												'strong'        => $default_attribs,
																																												'blockquote'    => $default_attribs,
																																												'del'           => $default_attribs,
																																												'strike'        => $default_attribs,
																																												'em'            => $default_attribs,
																																												'code'          => $default_attribs,
																																												'input'          => array( 'type'=>'checkbox', 'name'=>'post_category[]', 'value'=>'*', 'checked'=>'*' ),
																																												'br'          => $default_attribs,
																																												'b'          => array( 'style'=>'margin-top:10px' ),
																																											  );


																																											   
																																											  echo wp_kses( psc_my_Categ_tree( $each->term_id, $post_categorySelected, $each->taxonomy, '', true ), $allowed_tags );
																					}
																					// }
																				}
																			}
																		}
																	}
																	 // echo wp_category_checklist(0,0,$post_categorySelected,false,null,false) ;
																	?>
																</ul>
																
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>

												</div>
											</div> 
											
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Posts To Exclude', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<input id="post_exclude" value="<?php echo esc_attr( $settings['post_exclude'] ); ?>"  size="30" name="post_exclude" value="" type="text">
																
																<div></div>
															</td>
														</tr>
													</table>
													 <?php echo esc_html( __( 'comma separated post id\'s to exclude. ', 'post-slider-carousel' ) ); ?>   
													<div style="clear:both"></div>

												</div>
											</div> 
											<div class="stuffbox" id="namediv" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Sort By', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select name="sort_by" id="sort_by">
																	<option value="date" 
																	<?php
																	if ( 'date' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?> ><?php echo esc_html( __( 'Date', 'post-slider-carousel' ) ); ?></option>
																	<option value="ID" 
																	<?php
																	if ( 'ID' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>   ><?php echo esc_html( __( 'ID', 'post-slider-carousel' ) ); ?></option>
																	<option value="author" 
																	<?php
																	if ( 'author' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Author', 'post-slider-carousel' ) ); ?></option>
																	<option value="title" 
																	<?php
																	if ( 'title' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Title', 'post-slider-carousel' ) ); ?></option>
																	<option value="name" 
																	<?php
																	if ( 'name' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Name', 'post-slider-carousel' ) ); ?></option>
																	<option value="rand" 
																	<?php
																	if ( 'rand' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Random', 'post-slider-carousel' ) ); ?></option>
																	<option value="menu_order" 
																	<?php
																	if ( 'menu_order' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Menu order', 'post-slider-carousel' ) ); ?></option>
																	<option value="comment_count" 
																	<?php
																	if ( 'comment_count' == $settings['sort_by'] ) :
																		?>
  selected="selected" <?php endif; ?>><?php echo esc_html( __( 'Comment count', 'post-slider-carousel' ) ); ?></option>
																  </select>
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="namediv" style="width:100%;">
												<h3><label><?php echo esc_html( __( 'Sort Direction', 'post-slider-carousel' ) ); ?></label></h3>
												<div class="inside">
													<table>
														<tr>
															<td>
																<select name="sort_direction" id="sort_direction">
																<option value="asc" 
																<?php
																if ( '1' == $settings['sort_direction'] ) :
																	?>
  selected="selected" <?php endif; ?> ><?php echo esc_html( __( 'Ascending', 'post-slider-carousel' ) ); ?></option>
																<option value="desc" 
																<?php
																if ( '2' == $settings['sort_direction'] ) :
																	?>
  selected="selected" <?php endif; ?> ><?php echo esc_html( __( 'Descending', 'post-slider-carousel' ) ); ?></option>
															  </select>
																<div style="clear:both"></div>
																<div></div>
															</td>
														</tr>
													</table>

													<div style="clear:both"></div>
												</div>
											</div>
											<div class="stuffbox" id="Show_Pager_div" style="width:100%;">
											<h3><label><?php echo esc_html( __( 'Show Pager ?', 'post-slider-carousel' ) ); ?></label></h3>
											<div class="inside">
												<table>
													<tr>
														<td>
															<input style="width:20px;" type='radio' 
															<?php
															if ( 1 == $settings['show_pager'] ) {
																echo "checked='checked'";
															}
															?>
														  name='show_pager' value='1' ><?php echo esc_html( __( 'Yes', 'post-slider-carousel' ) ); ?> &nbsp;<input style="width:20px;" type='radio' name='show_pager' 
														<?php
														if ( 0 == $settings['show_pager'] ) {
															echo "checked='checked'";
														}
														?>
														 value='0' ><?php echo esc_html( __( 'No', 'post-slider-carousel' ) ); ?>
															<div style="clear:both"></div>
															<div></div>
														</td>
													</tr>
												</table>
												<div style="clear:both"></div>
											</div>
										</div>
										</fieldset>  
										 
										 
										<?php wp_nonce_field( 'action_settings_add_edit', 'add_edit_nonce' ); ?>       
										<input type="submit"  name="btnsave" id="btnsave" value="<?php echo esc_html( __( 'Save Changes', 'post-slider-carousel' ) ); ?>" class="button-primary">

									</form> 
									<script type="text/javascript">

										
										jQuery(document).ready(function() {
										//jQuery('input[type=radio][name=is_continues]').trigger("change")    
										jQuery("#scrollersettiings").validate({
										rules: {
										cols: {
												required:true,
												number:true
											  
										},
										cols1024: {
												required:true,
												number:true
											  
										},
										cols800: {
												required:true,
												number:true
											  
										},
										cols640: {
												required:true,
												number:true
											  
										},
										heading_cl:{
												required:true,
												maxlength:7  
											},
										post_meta_cl:{
												required:true,
												maxlength:7  
											},
										content_cl:{
												required:true,
												maxlength:7  
											},
										read_more_cl:{
												required:true,
												maxlength:7  
											},
										read_more_hcl:{
												required:true,
												maxlength:7  
											},
										readMore_text: {
												required:true


										},     
										max_post_retrive: {
												 required:true,
												 number:true
											   
										}     
										 
									   
									},
												errorClass: "image_error",
												errorPlacement: function(error, element) {
												error.appendTo(element.next().next());
												}


										});

												jQuery('#heading_cl').wpColorPicker();
												jQuery('#post_meta_cl').wpColorPicker();
												jQuery('#content_cl').wpColorPicker();
												jQuery('#read_more_cl').wpColorPicker();
												jQuery('#read_more_hcl').wpColorPicker();
												
												
											jQuery( "#postype_include_exclude" ).change(function() {


											   if(jQuery( "#postype_include_exclude" ).val().toString()=="1"){

													 jQuery("#post_type_to_exclude_lbl").hide();
													 jQuery("#post_type_to_include_lbl").show();

											   }
											   else{

													  jQuery("#post_type_to_exclude_lbl").show();
													  jQuery("#post_type_to_include_lbl").hide();


											   }

										   });

										   jQuery( "#categories_include_exclude" ).change(function() {


											   if(jQuery( "#categories_include_exclude" ).val().toString()=="1"){

													 jQuery("#categories_to_exclude_lbl").hide();
													 jQuery("#categories_to_include_lbl").show();

											   }
											   else{

													  jQuery("#categories_to_exclude_lbl").show();
													  jQuery("#categories_to_include_lbl").hide();


											   }

										   });

										  jQuery( "#postype_include_exclude" ).trigger('change'); 
										  jQuery( "#categories_include_exclude" ).trigger('change'); 
									   
								   });
							</script> 
									

					  </div>
				  </div>
			  </div>  
		  </div>      
	  </div>
	  <div id="postbox-container-1" class="postbox-container" > 

		<div class="postbox"> 
			  <h3 class="hndle"><span></span><?php echo esc_html( __( 'Access All Themes In One Price', 'post-slider-carousel' ) ); ?></h3> 
			  <div class="inside">
				  <center><a href="http://www.elegantthemes.com/affiliates/idevaffiliate.php?id=11715_0_1_10" target="_blank"><img border="0" src="<?php echo esc_url( plugins_url( 'images/300x250.gif', __FILE__ ) ); ?>" width="250" height="250"></a></center>

				  <div style="margin:10px 5px">

				  </div>
			  </div></div>
			<div class="postbox"> 
				<h3 class="hndle"><span></span><?php echo esc_html( __( 'Google For Business Coupon', 'post-slider-carousel' ) ); ?></h3> 
					<div class="inside">
						<center><a href="https://goo.gl/OJBuHT" target="_blank">
								<img src="<?php echo esc_url( plugins_url( 'images/g-suite-promo-code-4.png', __FILE__ ) ); ?>" width="250" height="250" border="0">
							</a></center>
						<div style="margin:10px 5px">
						</div>
					</div>

				</div>

	  </div>      
	 <div class="clear"></div>
  </div>  
 </div> 
	<?php
}
function psc_post_slider_carousel_preview_func() {

	if ( ! current_user_can( 'psc_preview_post_slider' ) ) {

		wp_die( esc_html( __( 'Access Denied', 'post-slider-carousel' ) ) );

	}

		   $settings = get_option( 'psc_slider_settings' );

		   $rand_Numb = uniqid( 'psc_thumnail_slider' );
		   $rand_Num_td = uniqid( 'psc_divSliderMain' );
		   $rand_var_name = uniqid( 'rand_' );
		   $uploads = wp_upload_dir();
		   $baseDir = $uploads ['basedir'];
		   $baseDir = str_replace( '\\', '/', $baseDir );

		   $baseurl = $uploads['baseurl'];
		   $baseurl .= '/psc_post_slider_carousel/';
		   $pathToImagesFolder = $baseDir . '/psc_post_slider_carousel';
		   $upload_dir_n = $uploads['basedir'];

	?>
		  
		  
		<style type='text/css' >
		 #<?php echo esc_html( $rand_Num_td ); ?> .bx-wrapper .bx-viewport {
			 background: none repeat scroll 0 0 <?php echo esc_html( $settings['scollerBackground'] ); ?> !important;
			 border: 0px none !important;
			 box-shadow: 0 0 0 0 !important;
			 /*padding:<?php echo esc_html( $settings['imageMargin'] ); ?>px !important;*/
		   }
		 </style>
	   <?php
			$wpcurrentdir = __DIR__;
			$wpcurrentdir = str_replace( '\\', '/', $wpcurrentdir );
		?>
	   <div style="width: 100%;">  
			<div style="float:left;width:100%;">
				<div class="wrap">
						<h2><?php echo esc_html( __( 'Slider Preview', 'post-slider-carousel' ) ); ?></h2>
			   
				<?php if ( is_array( $settings ) ) { ?>
				<div id="poststuff">
				  <div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<?php echo do_shortcode( '[psc_print_post_slider_carousel]' ); ?>
					</div>
			  </div>
			</div>  
				<?php } ?>
		 </div>      
	</div>                                      
	<div class="clear"></div>
	</div>
	<?php if ( is_array( $settings ) ) { ?>
	
		<h3><?php echo esc_html( __( 'To print this slider into WordPress Post/Page use below code', 'post-slider-carousel' ) ); ?></h3>
		<input type="text" value='[psc_print_post_slider_carousel] ' style="width: 400px;height: 30px" onclick="this.focus();this.select()" />
		<div class="clear"></div>
		<h3><?php echo esc_html( __( 'To print this slider into WordPress theme/template PHP files use below code', 'post-slider-carousel' ) ); ?></h3>
		<?php
			$shortcode = '[psc_print_post_slider_carousel]';
		?>
		<input type="text" value="&lt;?php echo do_shortcode('<?php echo esc_html( $shortcode ); ?>'); ?&gt;" style="width: 400px;height: 30px" onclick="this.focus();this.select()" />
	   
	<?php } ?>
	<div class="clear"></div>
	<?php
}

function psc_post_grid_preview_func() {

	if ( ! current_user_can( 'psc_preview_post_grid' ) ) {

		wp_die( esc_html( __( 'Access Denied', 'post-slider-carousel' ) ) );

	}

	$paged = isset( $_GET['paged'] ) ? trim( intval( $_GET['paged'] ) ) : 1;
	$settings = get_option( 'psc_slider_settings' );

	?>
	  <div style="width: 100%;">  
			<div style="float:left;width:100%;">
				<div class="wrap">
				  <h2><?php echo esc_html( __( 'Post Grid Preview', 'post-slider-carousel' ) ); ?></h2>
			   
				<div id="poststuff">
				  <div id="post-body" class="metabox-holder">
					  <div id="post-body-content">
											  <?php
												$default_attribs = array(
												'id' => array(),
												'class' => array(),
												'title' => array(),
												'style' => array(),
												'data' => array(),
												'data-mce-id' => array(),
												'data-mce-style' => array(),
												'data-mce-bogus' => array(),
												);

												$allowed_tags = array(
												'div'           => $default_attribs,
												'span'          => $default_attribs,
												'p'             => $default_attribs,
												'a'             => array_merge( $default_attribs, array(
												'href' => array(),
												'target' => array( '_blank', '_top' ),
												) ),
												'u'             =>  $default_attribs,
												'i'             =>  $default_attribs,
												'q'             =>  $default_attribs,
												'ul'            => $default_attribs,
												'ol'            => $default_attribs,
												'li'            => $default_attribs,
												'<br>'            => $default_attribs,
												'hr'            => $default_attribs,
												'strong'        => $default_attribs,
												'blockquote'    => $default_attribs,
												'del'           => $default_attribs,
												'strike'        => $default_attribs,
												'em'            => $default_attribs,
												'code'          => $default_attribs,
												'br'          => $default_attribs,
												'b'          => array( 'style'=>'margin-top:10px' ),
												'script'          => array( 'type'=>'javascript' ),
												'style'          => array( 'type'=>'javascript' ),
												'img'          => array( 'src'=>'*' ),
												);
												?>
																														   
						<?php echo wp_kses( psc_print_post_grid_func( array( 'paged' => $paged ) ), $allowed_tags); ?>  
					  </div>
				  </div>
				</div>
			</div> 
		 </div> 
		   <?php if ( is_array( $settings ) ) { ?>
	
			<h3><?php echo esc_html( __( 'To print this post grid into WordPress Post/Page use below code', 'post-slider-carousel' ) ); ?></h3>
			<input type="text" value='[psc_print_post_grid]' style="width: 400px;height: 30px" onclick="this.focus();this.select()" />
			<div class="clear"></div>
			<h3><?php echo esc_html( __( 'To print this post grid into WordPress theme/template PHP files use below code', 'post-slider-carousel' ) ); ?></h3>
				<?php
				$shortcode = '[psc_print_post_grid]';
				?>
			<input type="text" value="&lt;?php echo do_shortcode('<?php echo esc_html( $shortcode ); ?>'); ?&gt;" style="width: 400px;height: 30px" onclick="this.focus();this.select()" />

		<?php } ?>
		<div class="clear"></div>
	  </div>               
	<?php
}

function psc_get_no_img_url( $imageheight, $imagewidth, $grid = false ) {

		$uploads = wp_upload_dir();
		$baseDir = $uploads['basedir'];
		$baseDir = str_replace( '\\', '/', $baseDir );
		$pathToImagesFolder = $baseDir . '/post-slider-carousel';

		$baseurl = $uploads['baseurl'];
		$baseurl .= '/psc_post_slider_carousel/';
		$pathToImagesFolder = $baseDir . '/psc_post_slider_carousel';
		$upload_dir_n = $uploads['basedir'];

	if ( false == $grid ) {
		$image = plugin_dir_path( __FILE__ ) . 'images/no-image-available.jpg';
		$image = str_replace( '\\', '/', $image );

		$extension = 'jpg';
		$filenamewithoutextension = 'no-image-available';
		$imagetoCheck = "$pathToImagesFolder/no-image-available_$imageheight_$imagewidth.$extension";
		$imagetoCheckSmall = "$pathToImagesFolder/no-image-available_$imageheight_$imagewidth." . strtolower( $extension );
	} else {
			$image = plugin_dir_path( __FILE__ ) . 'images/no-image-available-grid.jpg';
			$image = str_replace( '\\', '/', $image );

			$extension = 'jpg';
			$filenamewithoutextension = 'no-image-available-grid';
			$imagetoCheck = "$pathToImagesFolder/no-image-available-grid_$imageheight_$imagewidth.$extension";
			$imagetoCheckSmall = "$pathToImagesFolder/no-image-available-grid_$imageheight_$imagewidth." . strtolower( $extension );
	}
	if ( file_exists( $imagetoCheck ) ) {

		$outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . $extension;

	} else if ( file_exists( $imagetoCheckSmall ) ) {

				  $outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . strtolower( $extension );
	} else {

			$image = wp_get_image_editor( $image );
		if ( ! is_wp_error( $image ) ) {

			$image->resize( $imagewidth, $imageheight, true );
			$image->save( $imagetoCheck );
			// $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];

			if ( file_exists( $imagetoCheck ) ) {
				   $outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . $extension;
			} else if ( file_exists( $imagetoCheckSmall ) ) {
				  $outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . strtolower( $extension );
			}
		}
	}

	   return $outputimg;
}

function psc_print_post_slider_carousel_func( $atts ) {

	global $wpdb;
	$rand_Numb = uniqid( 'psc_thumnail_slider' );
	$rand_Num_td = uniqid( 'psc_divSliderMain' );
	$rand_var_name = uniqid( 'rand_' );
	$settings = get_option( 'psc_slider_settings' );

	$uploads = wp_upload_dir();
	$baseDir = $uploads ['basedir'];
	$baseDir = str_replace( '\\', '/', $baseDir );

	$baseurl = $uploads['baseurl'];
	$baseurl .= '/psc_post_slider_carousel/';
	$pathToImagesFolder = $baseDir . '/psc_post_slider_carousel';
	$upload_dir_n = $uploads['basedir'];

	wp_enqueue_style( 'p_s_c_bx' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'p_s_c_bx' );

	ob_start();
	?>
	  
		
		<style type='text/css' >
		 #<?php echo esc_html( $rand_Num_td ); ?> .bx-wrapper .bx-viewport {
			 background: none repeat scroll 0 0 <?php echo esc_html( $settings['scollerBackground'] ); ?> !important;
			 border: 0px none !important;
			 box-shadow: 0 0 0 0 !important;
			 /*padding:<?php echo esc_html( $settings['imageMargin'] ); ?>px !important;*/
		   }
		 </style><!-- psc_print_post_slider_carousel_func -->
		 <?php
			$wpcurrentdir = __DIR__;
			$wpcurrentdir = str_replace( '\\', '/', $wpcurrentdir );
			?>
	 <?php if ( is_array( $settings ) ) { ?>
				<div id="poststuff">
				  <div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						 <div style="clear: both;"></div>
						<?php $url = plugin_dir_url( __FILE__ ); ?>           
						
					   
							<div style="width: auto;postion:relative" id="<?php echo esc_attr( $rand_Num_td ); ?>">
							  <div id="<?php echo esc_attr( $rand_Numb ); ?>" class="post_slider_carousel" style="margin-top: 2px !important;display:none">
								  
						 <?php

							  global $wpdb;
							  $imageheight = $settings['imageheight'];
							  $imagewidth = $settings['imagewidth'];
							  $exs_post_types = $settings['postype'];
							  $exs_post_typesArr = explode( ',', $exs_post_types );
							  $postTypesTouse = array();
							if ( 0 == $settings['postype_include_exclude'] ) {

								  $args = array( 'public'   => true );
								  $post_types = get_post_types( $args );
								foreach ( $post_types as $pt ) {

									if ( ! in_array( $pt, $exs_post_typesArr ) ) {

										$postTypesTouse[] = $pt;
									}
								}
							} else {

								$postTypesTouse = $exs_post_typesArr;
							}

							  $wp_query_args = array();
							  $wp_query_args['post_type'] = $postTypesTouse;
							  $wp_query_args['post_status'] = array( 'publish', 'private' );
							  $wp_query_args['posts_per_page'] = $settings['max_post_retrive'];
							  $wp_query_args['orderby'] = $settings['sort_by'];
							if ( '2' == $settings['sort_direction'] ) {
								$wp_query_args['order'] = 'desc';
							} else if ( '1' == $settings['sort_direction'] ) {

								$wp_query_args['order'] = 'asc';
							}

							  $exs_posts = $settings['post_exclude'];
							if ( '' != trim( $exs_posts ) ) {

								$exs_postsArr = explode( ',', $exs_posts );
								if ( count( $exs_postsArr ) > 0 ) {

									$wp_query_args['post__not_in'] = $exs_postsArr;
								}
							}

							  $exs_categories = $settings['post_category'];
							if ( trim( $exs_categories ) != '' ) {

								$exs_catArr = explode( ',', $exs_categories );
								if ( 0 == $settings['categories_include_exclude'] ) {

									if ( count( $exs_catArr ) > 0 ) {

										// $wp_query_args['category__not_in']=$exs_catArr;

										if ( count( $exs_catArr ) > 0 ) {

											$wp_query_args['tax_query'] = array(
												'relation' => 'AND',
											);
											foreach ( $exs_catArr as $cat ) {

												  $term = get_term( $cat );

												  $wp_query_args['tax_query'][] = array(
													  'taxonomy' => $term->taxonomy,
													  'field' => 'id',
													  'terms' => array( $cat ),
													  'operator' => 'NOT IN',

												  );

											}
										}
									}
								} elseif ( count( $exs_catArr ) > 0 ) {


										$wp_query_args['tax_query'] = array(
											'relation' => 'OR',
										);
										foreach ( $exs_catArr as $cat ) {

											   $term = get_term( $cat );

											   $wp_query_args['tax_query'][] = array(
												   'taxonomy' => $term->taxonomy,
												   'field' => 'id',
												   'terms' => array( $cat ),
												   'operator' => 'IN',

											   );

										}
								}
							}

							 $my_query = new WP_Query( $wp_query_args );

							if ( $my_query->have_posts() ) {

								while ( $my_query->have_posts() ) {

									$my_query->the_post();
									if ( has_post_thumbnail() ) {

										// $img=wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array($imagewidth,$imageheight),true);
										// $imgsrc = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), "Full");
										$postThumbnailID = get_post_thumbnail_id( get_the_ID() );
										$photoMeta = wp_get_attachment_metadata( $postThumbnailID );

										if ( is_array( $photoMeta ) && isset( $photoMeta['file'] ) ) {

											 $fileName = $photoMeta['file'];
											 $fname = $upload_dir_n . '/' . $fileName;
											 $image = str_replace( '\\', '/', $fname );

											 $imageNameArr = pathinfo( $image );
											 $imagename = $imageNameArr['basename'];
											 $filenamewithoutextension = $imageNameArr['filename'];
											 $extension = $imageNameArr['extension'];
											 $imagetoCheck = $pathToImagesFolder . '/' . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . $extension;
											 $imagetoCheckSmall = $pathToImagesFolder . '/' . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . strtolower( $extension );

											if ( file_exists( $imagetoCheck ) ) {

												   $outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . $extension;
											} else if ( file_exists( $imagetoCheckSmall ) ) {

												$outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . strtolower( $extension );
											} else {

													$image = wp_get_image_editor( $image );

												if ( ! is_wp_error( $image ) ) {

													  $image->resize( $imagewidth, $imageheight, true );
													  $image->save( $imagetoCheck );
													  // $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];

													if ( file_exists( $imagetoCheck ) ) {
														$outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . $extension;
													} else if ( file_exists( $imagetoCheckSmall ) ) {
															  $outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . strtolower( $extension );
													}
												} else {

															$outputimg = psc_get_no_img_url( $imageheight, $imagewidth );
												}
											}
										} else {

											$outputimg = psc_get_no_img_url( $imageheight, $imagewidth );
										}
									} else {

										 $outputimg = psc_get_no_img_url( $imageheight, $imagewidth );

									}

									$rowTitle = get_the_title();
									$rowTitle = str_replace( "'", '’', $rowTitle );
									$rowTitle = str_replace( '"', '”', $rowTitle );

									?>
										  
								  <div class="bx_pst_slider">   
									  <?php if ( true == $settings['linkimage'] ) { ?>                                                                                                                                                                                                                                                                                     
										<a data-post_id="<?php echo esc_attr( get_the_ID() ); ?>" 
																	<?php
																	if ( true == $settings['open_link_in'] ) :
																		?>
											target="_blank" <?php endif; ?>  href="<?php echo esc_attr( esc_url( get_permalink() ) ); ?>" title="<?php echo esc_attr( $rowTitle ); ?>" ><img src="<?php echo esc_attr( esc_url( $outputimg ) ); ?>" alt="<?php echo esc_attr( $rowTitle ); ?>" title="<?php echo esc_attr( $rowTitle ); ?>"   /></a>
									  <?php } else { ?>
										<img  src="<?php echo esc_attr( esc_url( $outputimg ) ); ?>" alt="<?php echo esc_attr( $rowTitle ); ?>" title="<?php echo esc_attr( $rowTitle ); ?>"   />
									  <?php } ?> 
								   </div>
							   
						   <?php } ?>   
								<?php
							}
							wp_reset_query();
							?>
						 
					</div>
						</div>
					<script>
				
				   <?php $intval = uniqid( 'interval_' ); ?>
			   
					var <?php echo esc_html( $intval ); ?> = setInterval(function() {
					var psc_slider='';    
					if(document.readyState === 'complete') {

					   clearInterval(<?php echo esc_html( $intval ); ?>);
					   
							
						   jQuery("#<?php echo esc_html( $rand_Numb ); ?>").show();
							var <?php echo esc_html( $rand_var_name ); ?>=psc_slider=jQuery('#<?php echo esc_html( $rand_Num_td ); ?>').html();   
							jQuery('#<?php echo esc_html( $rand_Numb ); ?>').bxSlider({
								<?php if ( 1 == $settings['min_post'] && 1 == $settings['max_post'] ) : ?>
								  mode:'fade',
								<?php endif; ?>
								  slideWidth: <?php echo esc_html( $settings['imagewidth'] ); ?>,
								   minSlides: <?php echo esc_html( $settings['min_post'] ); ?>,
								   maxSlides: <?php echo esc_html( $settings['max_post'] ); ?>,
								   moveSlides: <?php echo esc_html( $settings['scroll'] ); ?>,
								   slideMargin:<?php echo esc_html( $settings['imageMargin'] ); ?>,  
								   speed:<?php echo esc_html( $settings['speed'] ); ?>,
								   pause:<?php echo esc_html( $settings['pause'] ); ?>,
								   adaptiveHeight:false,
								   preventDefaultSwipeY: false,
								   <?php if ( $settings['pauseonmouseover'] && ( 1 == $settings['auto'] || 2 == $settings['auto'] ) ) { ?>
									 autoHover: true,
										<?php
								   } elseif ( 1 == $settings['auto'] || 2 == $settings['auto'] ) {
										?>
											   
									 autoHover:false,
																		  <?php

								   }
									?>
								   <?php if ( 1 == $settings['auto'] ) : ?>
									controls:false,
								   <?php else : ?>
									 controls:true,
								   <?php endif; ?>
								   pager:false,
								   useCSS:false,
								   <?php if ( 1 == $settings['auto'] || 2 == $settings['auto'] ) : ?>
									autoStart:true,
									autoDelay:200,
									auto:true,       
								   <?php endif; ?>
								   <?php if ( $settings['circular'] ) : ?> 
									infiniteLoop: true,
								   <?php else : ?>
									 infiniteLoop: false,
								   <?php endif; ?>
								   <?php if ( $settings['show_caption'] ) : ?>
									 captions:true,  
								   <?php else : ?>
									 captions:false,
								   <?php endif; ?>
								   <?php if ( $settings['show_pager'] ) : ?>
									 pager:true,
								   <?php else : ?>
									 pager:false,
								   <?php endif; ?>
									 onSlideBefore: function(slideElement){
														
										jQuery(slideElement).find('img').each(function(index, elm) {

												if(!elm.complete || elm.naturalWidth === 0){

												   var toload='';
												   var toloadval='';
												   jQuery.each(elm.attributes, function(i, attrib){

													   var value = attrib.value;
													   var aname=attrib.name;

													   var pattern = /^((http|https):\/\/)/;

													   if(pattern.test(value) && aname!='src' && aname.indexOf('data-html5_vurl')==-1) {

														   toload=aname;
														   toloadval=value;
														   }
													   // do your magic :-)
												   });

												   vsrc= jQuery(elm).attr("src");
												   jQuery(elm).removeAttr("src");
												   dsrc= jQuery(elm).attr("data-src");
												   lsrc= jQuery(elm).attr("data-lazy-src");

												   if(dsrc!== undefined && dsrc!='' && dsrc!=vsrc){
															jQuery(elm).attr("src",dsrc);
													   }
													   else if(lsrc!== undefined && lsrc!=vsrc){

															jQuery(elm).attr("src",lsrc);
													   }
														else if(toload!='' && toload!='srcset' && toloadval!='' && toloadval!=vsrc){

														   $(elm).attr("src",toloadval);


														   } 
													   else{

															jQuery(elm).attr("src",vsrc);

													   }   

												   elm= jQuery(elm)[0];      
												   if(!elm.complete && elm.naturalHeight == 0){

														jQuery(elm).removeAttr('loading');
														jQuery(elm).removeAttr('data-lazy-type');


														jQuery(elm).removeClass('lazy');

														jQuery(elm).removeClass('lazyLoad');
														jQuery(elm).removeClass('lazy-loaded');
														jQuery(elm).removeClass('jetpack-lazy-image');
														jQuery(elm).removeClass('jetpack-lazy-image--handled');
														jQuery(elm).removeClass('lazy-hidden');

											   }


										   }

										});

								  },   
								onSliderLoad: function(){


								}


					 });

							 
						  
						  
							
				   }    
				}, 100);         
					
					
					  window.addEventListener('load', function() {


									setTimeout(function(){ 

											if(jQuery("#<?php echo esc_html( $rand_Numb ); ?>").find('.bx-loading').length>0){

													jQuery("#<?php echo esc_html( $rand_Numb ); ?>").find('img').each(function(index, elm) {

															 if(!elm.complete || elm.naturalWidth === 0){

																var toload='';
																var toloadval='';
																jQuery.each(this.attributes, function(i, attrib){

																		var value = attrib.value;
																		var aname=attrib.name;

																		var pattern = /^((http|https):\/\/)/;

																		if(pattern.test(value) && aname!='src') {

																				toload=aname;
																				toloadval=value;
																		 }
																		// do your magic :-)
																 });

																		vsrc=jQuery(elm).attr("src");
																		jQuery(elm).removeAttr("src");
																		dsrc=jQuery(elm).attr("data-src");
																		lsrc=jQuery(elm).attr("data-lazy-src");


																		   if(dsrc!== undefined && dsrc!='' && dsrc!=vsrc){
																										 jQuery(elm).attr("src",dsrc);
																				}
																				else if(lsrc!== undefined && lsrc!=vsrc){

																								 jQuery(elm).attr("src",lsrc);
																				}
																				else if(toload!='' && toload!='srcset' && toloadval!='' && toloadval!=vsrc){

																						jQuery(elm).removeAttr(toload);
																						jQuery(elm).attr("src",toloadval);


																					} 
																				else{

																								jQuery(elm).attr("src",vsrc);

																		   }   

																		elm=jQuery(elm)[0];      
																		 if(!elm.complete && elm.naturalHeight == 0){

																						 jQuery(elm).removeAttr('loading');
																						 jQuery(elm).removeAttr('data-lazy-type');


																						 jQuery(elm).removeClass('lazy');

																						 jQuery(elm).removeClass('lazyLoad');
																						 jQuery(elm).removeClass('lazy-loaded');
																						 jQuery(elm).removeClass('jetpack-lazy-image');
																						 jQuery(elm).removeClass('jetpack-lazy-image--handled');
																						 jQuery(elm).removeClass('lazy-hidden');

																		}
															 }

														}).promise().done( function(){ 

																jQuery("#<?php echo esc_html( $rand_Num_td ); ?>").find('.bx-loading').remove();
														} );

												}


									   }, 6000);

							});
					 </script>         
						
					</div>
			  </div>
			</div>  
	 <?php } ?><!-- end psc_print_post_slider_carousel_func -->
	 <?php
		$output = ob_get_clean();
		return $output;
}

function psc_get_excerpt( $post_id ) {

	   $the_post = get_post( $post_id ); // Gets post ID
	   $the_excerpt = $the_post->post_content; // Gets post_content to be used as a basis for the excerpt
	if ( get_the_excerpt( $post_id ) != '' ) {
		$the_excerpt = get_the_excerpt( $post_id );
	}
	   $excerpt_length = 20;// Sets excerpt length by word count
	   $the_excerpt = strip_tags( strip_shortcodes( $the_excerpt ) ); // Strips tags and images
	   $words = explode( ' ', $the_excerpt, $excerpt_length + 1 );
	if ( count( $words ) > $excerpt_length ) :
		array_pop( $words );
		array_push( $words, '' );
		$the_excerpt = implode( ' ', $words );
		endif;

	   $the_excerpt = strip_shortcodes( $the_excerpt );
	   $the_excerpt = preg_replace( '/\[(.*?)\]/i', '', $the_excerpt );
	   $the_excerpt = strip_tags( $the_excerpt );
	if ( '' !== $the_excerpt ) {

		$the_excerpt .= '...';
	}
	   return $the_excerpt;
}
function psc_print_post_grid_func( $atts ) {

	global $wpdb;
	$rand_Numb = uniqid( 'psc_grid' );
	$settings = get_option( 'psc_pgrid_settings' );

	$uploads = wp_upload_dir();
	$baseDir = $uploads ['basedir'];
	$baseDir = str_replace( '\\', '/', $baseDir );

	$baseurl = $uploads['baseurl'];
	$baseurl .= '/psc_post_slider_carousel/';
	$pathToImagesFolder = $baseDir . '/psc_post_slider_carousel';
	$upload_dir_n = $uploads['basedir'];

	wp_enqueue_style( 'psc_grid' );
	wp_enqueue_style( 'font-awesome.min' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'psc_grid_min' );

	ob_start();
	?>
	<!-- psc_print_post_grid_func --><style>
	 .list-groupupdate-item-heading{color:<?php echo esc_html( $settings['heading_cl'] ); ?> } 
	 .list-groupupdate-item-heading a{color:<?php echo esc_html( $settings['heading_cl'] ); ?>;border:none;box-shadow:none } 
	 .list-groupupdate-item-heading:hover{color:<?php echo esc_html( $settings['heading_cl'] ); ?> } 
	 .list-groupupdate-item-heading a:hover{color:<?php echo esc_html( $settings['heading_cl'] ); ?>; border:none;box-shadow:none } 
	 .pmeta{color:<?php echo esc_html( $settings['post_meta_cl'] ); ?>}
	 .pmeta li span{color:<?php echo esc_html( $settings['post_meta_cl'] ); ?>}
	 .pmetaicon{color:<?php echo esc_html( $settings['post_meta_cl'] ); ?>}
	 .list-groupupdate-item-text{color:<?php echo esc_html( $settings['content_cl'] ); ?>}
	 .rmore{color:<?php echo esc_html( $settings['read_more_cl'] ); ?>;border:none;box-shadow:none !important  }
	 .rmore:hover{color:<?php echo esc_html( $settings['read_more_hcl'] ); ?>;border:none;box-shadow:none !important }
	</style>
	<?php
			$wpcurrentdir = __DIR__;
			$wpcurrentdir = str_replace( '\\', '/', $wpcurrentdir );
	?>
	 <?php if ( is_array( $settings ) ) : ?>
	   
	   <div class="max-width" >
	
		   <div id="container" class="main_grid_div" style="display:none">
	  

			<?php
						global $wpdb;
						$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
						$exs_post_types = $settings['postype'];
						$exs_post_typesArr = explode( ',', $exs_post_types );
						$postTypesTouse = array();
			if ( 0 == $settings['postype_include_exclude'] ) {

				$args = array( 'public'   => true );
				$post_types = get_post_types( $args );
				foreach ( $post_types as $pt ) {

					if ( ! in_array( $pt, $exs_post_typesArr ) ) {

						$postTypesTouse[] = $pt;
					}
				}
			} else {

				$postTypesTouse = $exs_post_typesArr;
			}

						$wp_query_args = array();
						$wp_query_args['post_type'] = $postTypesTouse;
						$wp_query_args['posts_per_page'] = $settings['max_post_retrive'];
						$wp_query_args['post_status'] = array( 'publish', 'private' );
			if ( -1 != $settings['max_post_retrive'] && 1 == $settings['show_pager'] ) {

				$wp_query_args['paged'] = ( isset( $atts['paged'] ) && '' != $atts['paged'] ) ? $atts['paged'] : $paged;
			}

						$wp_query_args['orderby'] = $settings['sort_by'];
			if ( '2' == $settings['sort_direction'] ) {
				$wp_query_args['order'] = 'desc';
			} else if ( '1' == $settings['sort_direction'] ) {

				$wp_query_args['order'] = 'asc';
			}

						$exs_posts = $settings['post_exclude'];
			if ( '' != trim( $exs_posts ) ) {

				$exs_postsArr = explode( ',', $exs_posts );
				if ( count( $exs_postsArr ) > 0 ) {

					$wp_query_args['post__not_in'] = $exs_postsArr;
				}
			}

					   $exs_categories = $settings['post_category'];
			if ( '' != trim( $exs_categories ) ) {

					  $exs_catArr = explode( ',', $exs_categories );
				if ( 0 == $settings['categories_include_exclude'] ) {

					if ( count( $exs_catArr ) > 0 ) {

						// $wp_query_args['category__not_in']=$exs_catArr;

						if ( count( $exs_catArr ) > 0 ) {

										  $wp_query_args['tax_query'] = array(
											  'relation' => 'AND',
										  );
										  foreach ( $exs_catArr as $cat ) {

												  $term = get_term( $cat );

												  $wp_query_args['tax_query'][] = array(
													  'taxonomy' => $term->taxonomy,
													  'field' => 'id',
													  'terms' => array( $cat ),
													  'operator' => 'NOT IN',

												  );

										  }
						}
					}
				} elseif ( count( $exs_catArr ) > 0 ) {


						$wp_query_args['tax_query'] = array(
							'relation' => 'OR',
						);
						foreach ( $exs_catArr as $cat ) {

										$term = get_term( $cat );

										$wp_query_args['tax_query'][] = array(
											'taxonomy' => $term->taxonomy,
											'field' => 'id',
											'terms' => array( $cat ),
											'operator' => 'IN',

										);

						}
				}
			}

					   $my_query = new WP_Query( $wp_query_args );

					   $imageheight = 218;
					   $imagewidth = 388;
			if ( $my_query->have_posts() ) {

				while ( $my_query->have_posts() ) {

							  $my_query->the_post();
					if ( has_post_thumbnail() ) {

						// $img=wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array($imagewidth,$imageheight),true);
						// $imgsrc = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), "Full");
						$postThumbnailID = get_post_thumbnail_id( get_the_ID() );
						$photoMeta = wp_get_attachment_metadata( $postThumbnailID );

						if ( is_array( $photoMeta ) && isset( $photoMeta['file'] ) ) {

									  $fileName = $photoMeta['file'];
									  $fname = $upload_dir_n . '/' . $fileName;
									  $image = str_replace( '\\', '/', $fname );

									  $imageNameArr = pathinfo( $image );
									  $imagename = $imageNameArr['basename'];
									  $filenamewithoutextension = $imageNameArr['filename'];
									  $extension = $imageNameArr['extension'];
									  $imagetoCheck = $pathToImagesFolder . '/' . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . $extension;
									  $imagetoCheckSmall = $pathToImagesFolder . '/' . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . strtolower( $extension );

							if ( file_exists( $imagetoCheck ) ) {

								   $outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . $extension;
							} else if ( file_exists( $imagetoCheckSmall ) ) {

								$outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . strtolower( $extension );
							} else {

								 $image = wp_get_image_editor( $image );

								if ( ! is_wp_error( $image ) ) {

											  $image->resize( $imagewidth, $imageheight, true );
											  $image->save( $imagetoCheck );
											  // $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];

									if ( file_exists( $imagetoCheck ) ) {
										$outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . $extension;
									} else if ( file_exists( $imagetoCheckSmall ) ) {
										$outputimg = $baseurl . $filenamewithoutextension . '_' . $imageheight . '_' . $imagewidth . '.' . strtolower( $extension );
									}
								} else {

													  $outputimg = psc_get_no_img_url( $imageheight, $imagewidth, true );
								}
							}
						} else {

									  $outputimg = psc_get_no_img_url( $imageheight, $imagewidth, true );
						}
					} else {

						$outputimg = psc_get_no_img_url( $imageheight, $imagewidth, true );

					}

							  $rowTitle = get_the_title();
							  $rowTitle = str_replace( "'", '’', $rowTitle );
							  $rowTitle = str_replace( '"', '”', $rowTitle );

							  $excerpt = psc_get_excerpt( get_the_ID() );
							  $permalink = get_the_permalink( get_the_ID() );

					?>
									
							  <div class="item___ <?php echo esc_html( $rand_Numb ); ?>">
								  <img class="group list-group-image" src="<?php echo esc_attr( esc_url( $outputimg ) ); ?>" width="640" height="480"  alt=""/>
						  
								<div class="group inner list-groupupdate-item-heading"><a href="<?php echo esc_attr( esc_url( $permalink ) ); ?>" ><?php echo esc_html( $rowTitle ); ?></a></div>
								<div class="caption">

									<ul class="pmeta">
										<li class="time"> 

											<span><i class="fa fa-calendar pmetaicon"></i>  <?php echo esc_html( get_the_date() ); ?>&nbsp;&nbsp;</span>
											<span><i class="fa fa-user pmetaicon"></i> <?php echo esc_html( get_the_author() ); ?>&nbsp;&nbsp;</span>
											<span><i class="fa fa-comment pmetaicon"></i>&nbsp; <?php esc_html( comments_number( '0', '0', '%' ) ); ?></span>
										</li>
									</ul>
									<p class="group inner list-groupupdate-item-text" >
										<?php echo esc_html( ( trim( $excerpt ) != '' ) ? $excerpt : '&nbsp;' ); ?>
									</p>


							   </div>
								<div class="entry-footer-" >
								   
									<div class="colupdate-md-12 colupdate-lg-12 colupdate-xs-12">
										<a href="<?php echo esc_attr( esc_url( $permalink ) ); ?>" class="rmore" target="_self" >
											<?php echo esc_html( $settings['readMore_text'] ); ?>&nbsp;<i class="fa fa-angle-double-right"></i></a>
									</div>
							   </div>

						  </div>

					  <?php } ?>   
				<?php
			}

			?>
				  
			  
	 

	</div>
			<?php

			if ( -1 != $settings['max_post_retrive'] && 1 == $settings['show_pager'] ) {
				$pagination_args = array(
					'base' => @add_query_arg( 'paged', '%#%' ),
					'format' => '',
					'total' => ceil( $my_query->max_num_pages ),
					'current' => max( 1, ( isset( $atts['paged'] ) && '' != $atts['paged'] ) ? $atts['paged'] : $paged ),
					'show_all' => false,
					'type' => 'plain',
				);

				echo "<div class='pagination' style='padding-top:5px'>";
				echo wp_kses_post( paginate_links( $pagination_args ) );
				echo '</div>';
			}

			wp_reset_query();
			?>
		   
</div>
<script type="text/javascript">

			<?php $intval = uniqid( 'interval_' ); ?>
			   
					var <?php echo esc_html( $intval ); ?> = setInterval(function() {

					if(document.readyState === 'complete') {

					   clearInterval(<?php echo esc_html( $intval ); ?>);
					   
						
						jQuery(".main_grid_div").show();
						jQuery("#container").wrecker({
								// options
								itemSelector : ".<?php echo esc_html( $rand_Numb ); ?>",
								maxColumns : <?php echo esc_html( $settings['cols'] ); ?>,
								responsiveColumns : [
										
										{1024 : <?php echo esc_html( $settings['cols1024'] ); ?>},
										{800  : <?php echo esc_html( $settings['cols800'] ); ?>},
										{640  : <?php echo esc_html( $settings['cols640'] ); ?>}
								]
						   });     
	
					}    
	   
		}, 100);
</script>
		 
	<?php endif; ?><!-- end psc_print_post_grid_func -->
	<?php
	$output = ob_get_clean();
	return $output;
}


// also we will add an option function that will check for plugin admin page or not
function psc_post_slider_carousel_is_plugin_page() {

		$server_uri = 'http://';
	if ( isset( $_SERVER['HTTP_HOST'] ) ) {
		$server_uri .= sanitize_text_field( $_SERVER['HTTP_HOST'] );
	}

	if ( isset( $_SERVER['REQUEST_URI'] ) ) {

		$server_uri .= sanitize_text_field( $_SERVER['REQUEST_URI'] );
	}

	foreach ( array( 'psc_post_slider_carousel', 'psc_post_slider_grid' ) as $allowURI ) {
		if ( stristr( $server_uri, $allowURI ) ) {
			return true;
		}
	}
	return false;
}

// add media WP scripts
function psc_post_slider_carousel_admin_scripts_init() {
	if ( psc_post_slider_carousel_is_plugin_page() ) {
		// double check for WordPress version and function exists
		if ( function_exists( 'wp_enqueue_media' ) ) {
			// call for new media manager
			wp_enqueue_media();
		}
		wp_enqueue_style( 'media' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}
}

function psc_remove_extra_p_tags( $content ) {

	if ( strpos( $content, 'psc_print_post_slider_carousel_func' ) !== false ) {

		$pattern = '/<!-- psc_print_post_slider_carousel_func -->(.*)<!-- end psc_print_post_slider_carousel_func -->/Uis';
		$content = preg_replace_callback(
			$pattern,
			function ( $matches ) {

				$altered = str_replace( '<p>', '', $matches[1] );
				$altered = str_replace( '</p>', '', $altered );

				$altered = str_replace( '&#038;', '&', $altered );
				$altered = str_replace( '&#8221;', '"', $altered );

				return @str_replace( $matches[1], $altered, $matches[0] );
			},
			$content
		);

	}

		$content = str_replace( '<p><!-- psc_print_post_slider_carousel_func -->', '<!-- psc_print_post_slider_carousel_func -->', $content );
		$content = str_replace( '<!-- end psc_print_post_slider_carousel_func --></p>', '<!-- end psc_print_post_slider_carousel_func -->', $content );

		return $content;
}


function psc_grid_remove_extra_p_tags( $content ) {

	if ( strpos( $content, 'psc_print_post_grid_func' ) !== false ) {

		$pattern = '/<!-- psc_print_post_grid_func -->(.*)<!-- end psc_print_post_grid_func -->/Uis';
		$content = preg_replace_callback(
			$pattern,
			function ( $matches ) {

				$altered = str_replace( '<p>', '', $matches[1] );
				$altered = str_replace( '</p>', '', $altered );

				$altered = str_replace( '&#038;', '&', $altered );
				$altered = str_replace( '&#8221;', '"', $altered );

				return @str_replace( $matches[1], $altered, $matches[0] );
			},
			$content
		);

	}

		$content = str_replace( '<p><!-- psc_print_post_grid_func -->', '<!-- psc_print_post_grid_func -->', $content );
		$content = str_replace( '<!-- end psc_print_post_grid_func --></p>', '<!-- end psc_print_post_grid_func -->', $content );

		return $content;
}

  add_filter( 'widget_text_content', 'psc_remove_extra_p_tags', 999 );
  add_filter( 'the_content', 'psc_remove_extra_p_tags', 999 );


function i13_psc_render_block_defaults( $block_content, $block ) {

	$block_content = psc_remove_extra_p_tags( $block_content );
	$block_content = psc_grid_remove_extra_p_tags( $block_content );
	return $block_content;
}


add_filter( 'render_block', 'i13_psc_render_block_defaults', 10, 2 );
