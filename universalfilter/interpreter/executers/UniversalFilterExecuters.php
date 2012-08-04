<?php

/**
 * This file collects all imports for the UniversalInterpreter
 *
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */

// base filters
include_once("universalfilter/interpreter/executers/UniversalFilterNodeExecuter.class.php");
include_once("universalfilter/interpreter/executers/IdentifierExecuter.class.php");
include_once("universalfilter/interpreter/executers/ConstantExecuter.class.php");

include_once("universalfilter/interpreter/executers/BaseEvaluationEnvironmentFilterExecuter.class.php");
include_once("universalfilter/interpreter/executers/FilterByExpressionExecuter.class.php");
include_once("universalfilter/interpreter/executers/ColumnSelectionFilterExecuter.class.php");

include_once("universalfilter/interpreter/executers/BaseHashingFilterExecuter.class.php");
include_once("universalfilter/interpreter/executers/DistinctFilterExecuter.class.php");
include_once("universalfilter/interpreter/executers/DataGrouperExecuter.class.php");

include_once("universalfilter/interpreter/executers/TableAliasExecuter.class.php");

// expressions
include_once("universalfilter/interpreter/executers/ExpressionNodeExecuter.class.php");
include_once("universalfilter/interpreter/executers/UnaryFunctionExecuter.class.php");
include_once("universalfilter/interpreter/executers/UnaryFunctionExecuters.php");
include_once("universalfilter/interpreter/executers/BinaryFunctionExecuter.class.php");
include_once("universalfilter/interpreter/executers/BinaryFunctionExecuters.php");
include_once("universalfilter/interpreter/executers/TertairyFunctionExecuter.class.php");
include_once("universalfilter/interpreter/executers/TertairyFunctionExecuters.php");
include_once("universalfilter/interpreter/executers/AggregatorFunctionExecuter.class.php");
include_once("universalfilter/interpreter/executers/FullTableAggregatorFunctionExecuter.class.php");
include_once("universalfilter/interpreter/executers/ColumnAggregatorFunctionExecuter.class.php");
include_once("universalfilter/interpreter/executers/AggregatorFunctionExecuters.php");
include_once("universalfilter/interpreter/executers/CheckInFunctionExecuter.class.php");