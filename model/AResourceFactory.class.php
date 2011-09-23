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
    /**
     * Creates an instance of a creator class.
     * @param $package the new package of the resource. It may exist already
     * @param $resource the name of the new resource. If it exists already, an exception will be thrown
     * @return The returned class implements ICreator and can add a resource to the system
     */
    abstract public function createCreator($package,$resource, $parameters);

    /**
     * Creates an instance of a reader. This can return the right information for a request
     * @param $package the package of the requested resource.
     * @param $resource the name of the requested resource.
     * @return The returned class implements IReader and can read information from a Resource
     */
    abstract public function createReader($package, $resource, $parameters);

    /**
     * Creates an instance of a deleter class.
     * @param $package the package of the resource. It will not get deleted
     * @param $resource the name of the new resource.
     * @return The returned class implements IDeleter and can delete a resource from the system
     */
    abstract public function createDeleter($package,$resource);

    /**
     * Visitor pattern function
     * @param $doc Doc is an instance of the Doc class. It will go allong every factory and ask for every resource's documentation data. Each resource adds what it wants to add.
     */
    abstract public function makeDoc($doc);
}
?>
