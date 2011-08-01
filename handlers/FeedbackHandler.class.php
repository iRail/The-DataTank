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
        /* Connect to mysql database */
        $conn = MDB2::factory(Config::$DSN, Config::$OPTION);
        
        $pageUrl = TDT::getPageUrl();

        $queryString = 'Insert Into feedback_messages (url_request, msg) values ('
            . $conn->quote($pageUrl, 'text') . ', '
            . $conn->quote($_POST['msg'], 'text') . ')';

        $result = $conn->exec($queryString);
        echo '\n' . $result . '. ';

        /* Close the connection */
        $conn->disconnect();
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
