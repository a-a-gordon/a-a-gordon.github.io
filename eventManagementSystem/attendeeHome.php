<!DOCTYPE html>
<html lang="en">
    <head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Attendee Home</title>
		<link rel="stylesheet" href="assets/css/style.css"/>
        <?php
            //Alexis Gordon
            //ISTE 341 Project 1 Attendee Home Page
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
            $arch = new Architecture('attendeeHome.php'); 

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
				
				//Showing all events and sessions, where listed events can expand to show its given sessions
				echo "<h1 class='placeholder'>All Upcoming Events</h1>";
				echo $db->getAllEventSessionsAsAccordionTable();

				//Adding the footer
				echo $arch->Footer(); 
			}
		?>
		<script>
			//Making accordian
			var acc = document.getElementsByClassName('accordion');
			var i;

			for (i = 0; i < acc.length; i++) {
				acc[i].addEventListener('click', function() {
					this.classList.toggle('active');
					var panel = this.nextElementSibling;
					if (panel.style.display === 'block') {
						panel.style.display = 'none';
					} else {
					panel.style.display = 'block';
					}
				});
			}
		</script>
    </body>
</html>