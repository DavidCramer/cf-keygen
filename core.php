<?php
/**
 * Plugin Name: Caldera Forms - Key Generator
 * Plugin URI:  
 * Description: Generates a unique key.
 * Version:     1.0.0
 * Author:      David Cramer
 * Author URI:  
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */



// add filters
add_filter('caldera_forms_get_form_processors', 'cf_keygen_register_processor');
// add meta filter
add_filter('caldera_forms_get_entry_meta_keygen', 'cf_keygen_meta_viewer', 10, 2);
add_filter('caldera_forms_get_entry_meta_verify_key', 'cf_keygen_verify_meta_viewer', 10, 2);

add_filter('caldera_forms_get_addons', 'cf_keygen_register_addon' );
function cf_keygen_register_addon($addons){
	$addons['keygen'] = __FILE__;
	return $addons;
}


function cf_keygen_register_processor($pr){
	$pr['keygen'] = array(
		"name"              =>  __('Keygen', 'cf-keygen'),
		"description"       =>  __("Generates a key", 'cf-keygen'),
		"author"            =>  'David Cramer',
		"author_url"        =>  'http://cramer.co.za',
		"processor"         =>  'cf_keygen_make_key',
		"template"          =>  plugin_dir_path(__FILE__) . "config.php",
		"icon"				=>	plugin_dir_url(__FILE__) . "icon.png",
		"default"   =>  array(
			'pattern'   =>  '****-****-****-****'
			),
		
		"magic_tags"    =>  array(
			"name",
			"key"
			)
		);
	$pr['verify_key']       = array(
		"name"              =>  __('Verify Key', 'cf-keygen'),
		"description"       =>  __("Verifies a key", 'cf-keygen'),
		"author"            =>  'David Cramer',
		"author_url"        =>  'http://cramer.co.za',
		"pre_processor"     =>  'cf_keygen_verify_key',
		"processor"         =>  'cf_keygen_bind_key',
		"icon"				=>	plugin_dir_url(__FILE__) . "icon-validate.png",
		"template"          =>  plugin_dir_path(__FILE__) . "config_verify.php",
		//"meta_template"		=>  plugin_dir_path(__FILE__) . "meta_template.php",
		"magic_tags"	=>	array(
			"key_name",
			"verified_key",
			"key_entry",
			"verification_count",
			"key_form",
			"key_processor",
		)
	);

	return $pr;
}
function cf_keygen_verify_meta_viewer($meta, $config){	
	$meta['meta_key'] = ucwords(str_replace('_', ' ', $meta['meta_key']) );
	return $meta;
}
function cf_keygen_meta_viewer($meta, $config){
	//if($meta['meta_key'] == 'key'){
	//	$meta['meta_key'] = Caldera_Forms::do_magic_tags( $config['name'] );
	//}
	return $meta;
}
function cf_keygen_verify_key($config, $form){
	global $wpdb, $transdata, $processed_meta; 

	$entry = Caldera_Forms::do_magic_tags($config['key']);
	$filter = '';
	if(!empty($config['connect'])){
		$filter = " AND `process_id` = '".$config['connect']."' ";
	}

	$key_entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . $wpdb->prefix . "cf_form_entry_meta` WHERE `meta_key` = 'key' ".$filter." AND `meta_value` = %s LIMIT 1 ;", $entry) );
	if(!empty($key_entry)){
		$in_limit = 0;
		if(!empty($config['limit'])){
			$query = "SELECT COUNT(`meta`.`entry_id`) AS `total`
					FROM `" . $wpdb->prefix . "cf_form_entry_meta` AS `meta`
					LEFT JOIN `" . $wpdb->prefix . "cf_form_entries` AS `entry` ON (`meta`.`entry_id` = `entry`.`id`) 
					WHERE `meta`.`meta_key` = 'verified_key' AND `entry`.`status` = 'active' AND `meta`.`meta_value` = %s ;";

			$in_limit = $wpdb->get_var( $wpdb->prepare( $query, $entry) );

			if($in_limit >= $config['limit']){
				$failed = true;
			}
		}    
		if(empty($failed)){
			// form
			$form_id = $wpdb->get_var( "SELECT `form_id` FROM `" . $wpdb->prefix . "cf_form_entries` WHERE `id` = '".$key_entry->entry_id."' AND `status` = 'active';");
			if(!empty($form_id)){
				// check the key has not been validated before and if so is it in the limit.
				// get date
				$link_form = get_option( $form_id );

				Caldera_Forms::set_submission_meta('verified_key', $entry, $form, $config['processor_id']);
				Caldera_Forms::set_submission_meta('key_entry', $key_entry->entry_id, $form, $config['processor_id']);
				Caldera_Forms::set_submission_meta('verification_count', $in_limit + 1, $form, $config['processor_id']);
				Caldera_Forms::set_submission_meta('key_form', $form_id, $form, $config['processor_id']);
				Caldera_Forms::set_submission_meta('key_processor', $key_entry->process_id, $form, $config['processor_id']);

				if(isset($link_form['processors'][$key_entry->process_id]['config']['name'])){
					Caldera_Forms::set_submission_meta('key_name', $link_form['processors'][$key_entry->process_id]['config']['name'], $form, $config['processor_id']);

					//$transdata[$config['processor_id']]['key_name']		= $link_form['processors'][$key_entry->process_id]['config']['name'];
				}
				/*
				$transdata[$config['processor_id']]['verified_key']			= $entry;
				$transdata[$config['processor_id']]['key_entry']			= $key_entry->entry_id;
				$transdata[$config['processor_id']]['verification_count']	= $in_limit + 1;
				$transdata[$config['processor_id']]['key_form']				= $form_id;
				$transdata[$config['processor_id']]['key_processor']		= $key_entry->process_id;
				
				$processed_meta[$form['ID']][$config['processor_id']] = $transdata[$config['processor_id']];
				*/
				return;
			}
		}
	}
	// fail
	$fail['type'] = 'error';
	if(!empty($config['fail_banner'])){
		$fail['note'] = Caldera_Forms::do_magic_tags($config['fail_banner']);
	}else{
		$fail['note'] = __('Invalid Key');
	}
	/*if(!empty($config['fail_field'])){
		$fail['fields'][$config['key']] = Caldera_Forms::do_magic_tags($config['fail_field']);
	}else{
		$fail['fields'][$config['key']] = __('Invalid');
	}*/

	return $fail;
}
function cf_keygen_bind_key($config, $form){
	global $wpdb, $transdata;

	//return $transdata[$config['processor_id']];
	
}
function cf_keygen_make_key($config, $form){
	global $wpdb;


	$an_tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789';
	$n_tokens = '0123456789';
	$a_tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$not_unique = true;
	
	while( $not_unique === true){
		$input_line = $config['pattern'];

		// alphanumer
		preg_match_all("/(\*)/", $input_line, $alphanumer);
		if(!empty($alphanumer[0])){
			for($i=0;$i<count($alphanumer[0]); $i++){
				$char = $an_tokens[rand(0, (strlen($an_tokens)-1) )];
				$input_line = preg_replace('/\*/', $char,$input_line, 1);
			}
		}
		// alphanumer
		preg_match_all("/(#)/", $input_line, $alphanumer);
		if(!empty($alphanumer[0])){
			for($i=0;$i<count($alphanumer[0]); $i++){
				$char = $n_tokens[rand(0, (strlen($n_tokens)-1) )];
				$input_line = preg_replace('/#/', $char,$input_line, 1);
			}
		}
		// alphanumer
		preg_match_all("/(&)/", $input_line, $alphanumer);
		if(!empty($alphanumer[0])){
			for($i=0;$i<count($alphanumer[0]); $i++){
				$char = $a_tokens[rand(0, (strlen($a_tokens)-1) )];
				$input_line = preg_replace('/&/', $char,$input_line, 1);
			}
		}

		$is_unique = $wpdb->get_var( $wpdb->prepare( "SELECT `meta_value` FROM `" . $wpdb->prefix . "cf_form_entry_meta` WHERE `meta_key` = 'key' AND `meta_value` = %s LIMIT 1 ;", $input_line) );
		
		if(empty($is_unique)){
			$not_unique = false;
		}
	}

	return array('name' => Caldera_Forms::do_magic_tags( $config['name'] ), 'key' => $input_line);
}