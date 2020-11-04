<?php
/**
 * @package v4Search
 * @version 0.0.1
 */
/*
Plugin Name: v4Search
Plugin URI: 
Description: V4 Search is a plugin that helps you understand what users are searching in your Wordpress Website.
Author: ItsValentin
Version: 0.0.1
Author URI: https://itsvalentin.com
*/

 

$lang = array(
	'results' => 'Results',
	'delete_all' => 'Delete Everything',
	'clear' => 'Clear',
	'results_for' => 'Results for',
	'user_key' => 'User Key',
	'keyword' => 'Keyword',
	'date' => 'Date',
	'options' => 'Options',
);

$ex = $wpdb->query("SHOW TABLES LIKE 'v4_search'");

if(empty($ex)){

$wpdb->query("CREATE TABLE `v4_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `params` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");


}


	
if(!empty($_GET['s'])){

	$s = esc_attr($_GET['s']);
	
	if(empty($_COOKIE['v4_user'])){
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < 15; $i++) {
	        $randomS .= $characters[rand(0, $charactersLength - 1)];
	    }

		setcookie('v4_user', $randomS, 0, "/"); 
		$v4_user = $randomS;
	}else{
		$v4_user = $_COOKIE['v4_user'];
	}
	$v4_user = esc_attr($v4_user);

	$today = date('d.m.Y');
	$ex_same_search = $wpdb->query("SELECT * FROM v4_search WHERE user_id = '$v4_user' AND params = '$s' AND `date` = '$today'");
	if(!empty($ex_same_search)){
		$wpdb->query("UPDATE v4_search SET count = count + 1 WHERE user_id = '$v4_user' AND params = '$s' AND `date` = '$today'");
	}else{
	
		$wpdb->query("INSERT INTO v4_search SET user_id = '$v4_user', params = '$s', `date` = '$today', count = 1");
	}
}

function v4_admin_page(){
	global $wpdb, $lang;
	$moreQ = '';
	$moreU = '';
	$moreT = '';
	$moreA = admin_url().'admin.php?page=v4_search';


	if(isset($_GET['delete_all_v4_search_button']) && $_GET['delete_all_v4_search_button'] == 'correct_data'){
		$wpdb->query("TRUNCATE TABLE v4_search");
		echo '<center><h1>LOG HAS BEEN DELETED</h1><br>';
		echo '<a href="'.$moreA.'">Go back</а></center>';
		exit;
	}

	if(!empty($_GET['params'])){
		$params = esc_attr($_GET['params']);
		$moreQ .= " AND params = '".$params."'";
		$moreU .= '&params='.$params;
		$moreT = $lang['results_for'].' <u>'. $params.'</u> ('.$lang['keyword'].')';
	}

	if(!empty($_GET['user_id'])){
		$user_id = esc_attr($_GET['user_id']);
		$moreQ .= " AND user_id = '".$user_id."'";
		$moreU .= '&user_id='.$user_id;
		$moreT = $lang['results_for'].' <u>'. $user_id.'</u> ('.$lang['user_key'].')'; 
	}	

	if(!empty($_GET['date'])){
		$date = esc_attr($_GET['date']);
		$moreQ .= " AND date = '".$date."'";
		$moreU .= '&date='.$date;
		$moreT = $lang['results_for'].' <u>'. $date.'</u> ('.$lang['date'].')'; 
	}

	$html = '<form method="POST" action="'.$moreA.'&delete_all_v4_search_button=correct_data"><center><h1>'.$lang['results'].'</h1><input type="submit" name="delete_all_button" value="'.$lang['delete_all'].'"></form><br><br>';

	if(!empty($moreT)){
		$html .= '<h2>'.$moreT.' - <a href="'.$moreA.'">'.$lang['clear'].'</a></h2><br><br>';
	}

	$html .= '<table style="width:50%;" class="v4_table">';	
	$html .= '<tr>';
	$html .= '<th><b>'.$lang['user_key'].'</b></th>';
	$html .= '<th><b>'.$lang['keyword'].'</b></th>';
	$html .= '<th><b>'.$lang['date'].'</b></th>';
	$html .= '</tr>';

	$selectS = $wpdb->get_results("SELECT * FROM v4_search WHERE 1=1 $moreQ");

	foreach($selectS AS $s){
		$html .= '<tr id="v4_row_'.$s->id.'" class="v4_all_rows">';
		$html .= '<td><a href="'.$moreA.'&user_id='.$s->user_id.'">'.$s->user_id.'</a></td>';
		$html .= '<td><a href="'.$moreA.'&params='.$s->params.'">'.$s->params.'</a></td>';
		$html .= '<td><a href="'.$moreA.'&date='.$s->date.'">'.$s->date.'</a></td>';
		$html .= '</tr>';	
	}

	$html .= '</table></center>';
	
	echo $html;

	if(isset($_POST['delete_button'])){
		$id = esc_attr($_POST['delete_button']);
		$wpdb->query("DELETE FROM v4_search WHERE id = '$id'");
	}
}
	
if (!function_exists(v4_search_menu_askljdhgqwkejgqlkdjxgaskljd)) {

	add_action('admin_menu', 'v4_search_menu_askljdhgqwkejgqlkdjxgaskljd');
	function v4_search_menu_askljdhgqwkejgqlkdjxgaskljd(){
	    add_menu_page( 'V4 Search', 'V4 Search', 'manage_options', 'v4_search', 'v4_admin_page', 'dashicons-search');
	}

	wp_enqueue_style( 'v4_style', plugins_url('v4_search/css/style.css'));
}