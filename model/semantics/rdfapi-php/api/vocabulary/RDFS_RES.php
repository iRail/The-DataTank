<?php
/**
*   RDF Vocabulary Description Language 1.0: RDF Schema (RDFS) Vocabulary (ResResource)
*
*   @version $Id: RDFS_RES.php 431 2007-05-01 15:49:19Z cweiske $
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
class RDFS_RES{

	static function RESOURCE()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'Resource');

	}

	static function LITERAL()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'Literal');

	}

	static function RDFS_CLASS()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'Class');

	}

	static function DATATYPE()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'Datatype');

	}

	static function CONTAINER()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'Container');

	}

	static function CONTAINER_MEMBERSHIP_PROPERTY()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'ContainerMembershipProperty');

	}

	static function SUB_CLASS_OF()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'subClassOf');

	}

	static function SUB_PROPERTY_OF()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'subPropertyOf');

	}

	static function DOMAIN()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'domain');

	}

	static function RANGE()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'range');

	}

	static function LABEL()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'label');

	}

	static function COMMENT()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'comment');

	}

	static function MEMBER()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'member');

	}

	static function SEEALSO()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'seeAlso');

	}

	static function IS_DEFINED_BY()
	{
		return  new ResResource(RDF_SCHEMA_URI . 'isDefinedBy');
	}
}
?>