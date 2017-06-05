<?php
	$conn = mysqli_connect('127.0.0.1', 'root', '', 'logintests');
	// Connection testing
	if(mysqli_connect_errno()){
		echo mysqli_connect_error();
		exit();
	}
?>