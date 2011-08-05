<?php
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
class FeedbackHandler {
    private $result;

    private function setData() {
        R::setup(Config::$DB, Config::$MySQL_USER_NAME, Config::$MySQL_PASSWORD);

        self->result = R::find(
            'feedback_messages',
            'url_request = :url_request',
            array(':url_request' => TDT::getPageUrl())
        ); 
    }

    /**
     * Push the feedback into our database.
     * @return The result of the push (contains a mysqli result).
     */
    public function handle() {
        $self->setData();
        return self->result;
    };
}
?>
