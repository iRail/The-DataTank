Parser for SQL querys
=====================

This folder contains the parser for the SQL-querys you can give to The DataTank.

INPUT: string that represents a SQL-query  
OUTPUT: a Filter Syntax Tree. (as described in universalfilters/README.md)


What can this parser parse and convert to the Universal Syntax Tree?
--------------------------------------------------------------------

It can parse and convert:
 - SELECT... with optional aliases for columns
 - FROM (but universalfilters supports no joins yet, so no ",")
 - WHERE e
 - GROUP BY field, field, field
 - LIKE
 - IN (...)

 - nested Selects

 - Math: "+", "-", "*", "/"
 - Comparision: "<", ">", "<=", ">=", "<>", "!=", "="
 - Boolean operations: "AND", "OR", "NOT"
 - String concatenation: '|'
 - Functions:
   * Unary: "UCASE(\_)", "LCASE(\_)", "LEN(\_)", "ROUND(\_)", "ISNULL(\_)", "SIN(\_)", "COS(\_)", "TAN(\_)", "ASIN(\_)", "ACOS(\_)", "ATAN(\_)", "SQRT(\_)", "ABS(\_)", "FLOOR(\_)", "CEIL(\_)", "EXP(\_)", "LOG(\_)"
   * Binary: "MATCH\_REGEX(\_,\_)", "ATAN2(\_,\_)", "LOG(\_,\_)", "POW(\_,\_)"
   * Ternary: "MID(\_,\_,\_)", "REPLACE\_REGEX(\_,\_,\_)" 
   * Aggregators: "AVG(\_)", "COUNT(\_)", "FIRST(\_)", "LAST(\_)", "MAX(\_)", "MIN(\_)"

 (Note: "ISNULL(\_)", "MATCH\_REGEX(\_,\_)" and "REPLACE\_REGEX(\_,\_,\_)" are not SQL functions)


How I parse SQL querys
----------------------

example: ``SELECT * FROM package.resource``


 1. SQLTokenizer => split query in tokens
 
        "SELECT", "*", "FROM", "package.resource"
    
 2. SQLParser => categorize the tokens and give the tokens to the grammar
 
        "SELECT"        => category SELECT,     value null
        "*"             => category '*',        value null
        "FROM"          => category FROM,       value null
        "package.table" => category identifier, value "package.resource"
    
 3. SQLgrammar => build the tree.

        ...result:
        ColumnSelectionFilter(
            new ColumnSelectionFilterColumn(
                new Identifier("*"), null));
        ...after...
        Identifier("package.resource");


SQLgrammar.lime
---------------

The SQLgrammar is writen in lime-php. That's a php library to describe and parse context free grammars. It uses a notation that looks like Bachus Naur Form, but than with php-statements which tell the parser what to do if it matches a certain part.


Limits of the current SQL Parser
--------------------------------

- Joins, Sorting, Union and Limit+Offset are not supported, as the Abstract Filter Layer does not support that yet.
- IS NULL or IS NOT NULL are not implemented and NULL is not a constant...
- There are no datatypes, and no functions for dates yet.
- bug? tokenizer has problems with newlines at the end of the query (???)