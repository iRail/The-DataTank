<?php
/**
 * Interface for a factory: check documentation on the Abstract Factory Pattern if you don't understand this code.
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

abstract class AResourceFactory{
    abstract public function createCreater($package,$resource);
    abstract public function createReader($package,$resource);
    abstract public function createUpdater($package,$resource);
    abstract public function createDeleter($package,$resource);
    abstract public function makeDoc($doc);
}
?>
