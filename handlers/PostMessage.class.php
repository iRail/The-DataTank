<?php

/* Copyright (C) 2011 by iRail vzw/asbl
   *
   * Author: Werner Laurensse
   * License: AGPLv3
   *
   * These classes extend the Exception class to make our own well-documented Exception-system
   */

  /**
   * This file contains classes that extend the Exception class to make our own well-documented Exception-system
   * @package The-Datatank/feedback
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Werner Laurensse
   */

  /**
   * This class forwards some feedback given by the user to our database for further analysis.
   */
class PostMessage {
    private $result;

    private function setData() {
        $pageURL = TDT::getPageUrl();

	  // To conquer sql injection, one must become sql injection.... or use
	  // prepared statements.	 
	  $mysqli = new mysqli('localhost', Config::$MySQL_USER_NAME, Config::$MySQL_PASSWORD, Config::$MySQL_DATABASE);
	  if(mysqli_connect_errno()){
	       echo "Something went wrong !! . " . mysqli_connect_error();
	       exit();
	  }	
	  // if id = 0, the auto incrementer will trigger
	  $auto_incr = 0;
	  $stmt = $mysqli->prepare("INSERT INTO feedback_messages VALUES (?, ?, ?)");
	  $stmt->bind_param('iss', $auto_incr, $pageURL, $_POST['msg']);
	  $result = $stmt->execute();
      if ($result === false) {
          header('Failed to create new Message', true, 400);
      }
	  $stmt->close();
	  $mysqli->close();
    }

    /**
     * Push the feedback into our database.
     * @return The result of the push (contains a mysqli result).
     */
    public function post() {
        $this->setData();
        header('Created', true, 201);
        //return self->result;
    }
}
?>
