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
        R::setup(Config::$DB, Config::$MySQL_USER_NAME, Config::$MySQL_PASSWORD);

        $message = R::dispense('feedback_messages');
        $message->url_request = TDT::getPageUrl();
        $message->msg = $_POST['msg'];
        R::store($message);
    }

    /**
     * Push the feedback into our database.
     * @return The result of the push (contains a mysqli result).
     */
    public function post() {
        $this->setData();
        header('Created', true, 201);
    }
}
?>
