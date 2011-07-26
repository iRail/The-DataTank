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
	    $link = mysqli_connect(
	        'localhost',              /* The host to connect to */
	        Config::$MySQL_USER_NAME, /* The user to connect with the MySQL database */
	        Config::$MySQL_PASSWORD,  /* The password to use to connect with the db  */
	        Config::$MySQL_DATABASE); /* The default database to query */

        if (!$link) {
            printf("Can't connect to MySQL Server. Errorcode: %s\n",
                mysqli_connect_error());
	       exit;
        }

        $pageUrl = TDT::getPageUrl();

        $queryString = 'Insert Into feedback_messages (url_request, msg) values ("' .
            $pageUrl . '", "' . $_POST['msg'] . '");';

        $result = mysqli_query($link, $queryString);
        echo '\n' . $result . '. ';

        /* Close the connection */
        mysqli_close($link);
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
