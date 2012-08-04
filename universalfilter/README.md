The Abstract Filter Layer
=========================

What is it and why would you use it?
------------------------------------

The Abstract Filter Layer is a module for filtering and querying the data from The DataTank. It is some sort of abstraction to make it easier to implement new kinds of filters with minimal effort.

At the moment of writing this documentation two filters are implemented using this abstraction layer: SQL and spectql.


### What do you need to do to implement a new query language?
Anwser: convert the query to the Universal Query Syntax Tree, give it to the UniversalInterpreter, and you are done. Yes, it's that easy.

The UniversalInterpreter interprets the query tree, filters rows, evaluates expressions, matches regexes, filters columns, groups data on certain fields, calculates aggregated values on those fields, ... You don't have to do all those things yourself. So, implementing a new filter becomes a lot easier!


An example: The SQL filter. And how it is executed...
-----------------------------------------------------

*Note:* this is an example of how I converted SQL-querys to the Filter Syntax Tree, but you can do it your way. (It does not matter how you convert it to the syntax tree, or how the user inputs the query.)

For this example we assume we have a resource "gentsefeesten/dag15" in The DataTank.

### Requesting the data...
Assume the user wants the titles and the description of all events from day 15 of the Gentse Feesten where the title starts with 'Bloem'

In SQL this is: 

    SELECT Titel, Omschrijving FROM genstefeesten.dag15 WHERE Titel LIKE 'Bloem%'

To give this SQL-query to The DataTank, we first need to url-encode it. You can use online tools for this. (or the javascript encode()-function) Encoded this becomes:

    SELECT%20Titel%2C%20Omschrijving%20FROM%20genstefeesten.dag15%20WHERE%20Titel%20LIKE%20'Bloem%25'

Add it to the url for sql, and we have:

    localhost/The-DataTank/sql.csv?query=SELECT%20Titel%2C%20Omschrijving%20FROM%20genstefeesten.dag15%20WHERE%20Titel%20LIKE%20'Bloem%25'

Surf to this url and you get back the data you want.

### The flow this query follows inside The-DataTank
When the request enters The-DataTank, it first enters the router. In the router the request gets send to the SQLController.
The SQLController creates an SQLParser and asks it to parse the SQL-query. That's where the conversion from string to Filter Syntax Tree happens. I will go deeper into this in the next paragraph.
The SQLParser returns a Syntax Tree. 

The SQLController makes a new UniversalInterpreter and calls the method interpret($tree) on it. This method returns the dataset in the intern representation of the Abstract Filter Layer (but that's not really important). 
Now the SQLController creates a new TableToPhpObjectConverter and asks it to convert the table to a php object as it is used by The DataTank.

Now, you can use the formatters to format the data. Filtering done...


### The SQLParser
So the only component I did not explain in detail in the above description is the SQLParser. It has only one task: convert the string which represents an SQL-query to the Filter Syntax Tree. To do this I use lime-php. That's a php library to describe and parse context free grammars. It uses a notation that looks like Bachus Naur Form, but than with php-statement which tell the parser what to do if it matches a certain part.
Before I input the data into the parser I already split the query in "tokens" in the SQLTokenizer. In theory, you don't need to do this, as you can pass each character to the grammar and then tokenize there. But e.g. constants (which can contain every character EXCEPT ') are not very handy to describe in a grammar. That's why I first tokenize these basic parts.
If you need examples for the parsing: both SQL and spectql use the lime-parser and have almost the same structure. First they use a SQL/SpectqlTokenizer, then they parse the grammar with a .lime grammar file. These two steps are combined in the class SQLParser/SpectqlParser.


The Filter Syntax Tree (also Universal Filter Tree)
---------------------------------------------------

This section describes what a query can be build of. 
All the possible nodes are defined in one file for ease of use: "universalfilters/UniversalFilters.php".

You should really check out that file for the kinds of filters you can use.

### A short description of the filters you can use

