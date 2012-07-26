The Implementation of the interpreter for the Abstract Filter Layer
===================================================================


This package contains the executers for a specific node in a Universal Filter Tree.

E.g. IdentifierExecuter executes a Identifier...

You can find more information in universalfilters/interpreter/README.md


Special files:
 - UniversalFilterExecuters.php -> groups all includes...

 - UniversalFilterNodeExecuter.class.php -> top class of all executers
 - ExpressionFilterNodeExecuter.class.php -> top class of all expression-executers 

 - BaseHashingFilterExecuter.class.php 
    -> base functionality for DataGrouperExecuter and DistinctFilterExecuter
    (they both search for rows that are the same (for some fields))

 - BaseEvaluationEnvironmentFilterExecuter.class.php 
    -> base functionality for FilterByExpressionExecuter and ColumnSelectionFilterExecuter.
    (they both have a source and a environment)

 - Unary/Binary/Tertairy/Aggregator-FunctionExecuter.class.php
    -> base functionality for all unary/binary/tertairy/aggregator executers.
    The executers themself can be found in Unary/Binary/Tertairy/Aggregator-Executer*s*.php

 - The rest of the names of the executers speak for themself...