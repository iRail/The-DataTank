<?php
/**
*   OWL Vocabulary (ResResource)
*
*   @version $Id: OWL_RES.php 431 2007-05-01 15:49:19Z cweiske $
*   @author Daniel Westphal (dawe@gmx.de)
*   @package vocabulary
*
*   Wrapper, defining resources for all terms of theWeb
*   Ontology Language (OWL). For details about OWL see:
*   http://www.w3.org/TR/owl-ref/
*   Using the wrapper allows you to define all aspects of
*   the vocabulary in one spot, simplifing implementation and
*   maintainence.
*/
class OWL_RES{

	// OWL concepts
	static function ANNOTATION_PROPERTY()
	{
		return  new ResResource(OWL_NS . 'AnnotationProperty');

	}

	static function ALL_DIFFERENT()
	{
		return  new ResResource(OWL_NS . 'AllDifferent');

	}

	static function ALL_VALUES_FROM()
	{
		return  new ResResource(OWL_NS . 'allValuesFrom');

	}

	static function BACKWARD_COMPATIBLE_WITH()
	{
		return  new ResResource(OWL_NS . 'backwardCompatibleWith');

	}

	static function CARDINALITY()
	{
		return  new ResResource(OWL_NS . 'cardinality');

	}

	static function OWL_CLASS()
	{
		return  new ResResource(OWL_NS . 'Class');

	}

	static function COMPLEMENT_OF()
	{
		return  new ResResource(OWL_NS . 'complementOf');

	}

	static function DATATYPE()
	{
		return  new ResResource(OWL_NS . 'Datatype');

	}

	static function DATATYPE_PROPERTY()
	{
		return  new ResResource(OWL_NS . 'DatatypeProperty');

	}

	static function DATA_RANGE()
	{
		return  new ResResource(OWL_NS . 'DataRange');

	}

	static function DATATYPE_RESTRICTION()
	{
		return  new ResResource(OWL_NS . 'DatatypeRestriction');

	}

	static function DEPRECATED_CLASS()
	{
		return  new ResResource(OWL_NS . 'DeprecatedClass');

	}

	static function DEPRECATED_PROPERTY()
	{
		return  new ResResource(OWL_NS . 'DeprecatedProperty');

	}

	static function DISTINCT_MEMBERS()
	{
		return  new ResResource(OWL_NS . 'distinctMembers');

	}

	static function DIFFERENT_FROM()
	{
		return  new ResResource(OWL_NS . 'differentFrom');

	}

	static function DISJOINT_WITH()
	{
		return  new ResResource(OWL_NS . 'disjointWith');

	}

	static function EQUIVALENT_CLASS()
	{
		return  new ResResource(OWL_NS . 'equivalentClass');

	}

	static function EQUIVALENT_PROPERTY()
	{
		return  new ResResource(OWL_NS . 'equivalentProperty');

	}

	static function FUNCTIONAL_PROPERTY()
	{
		return  new ResResource(OWL_NS . 'FunctionalProperty');

	}

	static function HAS_VALUE()
	{
		return  new ResResource(OWL_NS . 'hasValue');

	}

	static function INCOMPATIBLE_WITH()
	{
		return  new ResResource(OWL_NS . 'incompatibleWith');

	}

	static function IMPORTS()
	{
		return  new ResResource(OWL_NS . 'imports');

	}

	static function INTERSECTION_OF()
	{
		return  new ResResource(OWL_NS . 'intersectionOf');

	}

	static function INVERSE_FUNCTIONAL_PROPERTY()
	{
		return  new ResResource(OWL_NS . 'InverseFunctionalProperty');

	}

	static function INVERSE_OF()
	{
		return  new ResResource(OWL_NS . 'inverseOf');

	}

	static function MAX_CARDINALITY()
	{
		return  new ResResource(OWL_NS . 'maxCardinality');

	}

	static function MIN_CARDINALITY()
	{
		return  new ResResource(OWL_NS . 'minCardinality');

	}

	static function NOTHING()
	{
		return  new ResResource(OWL_NS . 'Nothing');

	}

	static function OBJECT_CLASS()
	{
		return  new ResResource(OWL_NS . 'ObjectClass');

	}

	static function OBJECT_PROPERTY()
	{
		return  new ResResource(OWL_NS . 'ObjectProperty');

	}

	static function OBJECT_RESTRICTION()
	{
		return  new ResResource(OWL_NS . 'ObjectRestriction');

	}

	static function ONE_OF()
	{
		return  new ResResource(OWL_NS . 'oneOf');

	}

	static function ON_PROPERTY()
	{
		return  new ResResource(OWL_NS . 'onProperty');

	}

	static function ONTOLOGY()
	{
		return  new ResResource(OWL_NS . 'Ontology');

	}

	static function PRIOR_VERSION()
	{
		return  new ResResource(OWL_NS . 'priorVersion');

	}

	static function PROPERTY()
	{
		return  new ResResource(OWL_NS . 'Property');

	}

	static function RESTRICTION()
	{
		return  new ResResource(OWL_NS . 'Restriction');

	}

	static function SAME_AS()
	{
		return  new ResResource(OWL_NS . 'sameAs');

	}

	static function SAME_CLASS_AS()
	{
		return  new ResResource(OWL_NS . 'sameClassAs');

	}

	static function SAME_INDIVIDUAL_AS()
	{
		return  new ResResource(OWL_NS . 'sameIndividualAs');

	}

	static function SAME_PROPERTY_AS()
	{
		return  new ResResource(OWL_NS . 'samePropertyAs');

	}

	static function SOME_VALUES_FROM()
	{
		return  new ResResource(OWL_NS . 'someValuesFrom');

	}

	static function SYMMETRIC_PROPERTY()
	{
		return  new ResResource(OWL_NS . 'SymmetricProperty');

	}

	static function THING()
	{
		return  new ResResource(OWL_NS . 'Thing');

	}

	static function TRANSITIVE_PROPERTY()
	{
		return  new ResResource(OWL_NS . 'TransitiveProperty');

	}

	static function UNION_OF()
	{
		return  new ResResource(OWL_NS . 'unionOf');

	}

	static function VERSION_INFO()
	{
		return  new ResResource(OWL_NS . 'versionInfo');
	}
}



?>