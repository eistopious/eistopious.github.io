<?php
	session_start();
	// If the user is logged in, header them away
	if(isset($_SESSION["username"])){
		header('Location: message.php?msg=NO to that weenis');
	exit();
	}
?>
<?php
	// Ajax calls this NAME CHECK code to execute
	if(isset($_POST["usernamecheck"])){
		// Set up the mysqli connection 

		include_once 'php_includes/conn.php';
		$username = preg_replace('#[^a-z0-9]#i', '', $_POST['usernamecheck']);
		$sql = "SELECT `id` FROM `users` WHERE `username`='$username' LIMIT 1";
		$query = mysqli_query($conn, $sql);
		$uname_check = mysqli_num_rows($query);

		// Check the lenght of the username

		if(strlen($username) < 3 || strlen($username) > 16){
			echo '<strong style="color:#F00;">Username must be between 3-16 characters</strong>';
			exit();
		}

		// Check if the username starts with a number

		if(is_numeric($username[0])){
			echo '<strong style="color:#F00;">Usernames must begin with a letter</strong>';
			exit();
		}

		// Check if the username is correct

		if($uname_check < 1){
			echo '<strong style="color:#009900;">' . $username . ' is OK</strong>';
			exit();

			// If it's not 
		}else{
			echo '<strong style="color:#F00;">' . $username . ' is taken</strong>';
			exit();
		}

	}
