<?php
/**
 * @package v4Search
 * @version 0.1
 */
/*
Plugin Name: v4Search
Plugin URI: 
Description: Плъгин, който ще ви помогне да разберете какво търсят потребителите във вашият сайт.
Author: ItsValentin
Version: 0.1
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

error_reporting(E_ALL);

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

	$s = addslashes(htmlspecialchars($_GET['s']));
	//print_r($s);
	
	if(empty($_COOKIE['v4_user'])){
		$randomS = randomS(10);
		setcookie('v4_user', $randomS, 0, "/"); 
		$v4_user = $randomS;
	}else{
		$v4_user = $_COOKIE['v4_user'];
	}
	$v4_user = addslashes(htmlspecialchars($v4_user));

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

	if(!empty($_GET['params'])){
		$params = addslashes(htmlspecialchars($_GET['params']));
		$moreQ .= " AND params = '".$params."'";
		$moreU .= '&params='.$params;
		$moreT = $lang['results_for'].' <u>'. $params.'</u> ('.$lang['keyword'].')';
	}

	if(!empty($_GET['user_id'])){
		$user_id = addslashes(htmlspecialchars($_GET['user_id']));
		$moreQ .= " AND user_id = '".$user_id."'";
		$moreU .= '&user_id='.$user_id;
		$moreT = $lang['results_for'].' <u>'. $user_id.'</u> ('.$lang['user_key'].')'; 
	}	

	if(!empty($_GET['date'])){
		$date = addslashes(htmlspecialchars($_GET['date']));
		$moreQ .= " AND date = '".$date."'";
		$moreU .= '&date='.$date;
		$moreT = $lang['results_for'].' <u>'. $date.'</u> ('.$lang['date'].')'; 
	}

	?>
	<script src="<?php echo plugins_url(); ?>/v4_search/jquery.js"></script>
	<style>
	.v4_table {
	  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
	  border-collapse: collapse;
	  width: 100%;
	}

	.v4_table td, .v4_table th {
	  border: 1px solid #ddd;
	  padding: 8px;
	}

	.v4_table tr:nth-child(even){
		background-color: #f2f2f2;
	}

	.v4_table tr:hover {
		background-color: #ddd;
	}

	.v4_table th {
	  padding-top: 12px;
	  padding-bottom: 12px;
	  text-align: left;
	  background-color: #333;
	  color: white;
	}

	.v4_link {
		cursor:pointer;
	}


	</style>

	<script>
		function deleteAjax(id){
			$.ajax({
			  type: 'POST',
			  url: "<?php echo plugins_url() ?>/v4_search/ajax/ajax.php",
			  cache: false,
			  data: {
			  	id:id,
			  },
			  success: function(html){
			  	if(id>0){
			   		$('#v4_row_'+id).fadeOut();
			   	}else{
			   		$('.v4_all_rows').hide();
			   	}
			  }
			});
		}
	</script>

	<?php
	$html = '<center><h1>'.$lang['results'].'</h1><a onclick="deleteAjax(0)" class="v4_link">'.$lang['delete_all'].'</a><br><br>';

	if(!empty($moreT)){
		$html .= '<h2>'.$moreT.' - <a href="'.$moreA.'">'.$lang['clear'].'</a></h2><br><br>';
	}

	$html .= '<table style="width:50%;" class="v4_table">';	
	$html .= '<tr>';
	$html .= '<th><b>'.$lang['user_key'].'</b></th>';
	$html .= '<th><b>'.$lang['keyword'].'</b></th>';
	$html .= '<th><b>'.$lang['date'].'</b></th>';
	$html .= '<th><b>'.$lang['options'].'</b></th>';
	$html .= '</tr>';

	$selectS = $wpdb->get_results("SELECT * FROM v4_search WHERE 1=1 $moreQ");

	foreach($selectS AS $s){

	$html .= '<tr id="v4_row_'.$s->id.'" class="v4_all_rows">';
		$html .= '<td><a href="'.$moreA.'&user_id='.$s->user_id.'">'.$s->user_id.'</a></td>';
		$html .= '<td><a href="'.$moreA.'&params='.$s->params.'">'.$s->params.'</a></td>';
		$html .= '<td><a href="'.$moreA.'&date='.$s->date.'">'.$s->date.'</a></td>';
		$html .= '<td><a class="v4_link" onclick="deleteAjax('.$s->id.')">X</a></td>';
	$html .= '</tr>';	
	
	}

	$html .= '</table></center>';

	echo $html;
}

function randomS($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


add_action('admin_menu', 'test_plugin_setup_menu');
 
function test_plugin_setup_menu(){
    add_menu_page( 'V4 Search', 'V4 Search', 'manage_options', 'v4_search', 'v4_admin_page', 'dashicons-search');
}