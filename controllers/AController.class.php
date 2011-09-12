<?php
/**
 * The controller will look for GET and POST requests on a certain module. It will ask the factories to return the right Resource instance.
 * If it checked all required parameters, checked the format, it will perform the call and get a result.
 *
 * @package The-Datatank/controllers
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */

abstract class AController{
    abstract function GET($matches);
    abstract function POST($matches);
    abstract function PUT($matches);
    abstract function DELETE($matches);
}

?>