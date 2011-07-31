<?php
/**
 * This class represents the Docs page
 *
 * @package The-Datatank/pages
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

class Docs {
	function GET() {
		require_once ("handlers/DocPrinter.php");
	}
}

class DocPage {
	function GET($matches) {
		require_once ("handlers/DocPagePrinter.php");
	}

}

?>