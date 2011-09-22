<?php
/**
*   Resource Description Framework (RDF) Vocabulary (Resource)
*
*   @version $Id: RDF_C.php 431 2007-05-01 15:49:19Z cweiske $
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
class RDF{

	// RDF concepts (constants are defined in constants.php)
	static function ALT()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_ALT);

	}

	static function BAG()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_BAG);

	}

	static function PROPERTY()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_PROPERTY);

	}

	static function SEQ()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_SEQ);

	}

	static function STATEMENT()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_STATEMENT);

	}

	static function RDF_LIST()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_LIST);

	}

	static function NIL()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_NIL);

	}

	static function TYPE()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_TYPE);

	}

	static function REST()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_REST);

	}

	static function FIRST()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_FIRST);

	}

	static function SUBJECT()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_SUBJECT);

	}

	static function PREDICATE()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_PREDICATE);

	}

	static function OBJECT()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_OBJECT);

	}

	static function DESCRIPTION()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_DESCRIPTION);

	}

	static function ID()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_ID);

	}

	static function ABOUT()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_ABOUT);

	}

	static function ABOUT_EACH()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_ABOUT_EACH);

	}

	static function ABOUT_EACH_PREFIX()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_ABOUT_EACH_PREFIX);

	}

	static function BAG_ID()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_BAG_ID);

	}

	static function RESOURCE()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_RESOURCE);

	}

	static function PARSE_TYPE()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_PARSE_TYPE);

	}

	static function LITERAL()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_PARSE_TYPE_LITERAL);

	}

	static function PARSE_TYPE_RESOURCE()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_PARSE_TYPE_RESOURCE);

	}

	static function LI()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_LI);

	}

	static function NODE_ID()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_NODEID);

	}

	static function DATATYPE()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_DATATYPE);

	}

	static function SEE_ALSO()
	{
		return  new Resource(RDF_NAMESPACE_URI . RDF_SEEALSO);
	}
}


?>