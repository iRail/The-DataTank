<?php

/**
 * This static class RDFContants defines important constants.
 *
 * @package The-Datatank/model/semantics
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
include_once('rdfapi-php/api/constants.php');

class RDFConstants {

    public static $TDML_NS = "http://thedatatank.com/tdtml/1.0#";
    public static $MAPPING_UPDATE = "update";
    public static $MAPPING_DELETE = "delete";
    public static $MAPPING_EQUALS = "equals";
    public static $VOCABULARIES = array(
        RDF_NAMESPACE_PREFIX => RDF_NAMESPACE_URI,
        RDF_SCHEMA_PREFIX => RDF_SCHEMA_URI,
        'xsd' => 'http://www.w3.org/2001/XMLSchema#',
        OWL_PREFIX => OWL_URI,
        'dc' => 'http://purl.org/dc/elements/1.1/',
        'dcterms' => 'http://purl.org/dc/terms/',
        'vcard' => 'http://www.w3.org/2001/vcard-rdf/3.0#'
    );

}

?>
