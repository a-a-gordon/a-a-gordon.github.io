<?php
    //Alexis Gordon
    //ISTE 341 Project 1 DB Connection
    //10-18-2021

    class DB {
        private $conn;

        /*
            Generates and sets up database connection
            Return: None
        */
        function __construct(){

            try {
                $this->conn = new PDO("mysql:host={$_SERVER['DB_SERVER']};dbname={$_SERVER['DB']}", 
                                    $_SERVER['DB_USER'], $_SERVER['DB_PASSWORD']);
            } catch (PDOException $pe){
                //can log message
                echo $pe->getMessage();
                die("Bad Database");
            }
        }




        /*
            Checks if username and password combination is valid in the db. Utilizes the password_verify() method in order to compare inputed password against hashed db password
                Used by the 'Login' page to confirm login
            Return: an array called "user" consisting of the userID, username, and user role of a user matching the credentials and -1 if there does not exist a user with those credentials
        */
        function isValidCredentials($username, $password){
            //Default set to no match
            $user = ['id'=>-1];

            try {
                if ($stmt = $this->conn->prepare("SELECT idattendee, name, password, role FROM attendee")){
                    //Executing
                    $stmt->execute();

                    //Retrieving data
                    while ($row = $stmt->fetch()){
                        if ($row['name']==$username && password_verify($password, $row['password'])){
                            //setting user variable to hold the user id, name and role
                            $user = ['id'=>$row['idattendee'], 'name'=>$row['name'], 'role'=>$row['role']];
                            break;
                        }
                    }
                }
                return $user;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }




        /*
            Retreives all the events in the db to be used on the main 'Event' page
            Return: an associated array called '$data' that contains information about all events
        */
        function getAllEvents(){
            $data = array();

            try {
                if ($stmt = $this->conn->prepare('SELECT event.name, venue.name, event.datestart, event.dateend, event.numberallowed, idevent FROM event JOIN venue ON venue.idvenue=event.venue ORDER BY event.name')){
                    //Executing 
                    $stmt->execute();

                    //Retrieving data
                    while ($row = $stmt->fetch()){
                        $data[] = $row;
                    }
                }
                return $data;

            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        } 




        /*
            Retreives all the sessions for a given event db to be used on the main 'Event' page
            Return: an associated array called '$data' that contains information about all the associated sessions
        */
        function getAllSessions($eventID){
            $data = array();

            try {
                if ($stmt = $this->conn->prepare('SELECT session.name, startdate, enddate, session.numberallowed FROM session WHERE event= :eventID ORDER BY session.name')){
                    //Executing 
                    $stmt->execute(array(":eventID"=>$eventID));

                    //Retrieving data
                    while ($row = $stmt->fetch()){
                        $data[] = $row;
                    }
                }
                return $data;

            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        } 




        /*
            Utilizes the getAllEvents and getAllSessions functions to generate a visual of the events and sessions for the main 'Event' page
            Return: a string called '$bigString' that contains an event accordion with subtables for the sessions
        */
        function getAllEventSessionsAsAccordionTable(){
            $events = $this->getAllEvents();
            if (count($events) > 0){
                //If there is data 
                $bigString = "";

                //Adding events as buttons (to form accordion)
                foreach($events as $row){
                    $bigString .= "<button class='accordion'><h2>{$row[0]}</h2></button>";

                    //Adding session table headers
                    $sessions = $this->getAllSessions($row['idevent']);
                    $bigString .= "<div class='panel'>
                                    <h3>{$row[0]} Sessions</h3>
                                    <table><thread>
                                        <tr>
                                            <th>Name</th>
                                            <th>Venue</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Capacity</th>
                                        </tr></thread><tbody>";
                    
                    //Adding session entries as subtables for each associated event
                    foreach($sessions as $subrow){
                        $bigString .= "<tr>
                                            <td>{$subrow['name']}</td>
                                            <td> {$row[1]}</td>
                                            <td>{$subrow['startdate']}</td>
                                            <td>{$subrow['enddate']}</td>
                                            <td>{$subrow['numberallowed']}</td>
                                        </tr>";
                    }

                    //Closing table after all data has been entered into it
                    $bigString .= "</tbody></table></div>";
                }             
            } else {
                //If there is no data for the table, instead of printing an empty table, print an h2 tag
                $bigString = "<h2>No sessions exist!</h2>";
            }

            return $bigString;
        }




        /*
            Retreives the sessions the logged in attendee is registered for to display on the 'Event Registration' page
            Return: an associative array called '$data' that contains the information for the sessions the currently logged in user is registered for
        */
        function getAttendeeRegisteredSessions($attendeeID){
            $data = array();

            try {
                if ($stmt = $this->conn->prepare('SELECT idsession, session.name, event.name, venue.name, startdate, enddate, session.numberallowed FROM session JOIN event ON session.event=event.idevent JOIN venue ON event.venue=venue.idvenue JOIN attendee_session ON session.idsession=attendee_session.session WHERE attendee_session.attendee= :attendeeID ORDER BY session.name')){
                    //Executing 
                    $stmt->execute(array(":attendeeID"=>$attendeeID));

                    //Retrieving data
                    while ($row = $stmt->fetch()){
                        $data[] = $row;
                    }
                }
                return $data;

            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        } 




        /*
            Retreives the sessions the logged in attendee is registered for to display on the 'Event Registration' page
            Return: an associative array called '$data' that contains the information for the sessions the currently logged in user is registered for
        */
        function getAttendeeRegisteredSessionsThroughManagement($managerID, $attendeeID, $role){
            $data = array();
            $query = 'SELECT attendee_session.attendee, attendee.name, attendee_session.session, session.name, idevent, event.name FROM attendee_session JOIN attendee ON idattendee=attendee_session.attendee JOIN session ON idsession=attendee_session.session JOIN event ON session.event=idevent ';
            if ($role==2){
                $query .= "JOIN manager_event ON manager_event.event=idevent WHERE manager= :managerID AND attendee_session.attendee= :attendeeID ORDER BY session.name";
            } else {
                $query .= "WHERE attendee_session.attendee= :attendeeID ORDER BY session.name";
            }
            $query .= "";
            try {
                if ($stmt = $this->conn->prepare($query)){
                    //Executing 
                    if ($role==2){
                        $stmt->execute(array(":managerID"=>$managerID, ":attendeeID"=>$attendeeID));
                    } else {
                        $stmt->execute(array(":attendeeID"=>$attendeeID));
                    }
                    
                    //Retrieving data
                    while ($row = $stmt->fetch()){
                        $data[] = $row;
                    }
                }
                return $data;

            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        } 




        /*
            Retreives the sessions the logged in attendee is registered for to display on the 'Event Registration' page
            Return: an associative array called '$data' that contains the information for the sessions the currently logged in user is registered for
        */
        function getAttendeeRegisteredSessionsThroughManagementAsButtons($managerID, $attendeeID, $role, $case){
            try {
                $sessions = $this->getAttendeeRegisteredSessionsThroughManagement($managerID, $attendeeID, $role);
                if (count($sessions) > 0){
                    //If there is data for the table
                    //Opening a div
                    $bigString = "<div class='potentialSessions'>";
    
                    //Adding session entries as buttons
                    foreach($sessions as $row){
                        $bigString .= "<input type='radio' name='{$case}' value='{$row[2]}'>{$row[3]}<br>";
                    }  
                    
                    //Closing the div once the data has been added
                    $bigString .= "</div><br/>";
    
                } else {
                    //If there is no data for the table, instead of printing an empty table, print an h2 tag
                    $bigString = "<h2>No sessions exist!</h2>";
                }
                return $bigString;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        } 




        /*
            Utilizes the data from the getAttendeeRegisteredSessions() function to display a table on the 'Event Registration' page
            Return: a string called '$bigString' that contains a table of the sessions the user is currently registered for
        */
        function getAttendeeRegisteredSessionsAsTable($attendeeID){
            $sessions = $this->getAttendeeRegisteredSessions($attendeeID);

            if (count($sessions) > 0){
                //If there is data for the table
                //Opening a table and setting column headers
                $bigString = "<table><thread>
                                <tr>
                                    <th>Session</th>
                                    <th>Event</th>
                                    <th>Venue</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Capacity</th>
                                </tr></thread><tbody>";

                //Adding event entries to table
                foreach($sessions as $row){
                    $bigString .= "<tr>
                                    <td>{$row[1]}</td>
                                    <td> {$row[2]}</td>
                                    <td> {$row[3]}</td>
                                    <td>{$row['startdate']}</td>
                                    <td>{$row['enddate']}</td>
                                    <td>{$row['numberallowed']}</td>
                                </tr>";
                }      
                
                //Closing table after all data has been entered into it
                $bigString .= "</tbody></table>";
            } else {
                //If there is no data for the table, instead of printing an empty table, print an h2 tag
                $bigString = "<h2>No sessions exist!</h2>";
            }

            return $bigString;
        }




        /*
            Retreives the sessions the logged in attendee is not registered for to display on a pop-up to add a session on the 'Event Registration' page
            Return: an associative array called '$data' that contains the session names and ids the currently logged in user is not registered for
        */
        function getAttendeeNotRegisteredSessions($attendeeID){
            $data = array();

            try {
                if ($stmt = $this->conn->prepare('SELECT DISTINCT session.name, idsession FROM session JOIN attendee_session WHERE idsession NOT IN (SELECT session FROM attendee_session WHERE attendee= :attendeeID) ORDER BY session.name')){
                    //Executing 
                    $stmt->execute(array(":attendeeID"=>$attendeeID));

                    //Retrieving data
                    while ($row = $stmt->fetch()){
                        $data[] = $row;
                    }
                }
                return $data;

            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        } 




        /*
            Utilizes the data from the getAttendeeNotRegisteredSessions() or getAtendeeRegisteredSessions() functions to add to a pop-up 'Add Session' or 'Remove Session' form on the 'Event Registration' page
            Return: a string called '$bigString' that contains a div of input elements of the session names and ids the user is or is not currently registered for
        */
        function getAttendeePotentialSessionsAsButtons($attendeeID, $case){
            if($case=='sessionUserAdd'){
                $sessions = $this->getAttendeeNotRegisteredSessions($attendeeID);
            } elseif ($case=='sessionUserRemove' || $case=='adminRemoveAttendeeFromSession'){
                $sessions = $this->getAttendeeRegisteredSessions($attendeeID);
            } else {
                $sessions = [];
            }

            if (count($sessions) > 0){
                //If there is data for the table
                //Opening a table and setting column headers
                $bigString = "<div class='potentialSessions'>";

                //Adding event entries to table
                foreach($sessions as $row){
                    if($case=='sessionUserAdd'){
                        $bigString .= "<input type='radio' name='sessionUserAdd' value='{$row['idsession']}'>{$row['name']}<br>";
                    } elseif ($case=='sessionUserRemove' || $case=='adminRemoveAttendeeFromSession'){
                        $bigString .= "<input type='radio' name='{$case}' value='{$row['idsession']}'>{$row[1]}<br>";
                    } 
                }  
                
                //Closing the div once the data has been added
                $bigString .= "</div><br/>";

            } else {
                //If there is no data for the table, instead of printing an empty table, print an h2 tag
                $bigString = "<h2>No sessions exist!</h2>";
            }
            return $bigString;
        }




        /*
            After a user submits that they want to add a session, function takes in user ID and session ID in order to register user for said session, and associated event if not already
            Return: a boolean value named '$successful' and returns True if registers user for that session and False if it fails to do so
        */
        function addUserToSession($userID, $sessionID){
            //Default in case following if loop fails
            $successful = False;

            try {
                if ($stmt = $this->conn->prepare("INSERT INTO attendee_session (session, attendee) VALUES (:sessionID, :attendeeID)")){
                    //Executing 
                    $stmt->execute(array(":sessionID"=>$sessionID, ":attendeeID"=>$userID));

                    $event = 0;

                    //Retreiving the event id session is linked to session
                    if ($stmt2 = $this->conn->prepare("SELECT idevent FROM event JOIN session ON idevent=session.event WHERE idsession= :sessionID")){
                        //Executing 
                        $stmt2->execute(array(":sessionID"=>$sessionID));

                        while ($row = $stmt2->fetch()){
                            $event = $row['idevent'];
                        } 

                        //Using newfound eventID to add attendee to the event associated to the session if not already
                        if ($stmt3 = $this->conn->prepare("INSERT INTO attendee_event (event, attendee, paid) VALUES (:eventID, :attendeeID, 1)")){
                            //Executing 
                            $stmt3->execute(array(":eventID"=>$event, ":attendeeID"=>$userID));

                            //Setting successful to true since user registered for new session and was potentially added to the associated event
                            $successful = True;
                        } 
                    }
                }
                return $successful;

            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            After a user submits that they want to remove a session, function takes in user ID and session ID in order to deregister user for said session, and associated event if it was the only one linked
            Return: a boolean value named '$numRows' and returns True if deregisters user for that session and False if it fails to do so
        */
        function removeUserFromSession($userID, $sessionID){
            $numRows = False;

            try {
                //Deregistering an attendee with id userID from a session with id sessionID
                if ($stmt = $this->conn->prepare("DELETE FROM attendee_session WHERE attendee = :attendeeID AND session = :sessionID")){
                    //Executing 
                    $stmt->execute(array(":attendeeID"=>$userID, ":sessionID"=>$sessionID));

                    //Setting boolean to whether row was deleted or not
                    $numRows = $stmt->rowCount() > 0;    

                    $event = 0;
                    //Retreiving the event id session is linked to session
                    if ($stmt2 = $this->conn->prepare("SELECT event FROM session WHERE idsession= :sessionID2")){
                        //Executing 
                        $stmt2->execute(array(":sessionID2"=>$sessionID));

                        while ($row = $stmt2->fetch()){
                            $event = $row['event'];
                        } 

                        //Using newfound eventID, figure out if that removed session was the only one within an event an attendee was registered for
                        if ($stmt3 = $this->conn->prepare("SELECT event, idsession, attendee FROM attendee_session JOIN session ON idsession=attendee_session.session WHERE event= :eventID AND attendee= :attendeeID2")){
                            //Executing 
                            $stmt3->execute(array(":eventID"=>$event, ":attendeeID2"=>$userID));

                            if ($stmt3->rowCount() == 0){
                                //If that deleted session was the only session the attendee was registered for in that event, it must be removed from the attendee_event table
                                if ($stmt4 = $this->conn->prepare("DELETE FROM attendee_event WHERE attendee = :attendeeID3 AND event = :eventID2")){
                                    //Executing 
                                    $stmt4->execute(array(":attendeeID3"=>$userID, ":eventID2"=>$event));
                                }
                            }
                        } 
                    }
                }
                return $numRows;

            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Retreives all the events in the db (if an admin) or the managed events (if an event manager) to be used on the 'Manage Events' page
            Return: an associated array called '$data' that contains information about all managed events for a given user based on ID and role
        */        
        function getManagedEvents($userID, $role){
            if ($role == 2){
                //Attendee is an event manager and needs permission to their specific events
                $query = "SELECT event.name, venue.name, event.datestart, event.dateend, event.numberallowed, idevent FROM event JOIN venue ON venue.idvenue=event.venue JOIN manager_event ON manager_event.event=idevent WHERE manager= :managerID ORDER BY event.name";
            } elseif ($role == 1){
                //Attendee is an admin and needs permission to all events
                $query = "SELECT event.name, venue.name, event.datestart, event.dateend, event.numberallowed, idevent FROM event JOIN venue ON venue.idvenue=event.venue ORDER BY event.name";
            } else {
                return;
            }

            $data = array();

            try {
                if ($stmt = $this->conn->prepare($query)){
                    if ($role == 2){
                        //Attendee is an event manager and needs permission to their specific events
                        $stmt->execute(array(":managerID"=>$userID));
                    } else {
                        //Attendee is an admin and needs permission to all events
                        $stmt->execute();
                    }
    
                    if ($stmt->rowCount() > 0){
                        //If we have data
                        while ($row = $stmt->fetch()){
                            //Appending new row entry with associative data array
                            $data[] = $row;
                        } 
                    } 
                } 
                return $data;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }


        /*
            Retreives all the managed events in the db as a table to be used on the 'Manage Events' page
            Return: a string called 'bigString' that contains an HTML table of managed events
        */
        function getManagedEventsAsTable($userID, $role){
            $events = $this->getManagedEvents($userID, $role);
            if (count($events) > 0){
                //If there is data 
                $bigString = "<table><thread>
                                <tr>
                                    <th>Name</th>
                                    <th>Venue</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Capacity</th>
                                </tr>
                            </thread><tbody>";

                //Adding events as to table
                foreach($events as $row){
                    $bigString .= "<tr>
                                        <td> {$row[0]}</td>
                                        <td> {$row[1]}</td>
                                        <td>{$row['datestart']}</td>
                                        <td>{$row['dateend']}</td>
                                        <td>{$row['numberallowed']}</td>
                                    </tr>";

                }  
                $bigString .= "</tbody></table>";
            } else {
                //If there is no data for the table, instead of printing an empty table, print an h2 tag
                $bigString = "<h2>You manage no events!</h2>";
            }
            return $bigString;
        }  



        /*
            Utilizes the data from the getManagedEvents() function to add to a pop-up 'Add Event' form on the 'Manage Events' page
            Return: a string called '$bigString' that contains a div of input elements of the event names and ids the manager or admin has access to
        */
        function getManagedEventsAsButtons($userID, $role, $case){
            if($role==2 || $role==1){
                $events = $this->getManagedEvents($userID, $role);
            } else {
                $events = [];
            }

            if (count($events) > 0){
                //If there is data 
                //Opening a div and setting class
                $bigString = "<div class='potentialEvents'>";

                //Adding event entries to buttons
                foreach($events as $row){
                    $bigString .= "<input type='radio' name='{$case}' value='{$row['idevent']}'>{$row[0]}<br>"; 
                }  
                
                //Closing the div once the data has been added
                $bigString .= "</div><br/>";

            } else {
                //If there is no data, print an h2 tag
                $bigString = "<h2>No managed events exist!</h2>";
            }

            return $bigString;
        }



        /*
            Retreives all the event sessions in the db (if an admin) or the managed event sessions (if an event manager) to be used on the 'Manage Events' page
            Return: an associated array called '$data' that contains information about all managed event sessions for a given user based on ID and role
        */  
        function getManagedSessions($userID, $role){
            $query = "SELECT idsession, session.name, event.name, startdate, enddate, session.numberallowed FROM session JOIN event ON idevent=session.event";
            if ($role == 2){
                //Attendee is an event manager and needs permission to their specific sessions
                $query .= " JOIN manager_event ON manager_event.event=idevent WHERE manager= :managerID";
            } 
            $query .= " ORDER BY session.name";

            $data = array();

            try {
                if ($stmt = $this->conn->prepare($query)){
                    if ($role == 2){
                        //Attendee is an event manager and needs permission to their specific sessions
                        $stmt->execute(array(":managerID"=>$userID));
                    } else {
                        //Attendee is an admin and needs permission to all sessions
                        $stmt->execute();
                    }
                    
                    if ($stmt->rowCount() > 0){
                        //If we have data
                        while ($row = $stmt->fetch()){
                            //Appending new row entry with associative data array
                            $data[] = $row;
                        } 
                    } 
                } 
                return $data;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Retreives all the managed event sessions in the db as a table to be used on the 'Manage Events' page
            Return: a string called 'bigString' that contains an HTML table of managed event sessions
        */
        function getManagedSessionsAsTable($userID, $role){
            $sessions = $this->getManagedSessions($userID, $role);
            if (count($sessions) > 0){
                //If there is data 
                $bigString = "<table><thread>
                                <tr>
                                    <th>Name</th>
                                    <th>Event</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Capacity</th>
                                </tr></thread><tbody>";

                //Adding events as to table
                foreach($sessions as $row){
                    $bigString .= "<tr>
                                        <td>{$row[1]}</td>
                                        <td> {$row[2]}</td>
                                        <td>{$row['startdate']}</td>
                                        <td>{$row['enddate']}</td>
                                        <td>{$row['numberallowed']}</td>
                                    </tr>";

                }  
                $bigString .= "</tbody></table>";
            } else {
                //If there is no data for the table, instead of printing an empty table, print an h2 tag
                $bigString = "<h2>You manage no sessions!</h2>";
            }
            return $bigString;
        }  



        /*
            Utilizes the data from the getManagedSessions() function to add to a form on the 'Manage Events' page
            Return: a string called '$bigString' that contains a div of input elements of the event sessions' names and ids the manager or admin has access to
        */
        function getManagedSessionsAsButtons($userID, $role, $case){
            $sessions = $this->getManagedSessions($userID, $role);

            $bigString = "";
            if (count($sessions) > 0){
                //If there is data 
                //Opening a div and setting class
                $bigString .= "<div class='potentialEvents'>";

                //Adding session entries to buttons
                foreach($sessions as $row){
                    $bigString .= "<input type='radio' name='{$case}' value='{$row['idsession']}'>{$row[1]}<br>";
                }  
                
                //Closing the div once the data has been added
                $bigString .= "</div><br/>";

            } else {
                //If there is no data, print an h2 tag
                $bigString = "<h2>No managed sessions exist!</h2>";
            }

            return $bigString;
        }



        /*
            Get the venues
            Return: an associated array called '$data' that contains information about all venues
        */
        function getVenues(){
            $data = array();

            try {
                if ($stmt = $this->conn->prepare('SELECT * FROM venue ORDER BY name')){
                    //Executing 
                    $stmt->execute();

                    //Retrieving data
                    while ($row = $stmt->fetch()){
                        $data[] = $row;
                    }
                }
                return $data;

            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Retreives all the venues in the db via getVenues() function and create a table to be used on the 'Manage Events' page
            Return: a string called 'bigString' that contains an HTML table of venues
        */
        function getVenuesAsTable(){
            $venues = $this->getVenues();

            if (count($venues) > 0){
                //If there is data 
                $bigString = "<table><thread>
                                <tr>
                                    <th>Name</th>
                                    <th>Capacity</th>
                                </tr></thread><tbody>";

                //Adding events as to table
                foreach($venues as $row){
                    $bigString .= "<tr>
                                        <td>{$row['name']}</td>
                                        <td> {$row['capacity']}</td>
                                    </tr>";

                }  
                $bigString .= "</tbody></table>";
            } else {
                //If there is no data for the table, instead of printing an empty table, print an h2 tag
                $bigString = "<h2>There are no venues!</h2>";
            }
            return $bigString;
        }



        /*
            Utilizes the data from the getVenues() function to add to a form on the 'Manage Events' page
            Return: a string called '$bigString' that contains a div of input elements of the venues' names and ids the admin has access to
        */
        function getVenuesAsButtons($case){
            $venues = $this->getVenues();

            $bigString = "";
            if (count($venues) > 0){
                //If there is data 
                //Opening a div and setting class
                $bigString .= "<div class='potentialVenues'>";

                //Adding session entries to buttons
                foreach($venues as $row){
                    $bigString .= "<input type='radio' name='{$case}' value='{$row['idvenue']}'>{$row['name']}<br>";
                }  
                
                //Closing the div once the data has been added
                $bigString .= "</div><br/>";

            } else {
                //If there is no data, print an h2 tag
                $bigString = "<h2>No venues exist!</h2>";
            }

            return $bigString;
        }




        /*
            Get the list of attendees alongside their registered session (and corresponding events)
            Return: an associated array called '$data' that contains information about all attending users
        */
        function getManagedAttendees($id, $role){
            $data = array();
            $query = "SELECT attendee_session.attendee, attendee.name, attendee_session.session, session.name, idevent, event.name FROM attendee_session JOIN attendee ON idattendee=attendee_session.attendee JOIN session ON idsession=attendee_session.session JOIN event ON session.event=idevent ";
            if ($role==2){
                $query .= "JOIN manager_event ON manager_event.event=idevent WHERE manager= :managerID ";
            }
            $query .= "ORDER BY attendee.name";

            try {
                if ($stmt = $this->conn->prepare($query)){
                    //Executing 
                    if ($role==2){
                        $stmt->execute(array(":managerID"=>$id));
                    } else {
                        $stmt->execute();
                    }

                    //Retrieving data
                    while ($row = $stmt->fetch()){
                        $data[] = $row;
                    }
                }
                return $data;

            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Retreives all the attending users in the db via getManagedAttendees() function and create a table to be used on the 'Manage Events' page
            Return: a string called 'bigString' that contains an HTML table of attending users
        */
        function getManagedAttendeesAsTable($id, $role){
            $users = $this->getManagedAttendees($id, $role);
            if (count($users) > 0){
                //If there is data 
                $bigString = "<table><thread>
                                <tr>
                                    <th>Name</th>
                                    <th>Session</th>
                                    <th>Event</th>
                                </tr></thread><tbody>";

                //Adding attending users as to table
                foreach($users as $row){
                    $bigString .= "<tr>
                                        <td>{$row[1]}</td>
                                        <td>{$row[3]}</td>
                                        <td> {$row[5]}</td>
                                    </tr>";

                }  
                $bigString .= "</tbody></table>";
            } else {
                //If there is no data for the table, instead of printing an empty table, print an h2 tag
                $bigString = "<h2>There are no attending users!</h2>";
            }
            return $bigString;
        }



        /*
            Utilizes the data from the getManagedAttendees() function to add to a form on the 'Manage Events' page
            Return: a string called '$bigString' that contains a div of input elements of the attendees' names and ids the manager or admin has access to
        */
        function getManagedAttendeesAsButtons($userID, $role, $case){
            $sessions = $this->getManagedAttendees($userID, $role);

            $bigString = "";
            if (count($sessions) > 0){
                //If there is data 
                //Opening a div and setting class
                $bigString .= "<div class='potentialEvents'>";

                //Adding session entries to buttons
                foreach($sessions as $row){
                    $bigString .= "<input type='radio' name='{$case}' value='{$row['idsession']}'>{$row[1]}<br>";
                }  
                
                //Closing the div once the data has been added
                $bigString .= "</div><br/>";

            } else {
                //If there is no data, print an h2 tag
                $bigString = "<h2>No managed sessions exist!</h2>";
            }

            return $bigString;
        }



        /*
            Takes in a new event name, start and end dates, capacity, venueID, and user information to add a new event on the 'Manage Events' page
            Return: a boolean value, True if event successfully added and False if not
        */
        function addNewEvent($eventName, $startdate, $enddate, $capacity, $venueID, $role, $userID){
            //Initially setting success to False
            $success = False;

            //Validation of information is unnecessary as form forced types for date and capacity and event name and venue can be alphanumeric
            //Sanitizing text information sent through form (rest is clear as is)
            $sanitizedEventName = filter_var(stripslashes(trim($eventName)), FILTER_SANITIZE_STRING);

            //Converting $startdate to proper format
            $startdate = substr($startdate, 0, 10) . " " . substr($startdate, 11);
            if (strlen($startdate) == 16){
                $startdate .= ":00";
            }
            $startdate = date($startdate);

            //Converting $enddate to proper format
            $enddate = substr($enddate, 0, 10) . " " . substr($enddate, 11);
            if (strlen($enddate) == 16){
                $enddate .= ":00";
            }
            $enddate = date($enddate);

            try {
                //Adding the new event
                if ($stmt = $this->conn->prepare("INSERT INTO event (name, datestart, dateend, numberallowed, venue) VALUES (:eventName, :dateStart, :dateEnd, :capacity, :venue)")){
                    //Executing
                    $stmt->execute(array(":eventName"=>$sanitizedEventName, ":dateStart"=>$startdate, ":dateEnd"=>$enddate, ":capacity"=>$capacity, ":venue"=>$venueID));

                    //Returning the new event id in order to add it to the manager_event table
                    //$eventID = $stmt->lastInsertId();
                    $stmtID = $this->conn->query("SELECT LAST_INSERT_ID()");
                    $eventID = $stmtID->fetchColumn();

                    //If no event was added since event name already exists, grab that id instead
                    if ($eventID == 0){
                        if ($stmt2 = $this->conn->prepare("SELECT idevent FROM event WHERE name= :eventName2")){
                            $stmt2->execute(array(":eventName2"=>$eventName));

                            while ($row = $stmt2->fetch()){
                                $eventID = $row['idevent'];
                            } 
                        }
                    }

                    //Adding the event to the event manager list if the role is an event manager
                    if ($role == 2){
                        if ($stmt3 = $this->conn->prepare("INSERT INTO manager_event (event, manager) VALUES (:eventID, :managerID)")){
                            //Executing
                            $stmt3->execute(array(":eventID"=>$eventID, ":managerID"=>$userID));
                        }
                    }
                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Takes the eventID and retreives that events given information. Utilized to update an event
            Return: a associated array called 'data' containing all information for a given event
        */
        function getEvent($eventID){
            $event = [];

            try {
                if ($stmt = $this->conn->prepare("SELECT name, datestart, dateend, numberallowed FROM event WHERE idevent= :eventID")){
                    $stmt->execute(array(":eventID"=>$eventID));
                    $event['id'] = $eventID;
                    while ($row = $stmt->fetch()){
                        $event['name'] = $row['name'];
                        $event['start'] = $row['datestart'];
                        $event['end'] = $row['dateend'];
                        $event['capacity'] = $row['numberallowed'];
                    } 
                }
                return $event;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Updates the chosen event
            Return: a boolean value, True if event successfully updated and False if not
        */
        function updateEvent($id, $name, $start, $end, $capacity){
            //Initially setting success to False
            $success = False;

            //Validation of information is unnecessary as form forced types for date and capacity and event name and venue can be alphanumeric
            //Sanitizing text information sent through form (rest is clear as is)
            $sanitizedEventName = filter_var(stripslashes(trim($name)), FILTER_SANITIZE_STRING);

            //Converting $startdate to proper format
            $start = substr($start, 0, 10) . " " . substr($start, 11);
            if (strlen($start) == 16){
                $start .= ":00";
            }
            $start = date($start);

            //Converting $enddate to proper format
            $end = substr($end, 0, 10) . " " . substr($end, 11);
            if (strlen($end) == 16){
                $end .= ":00";
            }
            $end = date($end);

            try {
                if ($stmt = $this->conn->prepare("UPDATE event SET name= :eventName, datestart= :eventStart, dateend= :eventEnd, numberallowed= :eventCapacity WHERE idevent= :eventID")){
                    $stmt->execute(array(":eventName"=>$sanitizedEventName, ":eventStart"=>$start, ":eventEnd"=>$end, ":eventCapacity"=>$capacity, ":eventID"=>$id));

                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Takes the eventID and deletes event from the following tables:
                manager_event, attendee_session, session, attendee_event, event
            Return: a boolean value, True if event successfully removed and False if not
        */
        function removeEvent($eventID){
            //No cascade allowed to be added to tables so:
                //Delete event from manager_event table
                //Delete event from attendee_session
                //Delete event from session table
                //Delete event from attendee_event
                //Delete event from event

            $numRows = False;

            try {
                //Delete event from manager_event table
                if ($stmt = $this->conn->prepare("DELETE FROM manager_event WHERE event= :eventID")){
                    //Executing
                    $stmt->execute(array(":eventID"=>$eventID));
                }

                //Delete event from attendee_session table
                $idAsString = "";
                //Retreiving a list of all sessions linked to event
                if ($stmt2 = $this->conn->prepare("SELECT idsession FROM session WHERE event= :eventID2")){
                    //Executing
                    $stmt2->execute(array(":eventID2"=>$eventID));

                    $sessions = [];
                    if ($stmt2->rowCount() > 0){
                        //If we have data
                        while ($row = $stmt2->fetch()){
                            //Appending new row entry with associative data array
                            $sessions[] = $row['idsession'];                                                                                                 //POTENITAL ISSUE
                        } 

                        foreach ($sessions as $num){
                            //Delete event from attendee_session table
                            if ($stmt3 = $this->conn->prepare("DELETE FROM attendee_session WHERE session= :sessionID")){
                                $stmt3->execute(array(":sessionID"=>$num));
                            }

                            //Delete event from session table
                            if ($stmt4 = $this->conn->prepare("DELETE FROM session WHERE idsession= :sessionID2")){
                                $stmt4->execute(array(":sessionID2"=>$num));
                            }
                        }
                    } 

                    //Delete event from attendee_event table
                    if ($stmt5 = $this->conn->prepare("DELETE FROM attendee_event WHERE event= :eventID3")){
                        $stmt5->execute(array(":eventID3"=>$eventID));
                    }

                    //Delete event from event table
                    if ($stmt6 = $this->conn->prepare("DELETE FROM event WHERE idevent= :eventID4")){
                        $stmt6->execute(array(":eventID4"=>$eventID));

                        //Setting boolean to whether row was deleted or not
                        $numRows = $stmt6->rowCount() > 0; 
                    }
                }
                return $numRows;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Takes in a new session name, start and end dates, and capacity to add a new event session on the 'Manage Events' page
            Return: a boolean value, True if event session successfully added and False if not
        */
        function addNewSession($sessionName, $associatedEventID, $startDate, $endDate, $capacity){
            $success = False;

            try {
                if ($stmt = $this->conn->prepare("INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES (:sessionName, :capacity, :eventID, :startDate, :endDate)")){
                    $stmt->execute(array(":sessionName"=>$sessionName, ":capacity"=>$capacity, ":eventID"=>$associatedEventID, ":startDate"=>$startDate, ":endDate"=>$endDate));
    
                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Takes the sessionID and retreives that sessions given information. Utilized to update a session
            Return: a associated array called 'data' containing all information for a given event session
        */
        function getSession($sessionID){
            $session = [];

            try {
                if ($stmt = $this->conn->prepare("SELECT name, startdate, enddate, numberallowed FROM session WHERE idsession= :sessionID")){
                    $stmt->execute(array(":sessionID"=>$sessionID));
                    $session['id'] = $sessionID;
                    while ($row = $stmt->fetch()){
                        $session['name'] = $row['name'];
                        $session['start'] = $row['startdate'];
                        $session['end'] = $row['enddate'];
                        $session['capacity'] = $row['numberallowed'];
                    } 
                }
                return $session;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Updates the chosen event session
            Return: a boolean value, True if session successfully updated and False if not
        */
        function updateSession($id, $name, $start, $end, $capacity){
            //Initially setting success to False
            $success = False;

            //Validation of information is unnecessary as form forced types for date and capacity and event name and venue can be alphanumeric
            //Sanitizing text information sent through form (rest is clear as is)
            $sanitizedSessionName = filter_var(stripslashes(trim($name)), FILTER_SANITIZE_STRING);

            //Converting $startdate to proper format
            $start = substr($start, 0, 10) . " " . substr($start, 11);
            if (strlen($start) == 16){
                $start .= ":00";
            }
            $start = date($start);

            //Converting $enddate to proper format
            $end = substr($end, 0, 10) . " " . substr($end, 11);
            if (strlen($end) == 16){
                $end .= ":00";
            }
            $end = date($end);

            try {
                if ($stmt = $this->conn->prepare("UPDATE session SET name= :sessionName, startdate= :sessionStart, enddate= :sessionEnd, numberallowed= :sessionCapacity WHERE idsession= :sessionID")){
                    $stmt->execute(array(":sessionName"=>$sanitizedSessionName, ":sessionStart"=>$start, ":sessionEnd"=>$end, ":sessionCapacity"=>$capacity, ":sessionID"=>$id));

                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Takes the sessionID and deletes event session from the following tables:
                attendee_session, session
            Return: a boolean value, True if event session successfully removed and False if not
        */
        function removeSession($sessionID){
            //No cascade allowed to be added to tables so:
                //Delete session from attendee_session
                //Delete session from session table
            $success = False;

            try {
                //Delete event from attendee_session table
                if ($stmt = $this->conn->prepare("DELETE FROM attendee_session WHERE session= :sessionID")){
                    $stmt->execute(array(":sessionID"=>$sessionID));
                }

                //Delete event from attendee_session table
                if ($stmt2 = $this->conn->prepare("DELETE FROM session WHERE idsession= :sessionID2")){
                    $stmt2->execute(array(":sessionID2"=>$sessionID));

                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        } 



        /*
            Takes in a new venue name and capacity to add a new venue on the 'Manage Events' page
            Return: a boolean value, True if venue successfully added and False if not
        */
        function addNewVenue($venueName, $capacity){
            //Default set success to false until insert succeeds
            $success = False;

            try {
                if ($stmt = $this->conn->prepare("INSERT INTO venue (name, capacity) VALUES (:venueName, :capacity)")){
                    //Executing
                    $stmt->execute(array(":venueName"=>$venueName, ":capacity"=>$capacity));
    
                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Takes the venueID and retreives that venues given information. Utilized to update a venue
            Return: a associated array called 'data' containing all information for a given venue
        */
        function getVenue($venueID){
            $venue = [];

            try {
                if ($stmt = $this->conn->prepare("SELECT name, capacity FROM venue WHERE idvenue= :venueID")){
                    $stmt->execute(array(":venueID"=>$venueID));
                    $venue['id'] = $venueID;
                    while ($row = $stmt->fetch()){
                        $venue['name'] = $row['name'];
                        $venue['capacity'] = $row['capacity'];
                    } 
                }
                return $venue;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Updates the chosen venue
            Return: a boolean value, True if venue successfully updated and False if not
        */
        function updateVenue($id, $name, $capacity){
            //Initially setting success to False
            $success = False;

            //Validation of information is unnecessary as form forced types for date and capacity and event name and venue can be alphanumeric
            //Sanitizing text information sent through form (rest is clear as is)
            $sanitizedVenueName = filter_var(stripslashes(trim($name)), FILTER_SANITIZE_STRING);

            try {
                if ($stmt = $this->conn->prepare("UPDATE venue SET name= :venueName, capacity= :venueCapacity WHERE idvenue= :venueID")){
                    $stmt->execute(array(":venueName"=>$sanitizedVenueName, ":venueCapacity"=>$capacity, ":venueID"=>$id));

                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Takes the venueID and retreives all associated events, calling remove event on each, before removing the venue itself
            Return: a boolean value, True if venue and associated event elements are successfully removed and False if not
        */
        function removeVenue($venueID){
            $success = False;

            try {
                //get linked events 
                if ($stmt = $this->conn->prepare("SELECT idevent FROM event WHERE venue= :venueID")){
                    //Executing
                    $stmt->execute(array(":venueID"=>$venueID));

                    while ($row = $stmt->fetch()){
                        //Removing each event that has the venue being removed
                        $this->removeEvent($row['idevent']);                                                                                                 //POTENITAL ISSUE
                    } 
                }

                //remove venue
                if ($stmt2 = $this->conn->prepare("DELETE FROM venue WHERE idvenue= :venueID2")){
                    //Executing
                    $stmt2->execute(array(":venueID2"=>$venueID));

                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }




        function getUser($userID){
            $user = [];
            try {
                if ($stmt = $this->conn->prepare('SELECT name, role FROM attendee WHERE idattendee= :attendeeID')){
                    $stmt->execute(array("attendeeID"=>$userID));

                    $user['id'] = $userID;
                    while ($row = $stmt->fetch()){
                        $user['name'] = $row['name'];
                        $user['role'] = $row['role'];
                    } 
                }
                return $user;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Retreives all the users in the db (if an admin) to be used on the 'Manage Users' or 'Manage Events' pages
            Return: an associated array called '$data' that contains information about all users
        */  
        function getUsers($case){
            $data = array();

            try {
                if($case=='table' || $case=='attendeeSessionAdd' || $case=='attendeeSessionRemove'){
                    $query = 'SELECT idattendee, name, role FROM attendee ORDER BY role';
                } elseif ($case=='removeUserSelect' || $case=='updateUserSelect'){
                    //If retreving for removal, ensure super-admin can not be chosen to be erased
                    $query = 'SELECT idattendee, name, role FROM attendee WHERE idattendee NOT IN (4) ORDER BY role';
                }else {
                    $query="";
                }

                if ($stmt = $this->conn->prepare($query)){
                    //Executing 
                    $stmt->execute();

                    //Retrieving data
                    while ($row = $stmt->fetch()){
                        $data[] = $row;
                    }
                }
                return $data;

            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Uses getUsers() function to place info in a table to be used on the 'Manage Users' page
            Return: a string called 'bigString' that contains an HTML table of users
        */  
        function getUsersAsTable($case){
            $users = $this->getUsers($case);

            if (count($users) > 0){
                //If there is data 
                $bigString = "<table><thread>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                </tr></thread><tbody>";

                //Adding events as to table
                foreach($users as $row){
                    $bigString .= "<tr>
                                        <td>{$row['name']}</td>";
                    $roleName = "";
                    if ($row['role']==1){
                        $roleName = "Admin";
                    } elseif ($row['role']==2){
                        $roleName = "Event manager";
                    } else {
                        $roleName = "Attendee";
                    }
                    
                    $bigString .= "     <td> {$roleName}</td>
                                    </tr>";

                }  
                $bigString .= "</tbody></table>";
            } else {
                //If there is no data for the table, instead of printing an empty table, print an h2 tag
                $bigString = "<h2>There are no users!</h2>";
            }
            return $bigString;
        }



        /*
            Uses getUsers() function to place info in a list of buttons to be used on the 'Manage Users' page
            Return: a string called '$bigString' that contains a div of input elements of the users' names and respective roles
        */ 
        function getUsersAsButtons($case){
            $users = $this->getUsers($case);
            $bigString = "";
            if (count($users) > 0){
                //If there is data 
                //Opening a div and setting class
                $bigString .= "<div class='potentialUsers'>";

                //Adding attendee entries to buttons
                foreach($users as $row){
                    $bigString .= "<input type='radio' name='{$case}' value='{$row['idattendee']}'>{$row['name']}<br>";
                }  
                
                //Closing the div once the data has been added
                $bigString .= "</div><br/>";

            } else {
                //If there is no data, print an h2 tag
                $bigString = "<h2>No users exist!</h2>";
            }

            return $bigString;
        }



        /*
            Takes in a new user name, password and role to add on the 'Manage Users' page
            Return: a boolean value, True if user successfully added and False if not
        */
        function addNewUser($name, $password, $role){
            //Default set success to false until insert succeeds
            $success = False;

            try {
                if ($stmt = $this->conn->prepare("INSERT INTO attendee (name, password, role) VALUES (:userName, :userPassword, :userRole)")){
                    /* //Hashing password
                    $hashPassword = password_hash($password, PASSWORD_DEFAULT); */
    
                    //Executing
                    $stmt->execute(array(":userName"=>$name, ":userPassword"=>$password, ":userRole"=>$role));
    
                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Updates the chosen user
            Return: a boolean value, True if user successfully updated and False if not
        */
        function updateUser($id, $name, $role){
            //Initially setting success to False
            $success = False;

            //Validation of information is unnecessary as form forced types can be alphanumeric
            //Sanitizing text information sent through form (rest is clear as is)
            $sanitizedUserName = filter_var(stripslashes(trim($name)), FILTER_SANITIZE_STRING);

            try {
                if ($stmt = $this->conn->prepare("UPDATE attendee SET name= :userName, role= :userRole WHERE idattendee= :attendeeID")){
                    $stmt->execute(array(":userName"=>$sanitizedUserName, ":userRole"=>$role, ":attendeeID"=>$id));

                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }



        /*
            Takes the userID and role, retreiving associated links in attendee_session to remove before removing user iteself
            Return: a boolean value, True if user is successfully removed and False if not
        */
        function removeUser($userID, $role){
            $success = False;

            try {
                //Remove event manager connections in manager_event table
                if ($role == 2){
                    if ($stmt = $this->conn->prepare("DELETE FROM manager_event WHERE manager= :managerID")){
                        //Executing
                        $stmt->execute(array(":managerID"=>$userID));
                    }
                }

                //Remove session connections in attendee_session table
                if ($stmt2 = $this->conn->prepare("DELETE FROM attendee_session WHERE attendee= :attendeeID")){
                    //Executing
                    $stmt2->execute(array(":attendeeID"=>$userID));
                }

                //Get attendee_event and remove link from that table
                if ($stmt3 = $this->conn->prepare("DELETE FROM attendee_event WHERE attendee= :attendeeID2")){
                    //Executing
                    $stmt3->execute(array(":attendeeID2"=>$userID));
                }

                //Remove attendee
                if ($stmt4 = $this->conn->prepare("DELETE FROM attendee WHERE idattendee= :attendeeID")){
                    //Executing
                    $stmt4->execute(array(":attendeeID"=>$userID));

                    $success = True;
                }
                return $success;
            } catch (PDOException $e) {
                //can log message
                echo $e->getMessage();
                return array();
            }
        }
    } //DB
?>