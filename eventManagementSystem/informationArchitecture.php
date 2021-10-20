<?php
    class Architecture {
        private $currentPage;

        /* Setting currentPage variable */
        function __construct($page){
            $this->currentPage = $page;
        }


        /* Retreiving navigation and header */
        function Navigation(){
            $statement = "";
            //If statement --> depending on role, different navigation links div (same header and logout)
            if ($_SESSION['loggedIn']){
                //Set for all permissions
                $statement .= '<header>
                        <h1>Event Planner</h1> 
                        <h3>Welcome, ' . $_SESSION["user"]["name"] . '</h3>
                    </header>
    
                    <nav>
                        <div class=\'links\'>
                            <a href="attendeeHome.php" ' . (($this->currentPage=='attendeeHome.php') ? 'class="active"' : "") . '">Events</a>
                            <a href="attendeeRegistration.php" ' . (($this->currentPage=='attendeeRegistration.php') ? 'class="active"' : "") . '">Event Registration</a>';
                
                //Extra permission of event management toward event managers and admins
                if ($_SESSION['user']['role'] == 2 OR $_SESSION['user']['role'] == 1){
                        $statement .= '<a href="eventManagerManageEvents.php" ' . (($this->currentPage=='eventManagerManageEvents.php') ? 'class="active"' : "") . '">Manage Events</a>';
                }
    
                //Extra permission of user management toward admins
                if ($_SESSION['user']['role'] == 1){
                    $statement .= '<a href="adminManageUsers.php" ' . (($this->currentPage=='adminManageUsers.php') ? 'class="active"' : "") . '">Manage Users</a>';
                }
                
                $statement .= '</div><div class=\'logout\'>
                            <a href="logout.php">Logout</a>
                        </div>
                    </nav>';
            } else {
                $statement .= '<header>
                        <h1>Event Planner</h1>
                    </header>';
            }
            return $statement;
        }
    
    
        /* Retreving footer */
        function Footer(){
            echo '<br/><br/><br/><br/><br/><footer><p class="copyright"> &copy; Alexis Gordon   October 2021 </p></footer>';
        }
    }
?>