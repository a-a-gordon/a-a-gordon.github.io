<!DOCTYPE html>
<html lang="en">
    <head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Event Management</title>
		<link rel="stylesheet" href="assets/css/style.css"/>
        <?php
            //Alexis Gordon
            //ISTE 341 Project 1 Event Management Page
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
			$arch = new Architecture('eventManagerManageEvents.php');

			if (!$_SESSION['loggedIn']){
				//If not logged in, sending session back to login.php file (security measure)
				header("Location: login.php");
				exit;
			}

			//Adding the permission-specific navigation
			echo $arch->Navigation(); 

			//Creating a database instance to work with
			require_once "PDO.DB.class.php";
			$db = new DB();

			//Retreiving table of the events the event manager has access to or all events for the admin
			echo "<h1 class='placeholder'>Events</h1>";
			echo $db->getManagedEventsAsTable($_SESSION['user']['id'], $_SESSION['user']['role']);
		?>

		<div class='buttons'>
			<!--Add new event button-->
			<button class='add' onclick="openFormAddEvent()">Add Event</button>
			<!--Update event button-->
			<button class='update' onclick="openFormUpdateEvent()">Update Event</button>
			<!--Remove event button-->
			<button class='remove' onclick="openFormRemoveEvent()">Remove Event</button>
		</div>

		<!--Form that will appear when the 'Add Event' button above is clicked and will prompt manager or admin to select from
			a set of events they have access to-->
		<div class="addEvent">
			<div class="formAddEvent" id="popupFormAddEvent">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">

				<h2 class='formHeader'>Create Event</h2>
				<p><strong>Please fill out the following information</strong></p>

				<!-- Name -->
				<div>
					<label for="setEventName">Name:</label>
					<input type="text" name="setEventName" size="30">
				</div><br/>

				<!-- Date Start -->
				<div>
					<label for="setEventDatestart">Start:</label>
					<input type="datetime-local" name="setEventDatestart">
				</div><br/>

				<!-- Date End -->
				<div>
					<label for="setEventDateend">End:</label>
					<input type="datetime-local" name="setEventDateend">
				</div><br/>

				<!-- Capacity -->
				<div>
					<label for="setEventCapacity">Capacity:</label>
					<input type="number" name="setEventCapacity" min="1" max="50000">
				</div>

				<!-- Venue -->
				<p style='margin-bottom: 0;'>Venue:</p>
				<?php 
					//Retreive radio button list of venues 
					echo $db->getVenuesAsButtons('venueSelectForNewEvent');
				?>

				<button type="submit" class="btn">Add Event</button>
				<button type="button" class="btn cancel" onclick="closeFormAddEvent()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormAddEvent() {
				document.getElementById("popupFormAddEvent").style.display = "block";
			}
			function closeFormAddEvent() {
				document.getElementById("popupFormAddEvent").style.display = "none";
			}
		</script>

		<!--Form that will appear when the 'Update Event' button above is clicked and will prompt manager or admin to select from
			a set of events they have access to-->
			<div class="updateEvent">
			<div class="formUpdateEvent" id="popupFormUpdateEvent">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">

				<h2 class='formHeader'>Managed Events</h2>
				<p><strong>Please select the event you wish to edit</strong></p>

				<?php 
					//Retreive radio button list of events manager or admin has access to
					echo $db->getManagedEventsAsButtons($_SESSION['user']['id'], $_SESSION['user']['role'], 'eventUpdate');
				?>

				<button type="submit" class="btn">Edit Event</button>
				<button type="button" class="btn cancel" onclick="closeFormUpdateEvent()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormUpdateEvent() {
				document.getElementById("popupFormUpdateEvent").style.display = "block";
			}
			function closeFormUpdateEvent() {
				document.getElementById("popupFormUpdateEvent").style.display = "none";
			}
		</script>

		<!--Form that will appear when the 'Update Event' form is submitted and will prompt manager or admin to select from
			a series of changes that can be made to the chosen event-->
			<div class="updateCertainEvent">
			<div class="formUpdateCertainEvent" id="popupFormUpdateCertainEvent">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">
					<?php 
						//Retreive selected event information to set as default in edit event form
						$event = $db->getEvent($_POST['eventUpdate']);
					?>
					<!-- Hold original event data for later comparison -->
					<input type="hidden" id="holdID" name="holdID" value="<?php echo $event['id']; ?>">
					<input type="hidden" id="origName" name="origName" value="<?php echo $event['name']; ?>">
					<input type="hidden" id="origStart" name="origStart" value="<?php echo $event['start']; ?>">
					<input type="hidden" id="origEnd" name="origEnd" value="<?php echo $event['end']; ?>">
					<input type="hidden" id="origCapacity" name="origCapacity" value="<?php echo $event['capacity']; ?>">

					<h2 class='formHeader'>Update <?php echo $event['name'] ?></h2>
					<p><strong>Make any changes to the event</strong></p>

					<!-- Name -->
					<div>
						<label for="updateEventName">Name:</label>
						<input type="text" name="updateEventName" value="<?php echo $event['name'] ?>" size="30">
					</div><br/>

					<!-- Date Start -->
					<div>
						<?php
							//Converting start date back into readable time for default
							$start = str_replace(" ", "T", $event['start']);
						?>
						<label for="updateEventDatestart">Start:</label>
						<input id="startDate" type="datetime-local" name="updateEventDatestart" value="<?php echo $start; ?>">
					</div><br/>

					<!-- Date End -->
					<div>
						<?php
							//Converting end date back into readable time for default
							$end = str_replace(" ", "T", $event['end']);
						?>
						<label for="updateEventDateend">End:</label>
						<input type="datetime-local" name="updateEventDateend" value="<?php echo $end; ?>">
					</div><br/>

					<!-- Capacity -->
					<div>
						<label for="updateEventCapacity">Capacity:</label>
						<input type="number" name="updateEventCapacity" min="1" max="50000" value="<?php echo $event['capacity']; ?>">
					</div><br/>

					<button type="submit" class="btn">Update Event</button>
					<button type="button" class="btn cancel" onclick="closeFormUpdateCertainEvent()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormUpdateCertainEvent() {
				document.getElementById("popupFormUpdateCertainEvent").style.display = "block";
			}
			function closeFormUpdateCertainEvent() {
				document.getElementById("popupFormUpdateCertainEvent").style.display = "none";
			}
		</script>

		<!--Form that will appear when the 'Remove Event' button above is clicked and will prompt manager or admin to select from
			a set of events they have access to-->
			<div class="removeEvent">
			<div class="formRemoveEvent" id="popupFormRemoveEvent">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">

				<h2 class='formHeader'>Managed Events</h2>
				<p><strong>Please select the event you wish to remove</strong></p>

				<?php 
					//Retreive radio button list of events manager or admin has access to
					echo $db->getManagedEventsAsButtons($_SESSION['user']['id'], $_SESSION['user']['role'], 'eventRemove');
				?>

				<button type="submit" class="btn">Remove Event</button>
				<button type="button" class="btn cancel" onclick="closeFormRemoveEvent()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormRemoveEvent() {
				document.getElementById("popupFormRemoveEvent").style.display = "block";
			}
			function closeFormRemoveEvent() {
				document.getElementById("popupFormRemoveEvent").style.display = "none";
			}
		</script>

		<?php 
			//add or remove an event, session, or potentially venue (if admin) and reload the page for the update
			function refreshPage(){
				echo "<script>window.location.href='eventManagerManageEvents.php'</script>";
			}

			//If the form is submitted to add a new event, validate info and if passes, add it
			if (isset($_POST['setEventName']) && isset($_POST['setEventDatestart']) && isset($_POST['setEventDateend']) && isset($_POST['setEventCapacity']) && isset($_POST['venueSelectForNewEvent'])){
				$addEventSuccess = $db->addNewEvent($_POST['setEventName'], $_POST['setEventDatestart'], $_POST['setEventDateend'], $_POST['setEventCapacity'], $_POST['venueSelectForNewEvent'], $_SESSION['user']['role'], $_SESSION['user']['id']);
				
				if ($addEventSuccess){
					refreshPage();
				}
			}

			//If the form is submitted to update an event, open update certain event form to make any changes
			if (isset($_POST['eventUpdate'])){
				echo '<script>openFormUpdateCertainEvent()</script>';
			}

			//If the form is submitted to update a certain event...
			if (isset($_POST['updateEventName']) && isset($_POST['updateEventDatestart']) && isset($_POST['updateEventDateend']) && isset($_POST['updateEventCapacity'])){
				//...and at least one of the values has changed
				if ($_POST['updateEventName'] != $_POST['origName'] || $_POST['updateEventDatestart'] != $_POST['origStart'] || $_POST['updateEventDateend'] != $_POST['origEnd'] || $_POST['updateEventCapacity'] != $_POST['origCapacity']){

					$updateEventSuccess = $db->updateEvent($_POST['holdID'], $_POST['updateEventName'], $_POST['updateEventDatestart'], $_POST['updateEventDateend'], $_POST['updateEventCapacity']);
					
					if ($updateEventSuccess){
						refreshPage();
					}
				}
			}

			//If the form is submitted to remove an event, remove it
			if (isset($_POST['eventRemove'])){
				$removeEventSuccess = $db->removeEvent($_POST['eventRemove']);
				
				if ($removeEventSuccess){
					refreshPage();
				}
			}
		?>

		<?php
			//Retreiving table of the sessions the event manager has access to or all sessions for the admin
			echo "<h1 class='placeholder_mid'>Sessions</h1>";
			echo $db->getManagedSessionsAsTable($_SESSION['user']['id'], $_SESSION['user']['role']);
		?>

		<div class='buttons'>
			<!--Add new session button-->
			<button class='add' onclick="openFormAddSession()">Add Session</button>
			<!--Update session button-->
			<button class='update' onclick="openFormUpdateSession()">Update Session</button>
			<!--Remove session button-->
			<button class='remove' onclick="openFormRemoveSession()">Remove Session</button>
		</div>

		<!--Form that will appear when the 'Add Session' button above is clicked and will prompt manager or admin to select from
			a set of event sessions they have access to-->
		<div class="addSession">
			<div class="formAddSession" id="popupFormAddSession">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">

				<h2 class='formHeader'>Create Session</h2>
				<p><strong>Please fill out the following information</strong></p>

				<!-- Name -->
				<div>
					<label for="setSessionName">Name:</label>
					<input type="text" name="setSessionName" size="30">
				</div><br/>

				<!-- Associated Event -->
				<p style='margin-bottom: 0;'>Associated Event:</p>
				<?php 
					//Retreive radio button list of events manager or admin has access to
					echo $db->getManagedEventsAsButtons($_SESSION['user']['id'], $_SESSION['user']['role'], 'sessionAdd');
				?>

				<!-- Date Start -->
				<div>
					<label for="setSessionDatestart">Start:</label>
					<input type="datetime-local" name="setSessionDatestart">
				</div><br/>

				<!-- Date End -->
				<div>
					<label for="setSessionDateend">End:</label>
					<input type="datetime-local" name="setSessionDateend">
				</div><br/>

				<!-- Capacity -->
				<div>
					<label for="setSessionCapacity">Capacity:</label>
					<input type="number" name="setSessionCapacity" min="1" max="50000">
				</div><br/>

				<button type="submit" class="btn">Add Session</button>
				<button type="button" class="btn cancel" onclick="closeFormAddSession()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormAddSession() {
				document.getElementById("popupFormAddSession").style.display = "block";
			}
			function closeFormAddSession() {
				document.getElementById("popupFormAddSession").style.display = "none";
			}
		</script>

		<!--Form that will appear when the 'Update Session' button above is clicked and will prompt manager or admin to select from
			a set of event sessions they have access to-->
			<div class="updateSession">
			<div class="formUpdateSession" id="popupFormUpdateSession">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">

				<h2 class='formHeader'>Managed Sessions</h2>
				<p><strong>Please select the session you wish to edit</strong></p>

				<?php 
					//Retreive radio button list of events manager or admin has access to
					echo $db->getManagedSessionsAsButtons($_SESSION['user']['id'], $_SESSION['user']['role'], 'sessionUpdate');
				?>

				<button type="submit" class="btn">Edit Session</button>
				<button type="button" class="btn cancel" onclick="closeFormUpdateSession()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormUpdateSession() {
				document.getElementById("popupFormUpdateSession").style.display = "block";
			}
			function closeFormUpdateSession() {
				document.getElementById("popupFormUpdateSession").style.display = "none";
			}
		</script>

		<!--Form that will appear when the 'Update Event' form is submitted and will prompt manager or admin to select from
			a series of changes that can be made to the chosen session-->
			<div class="updateCertainSession">
			<div class="formUpdateCertainSession" id="popupFormUpdateCertainSession">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">
					<?php 
						//Retreive selected session information to set as default in edit event form
						$session = $db->getSession($_POST['sessionUpdate']);
					?>
					<!-- Hold original event data for later comparison -->
					<input type="hidden" id="holdIDses" name="holdIDses" value="<?php echo $session['id']; ?>">
					<input type="hidden" id="origNameses" name="origNameses" value="<?php echo $session['name']; ?>">
					<input type="hidden" id="origStartses" name="origStartses" value="<?php echo $session['start']; ?>">
					<input type="hidden" id="origEndses" name="origEndses" value="<?php echo $session['end']; ?>">
					<input type="hidden" id="origCapacityses" name="origCapacityses" value="<?php echo $session['capacity']; ?>">

					<h2 class='formHeader'>Update <?php echo $session['name'] ?></h2>
					<p><strong>Make any changes to the session</strong></p>

					<!-- Name -->
					<div>
						<label for="updateSessionName">Name:</label>
						<input type="text" name="updateSessionName" value="<?php echo $session['name'] ?>" size="30">
					</div><br/>

					<!-- Date Start -->
					<div>
						<?php
							//Converting start date back into readable time for default
							$start = str_replace(" ", "T", $session['start']);
						?>
						<label for="updateSessionDatestart">Start:</label>
						<input type="datetime-local" name="updateSessionDatestart" value="<?php echo $start; ?>">
					</div><br/>

					<!-- Date End -->
					<div>
						<?php
							//Converting end date back into readable time for default
							$end = str_replace(" ", "T", $session['end']);
						?>
						<label for="updateSessionDateend">End:</label>
						<input type="datetime-local" name="updateSessionDateend" value="<?php echo $end; ?>">
					</div><br/>

					<!-- Capacity -->
					<div>
						<label for="updateSessionCapacity">Capacity:</label>
						<input type="number" name="updateSessionCapacity" min="1" max="50000" value="<?php echo $session['capacity']; ?>">
					</div><br/>

					<button type="submit" class="btn">Update Session</button>
					<button type="button" class="btn cancel" onclick="closeFormUpdateCertainSession()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormUpdateCertainSession() {
				document.getElementById("popupFormUpdateCertainSession").style.display = "block";
			}
			function closeFormUpdateCertainSession() {
				document.getElementById("popupFormUpdateCertainSession").style.display = "none";
			}
		</script>

		<!--Form that will appear when the 'Remove Session' button above is clicked and will prompt manager or admin to select from
			a set of event sessions they have access to-->
		<div class="removeSession">
			<div class="formRemoveSession" id="popupFormRemoveSession">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">

					<h2 class='formHeader'>Remove Session</h2>
					<p><strong>Please select the session you want to remove</strong></p>

					<?php 
						//Retreive radio button list of sessions manager or admin has access to
						echo $db->getManagedSessionsAsButtons($_SESSION['user']['id'], $_SESSION['user']['role'], 'sessionRemove');
					?>

					<button type="submit" class="btn">Remove Session</button>
					<button type="button" class="btn cancel" onclick="closeFormRemoveSession()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormRemoveSession() {
				document.getElementById("popupFormRemoveSession").style.display = "block";
			}
			function closeFormRemoveSession() {
				document.getElementById("popupFormRemoveSession").style.display = "none";
			}
		</script>

		<?php
			//If the form is submitted to add a new session, add it
			if (isset($_POST['setSessionName']) && isset($_POST['sessionAdd']) && isset($_POST['setSessionDatestart']) && isset($_POST['setSessionDateend']) && isset($_POST['setSessionCapacity'])){
				$addSessionSuccess = $db->addNewSession($_POST['setSessionName'], $_POST['sessionAdd'], $_POST['setSessionDatestart'], $_POST['setSessionDateend'], $_POST['setSessionCapacity']);
				
				if ($addSessionSuccess){
					refreshPage();
				}
			}

			//If the form is submitted to update a session, open update certain event session form to make any changes
			if (isset($_POST['sessionUpdate'])){
				echo '<script>openFormUpdateCertainSession()</script>';
			}

			//If the form is submitted to update a certain session...
			if (isset($_POST['updateSessionName']) && isset($_POST['updateSessionDatestart']) && isset($_POST['updateSessionDateend']) && isset($_POST['updateSessionCapacity'])){
				//...and at least one of the values has changed
				if ($_POST['updateSessionName'] != $_POST['origNameses'] || $_POST['updateSessionDatestart'] != $_POST['origStartses'] || $_POST['updateSessionDateend'] != $_POST['origEndses'] || $_POST['updateSessionCapacity'] != $_POST['origCapacityses']){

					$updateSessionSuccess = $db->updateSession($_POST['holdIDses'], $_POST['updateSessionName'], $_POST['updateSessionDatestart'], $_POST['updateSessionDateend'], $_POST['updateSessionCapacity']);
					
					if ($updateSessionSuccess){
						refreshPage();
					}
				}
			}

			//If the form is submitted to remove a session, remove it
			if (isset($_POST['sessionRemove'])){
				$removeSessionSuccess = $db->removeSession($_POST['sessionRemove']);
				
				if ($removeSessionSuccess){
					refreshPage();
				}
			}
		?>

		<?php
			//Retreiving table of all the venues 
			echo "<h1 class='placeholder_mid'>Venues</h1>";
			echo $db->getVenuesAsTable();

			//Venue maniuplation is only for admins and add and remove a venue buttons will be hidden from managers
			if ($_SESSION['user']['role'] == 1){
				echo "<div class='buttons'>";
				//Add new venue button
				echo "<button class='add' onclick='openFormAddVenue()'>Add Venue</button>";
				//Update venue button
				echo "<button class='update' onclick='openFormUpdateVenue()'>Update Venue</button>";
				//Remove venue button
				echo "<button class='remove' onclick='openFormRemoveVenue()'>Remove Venue</button>";
				echo "</div>";

				//Add an add venue form
				echo "<div class='addVenue'>
						<div class='formAddVenue' id='popupFormAddVenue'>
							<form action='eventManagerManageEvents.php' method='POST' class='formContainer'>
	
								<h2 class='formHeader'>Add Venue</h2>
								<p><strong>Please fill out the following information</strong></p>

								<div>
									<label for='setVenueName'>Name:</label>
									<input type='text' name='setVenueName' size='30'>
								</div><br/>

								<div>
									<label for='setVenueCapacity'>Capacity:</label>
									<input type='number' name='setVenueCapacity' min='1' max='50000'>
								</div><br/>
			
								<button type='submit' class='btn'>Add Venue</button>
								<button type='button' class='btn cancel' onclick='closeFormAddVenue()'>Cancel</button>
							</form>
						</div>
					</div>";
				
				//Add JavaScript for the add venue form
				echo "<script>
						function openFormAddVenue() {
							document.getElementById('popupFormAddVenue').style.display = 'block';
						}
						function closeFormAddVenue() {
							document.getElementById('popupFormAddVenue').style.display = 'none';
						}
					</script>";

				//Update venue form
				echo "<div class='updateVenue'>
						<div class='formUpdateVenue' id='popupFormUpdateVenue'>
							<form action='eventManagerManageEvents.php' method='POST' class='formContainer'>
	
								<h2 class='formHeader'>Managed Venue</h2>
								<p><strong>Please select the venue you wish to edit</strong></p>";

								echo $db->getVenuesAsButtons('venueUpdate');
		
				echo			"<button type='submit' class='btn'>Edit Venue</button>
								<button type='button' class='btn cancel' onclick='closeFormUpdateVenue()'>Cancel</button>
							</form>
						</div>
					</div>";
				
				//Add JavaScript for the update venue form
				echo "<script>
						function openFormUpdateVenue() {
							document.getElementById('popupFormUpdateVenue').style.display = 'block';
						}
						function closeFormUpdateVenue() {
							document.getElementById('popupFormUpdateVenue').style.display = 'none';
						}
					</script>";

				//Update certain venue form (used when 'Update Venue' form is submited)
				echo "<div class='updateCertainVenue'>
						<div class='formUpdateCertainVenue' id='popupFormUpdateCertainVenue'>
							<form action='eventManagerManageEvents.php' method='POST' class='formContainer'>";
				
								$venue = $db->getVenue($_POST['venueUpdate']);

								//hold original venue data for later comparison
								echo '<input type="hidden" id="holdIDven" name="holdIDven" value="';
								echo $venue["id"];
								echo '"><input type="hidden" id="origNameven" name="origNameven" value="';
								echo $venue["name"]; 
								echo '"><input type="hidden" id="origCapacityven" name="origCapacityven" value="';
								echo $venue["capacity"];
								echo '">';
	
								echo "<h2 class='formHeader'>Update ";
				 				echo $venue['name'];
								echo "</h2><p><strong>Make any changes to the venue</strong></p>

								<div>
									<label for='updateVenueName'>Name:</label>
									<input type='text' name='updateVenueName' value='";
									echo $venue['name'];
									echo "' size='30'>
								</div><br/>
								
								<div>
									<label for='updateVenueCapacity'>Capacity:</label>
									<input type='number' name='updateVenueCapacity' min='1' max='50000' value='";
									echo $venue['capacity'];
									echo "'>
								</div><br/>
								
								<button type='submit' class='btn'>Update Venue</button>
								<button type='button' class='btn cancel' onclick='closeFormUpdateCertainVenue()'>Cancel</button>
							</form>
						</div>
					</div>";
					
				//Add JavaScript for the update certain venue form
				echo "<script>
						function openFormUpdateCertainVenue() {
							document.getElementById('popupFormUpdateCertainVenue').style.display = 'block';
						}
						function closeFormUpdateCertainVenue() {
							document.getElementById('popupFormUpdateCertainVenue').style.display = 'none';
						}
					</script>";

				//Add a remove venue form
				echo "<div class='removeVenue'>
						<div class='formRemoveVenue' id='popupFormRemoveVenue'>
							<form action='eventManagerManageEvents.php' method='POST' class='formContainer'>
	
								<h2 class='formHeader'>Remove Venue</h2>
								<p><strong>Please select a venue to remove</strong></p>
								<p><strong>Note that removing a venue may impact events</strong></p>";

				//Retreiving a button list of venues
				echo $db->getVenuesAsButtons('selectRemoveVenue');
			
				echo "			<button type='submit' class='btn'>Remove Venue</button>
								<button type='button' class='btn cancel' onclick='closeFormRemoveVenue()'>Cancel</button>
							</form>
						</div>
					</div>";	
					
				//Add JavaScript for the remove venue form
				echo "<script>
						function openFormRemoveVenue() {
							document.getElementById('popupFormRemoveVenue').style.display = 'block';
						}
						function closeFormRemoveVenue() {
							document.getElementById('popupFormRemoveVenue').style.display = 'none';
						}
					</script>";
				
				//If the form is submitted to add a new venue, add it
				if (isset($_POST['setVenueName']) && isset($_POST['setVenueCapacity'])){
					$addVenueSuccess = $db->addNewVenue($_POST['setVenueName'], $_POST['setVenueCapacity']);
					
					if ($addVenueSuccess){
						refreshPage();
					}
				}

				//If the form is submitted to update a venue, open update certain venue form to make any changes
				if (isset($_POST['venueUpdate'])){
					echo '<script>openFormUpdateCertainVenue()</script>';
				}

				//If the form is submitted to update a certain venue...
				if (isset($_POST['updateVenueName']) && isset($_POST['updateVenueCapacity'])){
					//...and at least one of the values has changed
					if ($_POST['updateVenueName'] != $_POST['origNameven'] || $_POST['updateVenueCapacity'] != $_POST['origCapacityven']){

						$updateVenueSuccess = $db->updateVenue($_POST['holdIDven'], $_POST['updateVenueName'], $_POST['updateVenueCapacity']);
						
						if ($updateVenueSuccess){
							refreshPage();
						}
					}
				}

				//If the form is submitted to remove a venue, remove all dependencies and it
				if (isset($_POST['selectRemoveVenue'])){
					$removeVenueSuccess = $db->removeVenue($_POST['selectRemoveVenue']);
					
					if ($removeVenueSuccess){
						refreshPage();
					}
				}
			}
		?>

		<?php
			//Retreiving table of attending users the event manager has access to or all events for the admin
			echo "<h1 class='placeholder'>Attendees</h1>";
			echo $db->getManagedAttendeesAsTable($_SESSION['user']['id'], $_SESSION['user']['role']);
		?>

		<div class='buttons'>
			<!--Add new session attendee (and potentially event) button-->
			<button class='add' onclick="openFormAddManagedAttendeeToSession()">Add Attendee</button>
			<!--Remove session attendee (and potentially event) button-->
			<button class='remove' onclick="openFormRemoveManagedAttendeeFromSession()">Remove Attendee</button>
		</div>

		<!--Form that will appear when the 'Add Attendee' button above is clicked and will prompt manager or admin to select from
			a set of attendees and event sessions they have access to-->
		<div class="addManagedAttendeeToSession">
			<div class="formAddManagedAttendeeToSession" id="popupFormAddManagedAttendeeToSession">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">

				<h2 class='formHeader'>Add Attendee to Session</h2>
				<p><strong>Please select an attendee and a session</strong></p>

				<!-- Attendee -->
				<p style='margin-bottom: 0;'>Attendee:</p>
				<?php 
					//Retreive radio button list of attendees manager or admin has access to
					echo $db->getUsersAsButtons('attendeeSessionAdd');
				?>

				<!-- Session -->
				<p style='margin-bottom: 0;'>Session:</p>
				<?php 
					//Retreive radio button list of events manager or admin has access to
					echo $db->getManagedSessionsAsButtons($_SESSION['user']['id'], $_SESSION['user']['role'], 'attendeeSessionAdd2');
				?>

				<button type="submit" class="btn">Add Attendee to Session</button>
				<button type="button" class="btn cancel" onclick="closeFormAddManagedAttendeeToSession()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormAddManagedAttendeeToSession() {
				document.getElementById("popupFormAddManagedAttendeeToSession").style.display = "block";
			}
			function closeFormAddManagedAttendeeToSession() {
				document.getElementById("popupFormAddManagedAttendeeToSession").style.display = "none";
			}
		</script>

		<!--Form that will appear when the 'Remove Attendee' button above is clicked and will prompt manager or admin to select from
			a set of attendees and event sessions they have access to-->
			<div class="removeManagedAttendeeFromSession">
			<div class="formRemoveManagedAttendeeFromSession" id="popupFormRemoveManagedAttendeeFromSession">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">

				<h2 class='formHeader'>Remove Attendee from Session</h2>
				<p><strong>Please select an attendee</strong></p>

				<!-- Attendee -->
				<p style='margin-bottom: 0;'>Attendee:</p>
				<?php 
					//Retreive radio button list of attendees manager or admin has access to
					echo $db->getUsersAsButtons('attendeeSessionRemove');
				?>

				<button type="submit" class="btn">Select Attendee</button>
				<button type="button" class="btn cancel" onclick="closeFormRemoveManagedAttendeeFromSession()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormRemoveManagedAttendeeFromSession() {
				document.getElementById("popupFormRemoveManagedAttendeeFromSession").style.display = "block";
			}
			function closeFormRemoveManagedAttendeeFromSession() {
				document.getElementById("popupFormRemoveManagedAttendeeFromSession").style.display = "none";
			}
		</script>

		<!--Form that will appear when the 'Remove Attendee' button above is submitted and will prompt manager or admin to select from
			a set of sessions to remove specified attendee from-->
			<div class="removeCertainManagedAttendeeFromSession">
			<div class="formRemoveCertainManagedAttendeeFromSession" id="popupFormRemoveCertainManagedAttendeeFromSession">
				<form action="eventManagerManageEvents.php" method='POST' class="formContainer">
				<input type="hidden" id="attendeeHold" name="attendeeHold" value="<?php echo $_POST['attendeeSessionRemove']; ?>">

				<h2 class='formHeader'>Remove Attendee from Session</h2>
				<p><strong>Please select a session</strong></p>

				<!-- Session -->
				<?php 
					//Retreive radio button list of sessions that attendee is registered for
					echo $db->getAttendeeRegisteredSessionsThroughManagementAsButtons($_SESSION['user']['id'], $_POST['attendeeSessionRemove'], $_SESSION['user']['role'], 'adminRemoveAttendeeFromSession');
				?>

				<button type="submit" class="btn">Remove Attendee from Session</button>
				<button type="button" class="btn cancel" onclick="closeFormRemoveCertainManagedAttendeeFromSession()">Cancel</button>
				</form>
			</div>
		</div>

		<script>
			//JavaScript functions to open and close form as different buttons are clicked
			function openFormRemoveCertainManagedAttendeeFromSession() {
				document.getElementById("popupFormRemoveCertainManagedAttendeeFromSession").style.display = "block";
			}
			function closeFormRemoveCertainManagedAttendeeFromSession() {
				document.getElementById("popupFormRemoveCertainManagedAttendeeFromSession").style.display = "none";
			}
		</script>

		<?php
			//If the form is submitted to add an attendee to a session, add it
			if (isset($_POST['attendeeSessionAdd']) && isset($_POST['attendeeSessionAdd2'])){
				$addManagedAttendeeToSessionSuccess = $db->addUserToSession($_POST['attendeeSessionAdd'], $_POST['attendeeSessionAdd2']);
				
				if ($addManagedAttendeeToSessionSuccess){
					refreshPage();
				}
			}

			//If the form is submitted to remove a session, remove it
			if (isset($_POST['attendeeSessionRemove'])){
				echo '<script>openFormRemoveCertainManagedAttendeeFromSession()</script>';
			}

			//If the form is submitted to with a specific attendee and session to remove a session, remove it
			if (isset($_POST['attendeeHold']) && isset($_POST['adminRemoveAttendeeFromSession'])){
				$removeManagedAttendeeFromSessionSuccess = $db->removeUserFromSession($_POST['attendeeHold'], $_POST['adminRemoveAttendeeFromSession']);

				if ($removeManagedAttendeeFromSessionSuccess){
					refreshPage();
				}
			}

			//Adding the footer
			echo $arch->Footer(); 
		?>
    </body>
</html>