<?php
/**
*   RDF Vocabulary Description Language 1.0: RDF Schema (RDFS) Vocabulary (Resource)
*
*   @version $Id: RDFS_C.php 431 2007-05-01 15:49:19Z cweiske $
*   @author Daniel Westphal (dawe@gmx.de)
*   @package vocabulary
*
*   Wrapper, defining resources for all terms of the
*   RDF Schema (RDFS).
*   For details about RDFS see: http://www.w3.org/TR/rdf-schema/.
*   Using the wrapper allows you to define all aspects of
*   the vocabulary in one spot, simplifing implementation and
*   maintainence.
*/
class RDFS{

	static function RESOURCE()
	{
		return  new Resource(RDF_SCHEMA_URI . 'Resource');

	}

	static function LITERAL()
	{
		return  new Resource(RDF_SCHEMA_URI . 'Literal');

	}

	static function RDFS_CLASS()
	{
		return  new Resource(RDF_SCHEMA_URI . 'Class');

	}

	static function DATATYPE()
	{
		return  new Resource(RDF_SCHEMA_URI . 'Datatype');

	}

	static function CONTAINER()
	{
		return  new Resource(RDF_SCHEMA_URI . 'Container');

	}

	static function CONTAINER_MEMBERSHIP_PROPERTY()
	{
		return  new Resource(RDF_SCHEMA_URI . 'ContainerMembershipProperty');

	}

	static function SUB_CLASS_OF()
	{
		return  new Resource(RDF_SCHEMA_URI . 'subClassOf');

	}

	static function SUB_PROPERTY_OF()
	{
		return  new Resource(RDF_SCHEMA_URI . 'subPropertyOf');

	}

	static function DOMAIN()
	{
		return  new Resource(RDF_SCHEMA_URI . 'domain');

	}

	static function RANGE()
	{
		return  new Resource(RDF_SCHEMA_URI . 'range');

	}

	static function LABEL()
	{
		return  new Resource(RDF_SCHEMA_URI . 'label');

	}

	static function COMMENT()
	{
		return  new Resource(RDF_SCHEMA_URI . 'comment');

	}

	static function MEMBER()
	{
		return  new Resource(RDF_SCHEMA_URI . 'member');

	}

	static function SEEALSO()
	{
		return  new Resource(RDF_SCHEMA_URI . 'seeAlso');

	}

	static function IS_DEFINED_BY()
	{
		return  new Resource(RDF_SCHEMA_URI . 'isDefinedBy');
	}

}
?>