?>
<?php
	// Ajax calls this REGISTRATION code to executE
	if(isset($_POST["u"])){
		// CONNECT TO THE DATABASE
		include_once 'php_includes/conn.php';

		// GATHER THE POST DATA INTO LOCAL VARIABLES

		$u = preg_replace('#[^a-z0-9]#i', '', $_POST["u"]);
		$e = mysqli_real_escape_string($conn, $_POST["e"]);
		$p = $_POST["p"];
		$g = preg_replace('#[^a-z]#', '', $_POST["g"]);
		$c = preg_replace('#[^a-z ]#i', '', $_POST["c"]);

		// GET USER IP ADDRESS
		$ip = preg_replace('#[^0-9]#', '', getenv('REMOTE_ADDR'));

		// DUPLICATE DATA CHECKS FOR USERNAME AND EMAIL
		$sql = "SELECT `id` FROM `users` WHERE `username`='$u' LIMIT 1";
		$query = mysqli_query($conn, $sql);
		$u_check = mysqli_num_rows($query);
		// -------------------------------------------
		$sql = "SELECT `id` FROM `users` WHERE `email`='$e' LIMIT 1";
		$query = mysqli_query($conn, $sql);
		$e_check = mysqli_num_rows($query);

		// FORM DATA ERROR HANDLING
		if($u == "" || $e == "" || $p == "" || $g == "" || $c == ""){
			echo "The form submission is missing values.";
        exit();
    	}else if($u_check > 0){
    		echo "The username you entered is alreay taken";
    		exit();
    	}else if($e_check > 0){
    		echo "That email address is already in use in the system";
    		exit();
    	}else if(strlen($u) < 3 || strlen($u) > 16){
    		echo "Username must be between 3 and 16 characters";
    		exit();
    	}else if(is_numeric($u[0])){
    		 echo 'Username cannot begin with a number';
        	exit();
    	}else{
    		// END FORM DATA ERROR HANDLING
    			// Begin Insertion of data into the database
    				// Hash the password and apply the salt
    				include_once ("php_includes/randStrGen.php");
    				$p_hash = randStrGen(20).randStrGen(20);
    				// Add user info into the database table for the main site table
    				$sql = "INSERT INTO `users` (`username`, `email`, `password`, `gender`, `country`, `ip`, `signup`, `lastlogin`, `notescheck`)
    						VALUES('$u', '$e', '$p_hash', '$g', '$c', '$ip', NOW(), NOW(), NOW())";
    				$query = mysqli_query($conn, $sql); 
					$uid = mysqli_insert_id($conn); 
					$sql = "INSERT INTO useroptions (id, username, background) VALUES ('$uid','$u','original')";

					// Establish their row in the useroptions table
					$query = mysqli_query($conn, $sql);

					// Create directory(folder) to hold each user's files(pics, MP3s, etc.)
					if (!file_exists("user/$u")) {
						mkdir("user/$u", 0755);
					}

					// Email the user their activation link

					$to = "$e";							 
					$from = "auto_responder@socialmedia.com";
					$subject = 'SocialMedia Account Activation';
					$message = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Social Media Message</title></head><body style="margin:0px; font-family:Tahoma, Geneva, sans-serif;"><div style="padding:10px; background:#333; font-size:24px; color:#CCC;"><a href="http://www.socialmedia.com"><img src="http://www.socialmedia.com/images/logo.png" width="36" height="30" alt="socialmedia" style="border:none; float:left;"></a>SocialMedia Account Activation</div><div style="padding:24px; font-size:17px;">Hello '.$u.',<br /><br />Click the link below to activate your account when ready:<br /><br /><a href="http://www.socialmedia.com/activation.php?id='.$uid.'&u='.$u.'&e='.$e.'&p='.$p_hash.'">Click here to activate your account now</a><br /><br />Login after successful activation using your:<br />* E-mail Address: <b>'.$e.'</b></div></body></html>';
					$headers = "From: $from\n";
			        $headers .= "MIME-Version: 1.0\n";
			        $headers .= "Content-type: text/html; charset=iso-8859-1\n";
					mail($to, $subject, $message, $headers);
					echo "<strong style='color:#009900;'>signup_success</strong>";
					exit();
    	}
    	exit();
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Social Media - Sign Up</title>
	<meta charset="UTF-8">
	<link rel="icon" type="image/x-icon" href="images/logo.png">
	<link rel="stylesheet" type="text/css" href="style/style.css">
	<style type="text/css">
		#signupform{
			margin-top: 24px;
		}

		#signupform > div{
			margin-top: 12px;
		}

		#signupform > input,select{
			width: 200px;													
			padding: 3px;
			background: #F3F9DD;
		}

		#signupbtn{
			font-size: 14px;
			padding: 8px;
		}

		#terms{
			border: 1px solid #ccc;
			background: #F5F5F5;
			padding: 12px;
		}

		#pageMiddle_2{
			background-color: #fff;
			width: 1000px;
			margin: 0 auto;
			height: 900px;
		}

	</style>
	<script src="js/main.js"></script>
	<script src="js/ajax.js"></script>
	<script>
		// Onkeyup function

		function restrict(elem){
			var tf = _(elem);
			var rx = new RegExp;
			if(elem == "email"){
				rx = /[' "]/gi;
			}else if(elem == "username"){
				rx = /[^a-z0-9]/gi;
			}
			tf.value = tf.value.replace(rx, "");
		}

		// Empty element function

		function emptyElement(x){
			_(x).innerHTML = "";
		}

		// Check username function

		function checkusername(){
			var u = _("username").value;
			if(u != ""){
				_("unamestatus").innerHTML = 'checking...';
				var ajax = ajaxObj("POST", "signup.php");
			ajax.onreadystatechange = function(){
				if(ajaxReturn(ajax) == true){
					_("unamestatus").innerHTML = ajax.responseText;
				}
			}
			ajax.send("usernamecheck="+u);
			}
		}

		// Signup function

		function signup(){
			var u = _("username").value;
			var e = _("email").value;
			var p1 = _("pass1").value;
			var p2 = _("pass2").value;
			var c = _("country").value;
			var g = _("gender").value;
			var status = _("status");
			if(u == "" || e == "" || p1 == "" || p2 == "" || c == "" || g == ""){
				status.innerHTML = "Fill out all of the form data";
			} else if(p1 != p2){
				status.innerHTML = "Your password fields do not match";
			} else if( _("terms").style.display == "none"){
				status.innerHTML = "Please view the terms of use";
			} else {
				_("signupbtn").style.display = "none";
				status.innerHTML = 'please wait ...';
				var ajax = ajaxObj("POST", "signup.php");
		        ajax.onreadystatechange = function() {
			        if(ajaxReturn(ajax) == true) {
			            if(ajax.responseText.replace(/^\s+|\s+$/g, "") ==  "signup_success"){
							status.innerHTML = ajax.responseText;
							_("signupbtn").style.display = "block";
						} else {
							window.scrollTo(0,0);
							_("signupform").innerHTML = "OK "+u+", check your email inbox and junk mail box at <u>"+e+"</u> in a moment to complete the sign up process by activating your account. You will not be able to do anything on the site until you successfully activate your account.";
						}
			        }
		        }
		        ajax.send("u="+u+"&e="+e+"&p="+p1+"&c="+c+"&g="+g);
			}
		}

		// Open Terms function

		function openTerms(){
			_("terms").style.display = "block";
			emptyElement("status");
		}

		/* function addEvents(){
			_("elemID").addEventListener("click", func, false);
		}
		window.onload = addEvents; */
	</script>
</head>
<body>
    <!-- JavaScript jQuery functions
		onblur - when I leave that field I'll call my func.
		onkeyup - when I press any key in a field I'll call my func.
		onfocus - when a field gets focus (if I click on it)
     -->
	<?php require_once 'template_pageTop.php'; ?>
	<div id="pageMiddle_2">
		<h3>Sign Up Here</h3>
		<form name="signupform" id="signupform" onsubmit="return false;">
			<div>Username:</div>
			<input id="username" type="text" onblur="checkusername()" onkeyup="restrict('username')" maxlength="16">
			<span id="unamestatus"></span>
			<div>Email Address:</div>
			<input id="email" type="text" onfocus="emptyElement('status')" onkeyup="restrict('email')" maxlength="88">
			<div>Create Password:</div>
			<input id="pass1" type="password" onfocus="emptyElement('status')" maxlength="16">
			<div>Confirm Password:</div>
			<input id="pass2" type="password" onfocus="emptyElement('status')" maxlength="16">
			<div>Gender:</div>
			<select id="gender" onfocus="emptyElement('status')">
				<option value=""></option>
				<option value="m">Male</option>
				<option value="f">Female</option>
			</select>
			<div>Country:</div>
			<select id="country" onfocus="emptyElement('status')">
				<?php require_once 'template_country_list.php'; ?>
			</select>
			<div>
		      <a href="#" onclick="return false" onmousedown="openTerms()">
		        View the Terms Of Use
		      </a>
		    </div>
		    <div id="terms" style="display:none;">
		      <h3>Social Media Terms Of Use</h3>
		      <p>1. Play nice here.</p>
		      <p>2. Take a bath before you visit.</p>
		      <p>3. Brush your teeth before bed.</p>
		    </div>
		    <br /><br />
		    <button id="signupbtn" onclick="signup()">Create Account</button>
		    <span id="status"></span>
		</form>
	</div>
	<?php include_once("template_pageBottom.php"); ?>
</body>
</html>