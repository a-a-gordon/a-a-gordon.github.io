<!DOCTYPE html>
<html lang="en">
    <head>
		<meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Login</title>
        <link rel="stylesheet" href="assets/css/style.css"/>
        <?php
            //Alexis Gordon
            //ISTE 341 Project 1 Registration Page
            //10-18-2021
        ?>
    </head>

    <body>
        <!-- Form area -->
		<form class='center_form placeholder_login' action="registration.php" method="POST">
            <h1>Register with a New Account</h1>

			<!-- Username -->
			<div>
				<label for="newUsername">Username:</label>
				<input type="text" id="newUsername" name="newUsername" size="30">
			</div><br/>

			<!-- Password -->
			<div>
				<label for="newPassword">Password:</label>
				<input type="password" id="newPassword" name="newPassword" size="30">
			</div>

            <!-- Role -->
            <p style='margin-bottom: 0;'>Role:</p>
                <div class='potentialRoles'>
                    <input type='radio' name='newUserRole' value='1'>Admin<br>
                    <input type='radio' name='newUserRole' value='2'>Event Manager<br>
                    <input type='radio' name='newUserRole' value='3'>Attendee<br>
                </div><br/>

			<div>
				<input class='add' type="submit" name="submit" value="Register">
			</div><br/>
            <?php
                if (isset($_GET['set'])){
                    if ($_GET['set']=='fail'){
                        echo '<p id="registerMessage">Username and password combo is already being used</p>';
                    }
                }
            ?>
		</form>
    
        <?php
            $_SESSION['loggedIn'] = False;

            //Adding the architecture php page and calling the Navigation function
            require 'informationArchitecture.php'; 
            $arch = new Architecture('registration.php'); 
            //Adding the permission-specific navigation
            echo $arch->Navigation(); 
            
            //If the username, password and role fields were filled out
            if (!empty($_POST['newUsername']) && !empty($_POST['newPassword']) && isset($_POST['newUserRole'])){ 
                echo "<script> setMessage(''); </script>";
                
                //Sanitize the data
                $username = filter_var(stripslashes(trim($_POST['newUsername'])), FILTER_SANITIZE_STRING);
                $password = filter_var(stripslashes(trim($_POST['newPassword'])), FILTER_SANITIZE_STRING);
            
                //Creating a database instance to work with
                require_once "PDO.DB.class.php";
                $db = new DB();

                //hash password
                $hashPassword = password_hash($password, PASSWORD_DEFAULT);

                //Check against existing users to make sure not same combo for username and password
                $user = $db->isValidCredentials($username, $password);

                if ($user['id']==-1){
                    //User name and password combo does not already exist in the db
                    //Add the user
                    $addUserSuccess = $db->addNewUser($username, $hashPassword, $_POST['newUserRole']);

                    if ($addUserSuccess){
                        //Redirect to the login once registered to sign in with new credentials 
                        header("Location: login.php");
                    }
                } else {
                    header('Location: registration.php?set=fail');
                }
            } 

            //Adding the footer
            echo $arch->Footer(); 
        ?>
    </body>
</html>
