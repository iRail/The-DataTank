<?php
/**
*   Resource Description Framework (RDF) Vocabulary (ResResource)
*
*   @version $Id: RDF_RES.php 431 2007-05-01 15:49:19Z cweiske $
*   @author Daniel Westphal (dawe@gmx.de)
*   @package vocabulary
*
*   Wrapper, defining resources for all terms of the
*   Resource Description Framework (RDF).
*   For details about RDF see: http://www.w3.org/RDF/.
*   Using the wrapper allows you to define all aspects of
*   the vocabulary in one spot, simplifing implementation and
*   maintainence.
*/
class RDF_RES{

	// RDF concepts (constants are defined in constants.php)
	static function ALT()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_ALT);

	}

	static function BAG()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_BAG);

	}

	static function PROPERTY()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_PROPERTY);

	}

	static function SEQ()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_SEQ);

	}

	static function STATEMENT()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_STATEMENT);

	}

	static function RDF_LIST()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_LIST);

	}

	static function NIL()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_NIL);

	}

	static function TYPE()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_TYPE);

	}

	static function REST()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_REST);

	}

	static function FIRST()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_FIRST);

	}

	static function SUBJECT()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_SUBJECT);

	}

	static function PREDICATE()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_PREDICATE);

	}

	static function OBJECT()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_OBJECT);

	}

	static function DESCRIPTION()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_DESCRIPTION);

	}

	static function ID()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_ID);

	}

	static function ABOUT()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_ABOUT);

	}

	static function ABOUT_EACH()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_ABOUT_EACH);

	}

	static function ABOUT_EACH_PREFIX()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_ABOUT_EACH_PREFIX);

	}

	static function BAG_ID()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_BAG_ID);

	}

	static function RESOURCE()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_RESOURCE);

	}

	static function PARSE_TYPE()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_PARSE_TYPE);

	}

	static function LITERAL()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_PARSE_TYPE_LITERAL);

	}

	static function PARSE_TYPE_RESOURCE()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_PARSE_TYPE_RESOURCE);

	}

	static function LI()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_LI);

	}

	static function NODE_ID()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_NODEID);

	}

	static function DATATYPE()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_DATATYPE);

	}

	static function SEE_ALSO()
	{
		return  new ResResource(RDF_NAMESPACE_URI . RDF_SEEALSO);
	}
}


?>