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
	require_once ('contents.php');
	include_once ("templates/TheDataTank/header.php");
	echo $index_content;
	include_once ("templates/TheDataTank/footer.php");
    }

    //give error on POST?
}


?>