<?php

require_once('../../../../wp-load.php');

if(isset($_POST['id']) && is_numeric($_POST['id'])){
	$id = (int)$_POST['id'];

	if($id > 0){
		$wpdb->query("DELETE FROM v4_search WHERE id = '$id'");
	}else{
		$wpdb->query("TRUNCATE TABLE v4_search");
	}
}