<?php
	# source guide wat ik heb gebruikt: https://www.youtube.com/watch?v=vU7Mxj1COvM


	session_start();
	//connect naar database
	$db = mysqli_connect("localhost", "root", "", "authentication");

	if (isset($_POST['login_btn'])) {
		$username = mysql_real_escape_string($_POST['username']);
		$password = mysql_real_escape_string($_POST['password']);

		$password = md5($password);
		$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
		$result = mysqli_query($db, $sql);

		if (mysqli_num_rows($result) == 1){
			$_SESSION['message'] = "You are now logged in";
			$_SESSION['username'] = $username;
			header("location: home.php"); //stuurt het naar de home pagina
		}else{
			$_SESSION['message'] = "Username/password combination incorrect";
		}
		
	}
	
?>


<!DOCTYPE html>
<html>
<head>
	<title>Register, login and logout user php mysql</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
	<div class="header">
		<h1>Register, login and logout user php mysql</h1>
	</div>

<?php
	if (isset($_SESSION['message'])) {
		echo "<div id='error_msg'>".$_SESSION['message']."</div>";
		unset($_SESSION['message']);
	}
?>

<form method="post" action="login.php">
	<table>
		<tr>
			<td>Username:</td>
			<td><input type="text" name="username" class="textInput" required></td>
		</tr>
		
		<tr>
			<td>Password:</td>
			<td><input type="password" name="password" class="textInput" required></td>
		</tr>
		
		<tr>
			<td>
			<input type="submit" name="login_btn" value="login"></td>
		</tr>
	</table>

</form>
</body>
</html>