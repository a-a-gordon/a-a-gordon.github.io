<!DOCTYPE html>
<html lang="en">
    <head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Attendee Registration</title>
		<link rel="stylesheet" href="assets/css/style.css"/>
        <?php
            //Alexis Gordon
            //ISTE 341 Project 1 Attendee Registration Page
            //10-18-2021
        ?>
    </head>
    <body>
		<?php
			//Continuing the user's session
			session_name("user_login_form");
			session_start();

			//Adding the architecture php page
			require 'informationArchitecture.php'; 
			$arch = new Architecture('attendeeRegistration.php'); 

			if (!$_SESSION['loggedIn']){
				//If not logged in, sending session back to login.php file (security measure)
				header("Location: login.php");
				exit;
			} else {
				//Adding the permission-specific navigation
				echo $arch->Navigation(); 

				//Creating a database instance to work with
				require_once "PDO.DB.class.php";
            	$db = new DB();

                echo "<h1 class='placeholder'>My Registered Event Sessions</h1>";
				//Retreive table of sessions attendee is registered for based on their userID
				echo $db->getAttendeeRegisteredSessionsAsTable($_SESSION['user']['id']);
			}
		?>

		<div class='buttons'>
			<!--Add new session to specifc user registration button-->
			<button class='add' onclick="openFormAddUserToSession()">Add Session</button>
			<!--Remove session for specifc user registration button-->
			<button class='remove' onclick="openFormRemoveUserFromSession()">Remove Session</button>
		</div>

		<!--Form that will appear when the 'Add Session' button above is clicked and will prompt users to select from
			a set of sessions they are not currently registered for-->
		<div class="addUserToSession">
			<div class="formAddUserToSession" id="popupFormAddUserToSession">
				<form action="attendeeRegistration.php" method='POST' class="formContainer">

				<h2 class='formHeader'>Available Sessions</h2>
				<p><strong>Please select the session you will register for</strong></p>

				<?php 
					//Retreive radio button list of sessions attendee is not registered for
					echo $db->getAttendeePotentialSessionsAsButtons($_SESSION['user']['id'], 'sessionUserAdd');
				?>

				<button type="submit" class="btn">Add Session</button>
				<button type="button" class="btn cancel" onclick="closeFormAddUserToSession()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormAddUserToSession() {
				document.getElementById("popupFormAddUserToSession").style.display = "block";
			}
			function closeFormAddUserToSession() {
				document.getElementById("popupFormAddUserToSession").style.display = "none";
			}
		</script>

		<!--Form that will appear when the 'Remove Session' button above is clicked and will prompt users to select from
			a set of sessions they are currently registered for-->
		<div class="removeUserFromSession">
			<div class="formRemoveUserFromSession" id="popupFormRemoveUserFromSession">
				<form action="attendeeRegistration.php" method='POST' class="formContainer">

				<h2 class='formHeader'>Your Sessions</h2>
				<p><strong>Please select the session you wish to remove</strong></p>

				<?php 
					//Retreive radio button list of sessions attendee is currently registered for
					echo $db->getAttendeePotentialSessionsAsButtons($_SESSION['user']['id'], 'sessionUserRemove');
				?>

				<button type="submit" class="btn">Remove Session</button>
				<button type="button" class="btn cancel" onclick="closeFormRemoveUserFromSession()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormRemoveUserFromSession() {
				document.getElementById("popupFormRemoveUserFromSession").style.display = "block";
			}
			function closeFormRemoveUserFromSession() {
				document.getElementById("popupFormRemoveUserFromSession").style.display = "none";
			}
		</script>


		<?php 
			//Add or remove the session and reload the page for the update
			function refreshPage(){
				echo "<script>window.location.href='attendeeRegistration.php'</script>";
			}

			//If the form is submitted to add a new session to an attendee's registration, add it
			if (isset($_POST['sessionUserAdd'])){
				$addSuccess = $db->addUserToSession($_SESSION['user']['id'], $_POST['sessionUserAdd']); 
				
				if ($addSuccess){
					refreshPage();
				}
			}

			//If the form is submitted to remove a session to an attendee's registration, remove it
			if (isset($_POST['sessionUserRemove'])){
				$removeSuccess = $db->removeUserFromSession($_SESSION['user']['id'], $_POST['sessionUserRemove']); 

				if ($removeSuccess){
					refreshPage();
				}
			}

			//Adding the footer
			echo $arch->Footer(); 
		?>
    </body>
</html>