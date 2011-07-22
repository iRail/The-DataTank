<?php

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

    public function handle() {
        $self->setData();
        return self->result;
    };
}
?>
