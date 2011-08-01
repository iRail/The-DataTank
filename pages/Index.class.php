<?php
/**
 * Index class, homepage of the datatank
 *
 * @package The-Datatank/pages
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */


class Index{
    function GET() {
	include_once ("templates/TheDataTank/header.php");
	echo '<h1>Welcome to The DataTank</h1>
              <h2>The DataTank for an app-builder</h2>
              <p>The DataTank is nothing more than <strong>a webservice</strong>. Whether you need to set up an API (Application Programming Interface) on your own domain, or whether you want to use public data, you\'re at the right address.</p>
              <p>This project aims at serving giving you the right data. Check out <a href="/docs/">the datasets</a> that are currently available.</p>
              <h2>The DataTank for a data-owner</h2>
              <p>We have the right platform for you to <strong>open your data</strong>. We believe that opening your data is not only a political interesting thing to do, it is as well economically relevant. Combining your data with other data online has never been this easy!</p>';
	
	include_once ("templates/TheDataTank/footer.php");
    }
}
?>