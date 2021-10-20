<!DOCTYPE html>
<html lang="en">
    <head>
		<meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Login</title>
        <link rel="stylesheet" href="assets/css/style.css"/>
        <?php
            //Alexis Gordon
            //ISTE 341 Project 1 Login
            //10-18-2021
        ?>
    </head>
    <body>
        <!-- Form area -->
		<form class='center_form placeholder_login' action="login.php" method="POST">
            <h1>Login</h1>

			<!-- Username -->
			<div>
				<label for="usernameLogin">Username:</label>
				<input type="text" id="usernameLogin" name="usernameLogin" size="30">
			</div><br/>

			<!-- Password -->
			<div>
				<label for="passwordLogin">Password:</label>
				<input type="password" id="passwordLogin" name="passwordLogin" size="30">
			</div>

			<div>
				<input class='add' type="submit" name="submit" value="Login">
			</div><br/>

            <a class='register' href='registration.php'>Or register with a new account</a>
		</form>

        <?php
            //Initiating a session
            session_name('user_login_form');
            session_start();

            //Defaulting session to not logged in
            $_SESSION['loggedIn'] = false;

            //Adding the architecture php page and calling the Navigation function
            require 'informationArchitecture.php'; 
            $arch = new Architecture('login.php'); 
            //Adding the permission-specific navigation
            echo $arch->Navigation(); 
            
            //If the username and password fields were filled out
            if (!empty($_POST['usernameLogin']) && !empty($_POST['passwordLogin'])){ 
                //Sanitize the data
                $username = filter_var(stripslashes(trim($_POST['usernameLogin'])), FILTER_SANITIZE_STRING);
                $password = filter_var(stripslashes(trim($_POST['passwordLogin'])), FILTER_SANITIZE_STRING);

                //Creating a database instance to work with
                require_once "PDO.DB.class.php";
                $db = new DB();

                //Validates user's credentials and returns an array containing a user's id, name, and role
                $user = $db->isValidCredentials($username, $password);
                
                if ($user['id'] != -1){ 
                    //If a valid userID is returned, set logged in to true and set a session variable with the user information
                    $_SESSION['loggedIn'] = True;
                    $_SESSION['user'] = $user;

                    //Redirect to the attendee home page
                    header("Location: attendeeHome.php");
                } else {
                    echo "Incorrect Login";
                }
            } 

            //Adding the footer
            echo $arch->Footer(); 
        ?>
    </body>
</html>