#### Filter: Identifier
The most basic and most used filter is the Identifier. It has two meanings.
If you use it as a Source for another filter, it represents a table from The DataTank.
E.g. "gentsefeesten.dag15" in the above example is wrapped in an Identifier.

If you use it anywhere else (e.g. in the WHERE part or SELECT part of an SQL-query), it represents a column/a number of columns in the source dataset. In this last interpretation it can also contain '*', which just returns the complete source dataset.

Please note that I use "." as separator and not "/". So it's "package.resource.column" .

#### Filter: Constant
Another very basic filter is the Constant. It returns a column which contains the given constant.

#### Filter: ColumnSelectionFilter
If you need to select columns or build a new table from existing or calculated columns, you use a ColumnSelectionFilter. It needs a source (the filter that is executed before) and an array of ColumnSelectionFilerColumns which contain a filter that return a column and an optional alias. 

#### Filter: DistinctFilter
Removes double rows...

#### Filter: DataGrouper
Groups data on the given fields. You probably want to use aggregator functions after you did the grouping.

#### UnairyFunctions
Input: one column of the data.  
Output: a new column (the unairy function applied)

Supported unairy functions: "to uppercase", "to lowercase", "string length", "round number", "check if null", "boolean not".

#### BinaryFunctions
Input: two columns of the data.  
Output: a new column

Supported Binary functions: "+", "-", "*", "/", "<", ">", "<=", ">=", "=", "!=", "OR", "AND", "match regex" (does arg1 matches arg2 where arg2 is a regular expression in php)

#### TertairyFunctions
Input: three columns of data.  
Output: a new column

Supported Tertairy functions: "substring", "regex replace".

#### Aggregators
Input: A table or a column or a grouped column  
Output: A row or a cell or a column

Aggregators combine multiple rows in one row. They can be used on a full table (eg. Count(*)) or on columns, or on grouped columns.

Supported Aggregators: "average", "count", "first", "last", "max", "min", "sum"

#### CheckInFunction
Checks for each field in the column if it matches a constant in the list.
(Some sort of enum check)

#### Conclusion
So, those are the filters you can use to build the filter syntax tree. Have fun implementing your query language!


Implementation of the Interpreter
---------------------------------
See the README.md in the folder universaltree/interpreter to start.


Future development
------------------

### There is also no filter yet: 
1. to sort the data. (order multiple columns ascending/descending)
2. to join data (full/left/right inner/outer join)
3. for Union

### Other future developments:
- there are no datatypes. It can be usefull to keep information about the datatype in the tree, so that booleans can be used as numbers, and date's can be compared but also printed without problem. You will also need functions to convert datatypes if you implement this.

### Optimalisation:
The Aggregators are NOT optimized for big datasets (except for count).
Group By is not optimized for big datasets because BigMap is not implemented correctly (keeps everything in memory)

### Query optimalisation:
See README in universalfilters/interpreter/optimizer if you would like to implement that.

### Converting the tree back to SQL and other filters... to execute directly on a source.
That is possible, BUT...
You have to be careful with joins and nested querys. The can contain data from different sources.
E.g. if you have an xml-file and a table of a database, you can join them, but it's hard to convert to SQL...

#### More in detail... What does it take to implement this feature...
First you have to check all dependencies between identifiers to see if they all belong to the same source. (1)
You also have to take table and column aliases in account in this step.

After that you can split the query-tree in pieces where each piece only depends on 1 source. You will also have one toppiece which afterwards combines all sources. (join)

You can then try to convert the pieces to other query languages and execute them on the source directly. (2) (3)
After that you just execute the toppiece...

(1) You can do this while calculating the headers... (in initExpression)
(2) Note that not all querylanguages are as expressive as the query tree. So sometimes those little pieces have to be split up themself too.
(3) Sometimes it is more efficient to just download the full source. E.g. with nested querys, where a subquery is executed for each row in another table.
