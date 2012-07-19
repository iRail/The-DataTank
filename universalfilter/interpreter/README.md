The Implementation of the interpreter for the Abstract Filter Layer
===================================================================

NOTE
----
If you just want to implement a new query-language, you don't need to read this file. In that case, please go to the file: "universalfilter/README.md".


The idea
--------

So, someone gives us a Filter Syntax Tree, and we need to evaluate it. How are we going to do that? 
First problem: a "Filter Syntax Tree" is a bit abstract. So, let's try to focus on SQL. How would we implement SQL? 
SQL works on tables. Well, so does this interpreter. (The tables can be extended to be something else than a table though) 

So, the first thing we need to do is convert all the data inside The DataTank to tables. For csv's, excel, and other kinds of tabular data that's not a problem. But what about xml?

### The ``UniversalTableManager``

We don't want to worry about that while implementing the filters, so we have an abstraction named the ``UniversalTableManager`` which does the conversion and the rest of the code does not know how it happened.

The implementation for tabular data is straightforward. For xml we are still discussing which way it will be implemented. For the moment, an xml is seen as multiple tables. For example for a html file ``package.examplefile`` would be a table, and also ``package.examplefile.body`` and also ``package.examplefile.body.p``. The last one would return a table of all paragraphs that are directly in the body element.

For the conversion from php-object to the table and back: see universalfilters/tablemanager/tools for the conversion classes

> Editor note: maybe we should some sort of Xquery expression to describe which elements we want to use as a table. However maybe that's just overkill... And maybe we should implement that as a filter itself...

### The ``UniversalTable``

We also need some kind of structure/class to save the data in. I called it the ``UniversalTable`` and it is located in the "universalfilter/data" folder.

The ``UniversalTable`` is build of two parts: ``UniversalTableHeader`` and ``UniversalTableContent``. 

The ``UniversalTableHeader`` contains information about the table but not the content. So it contains the names of the columns, information about links to other tables, if the table contains only one column or could contain more than one,... 
It contains a array of ``UniversalTableHeaderColumnInfo``-objects. Which keep the information for the individual columns. 

Note that the name of a column is not a string but an array. For example the column ``Titel`` in the table of the ``gentsefeesten.dag15`` is actually named ``gentsefeesten.dag15.Titel``.

A column also knows whether it is grouped or not.

It also contains an id. Why? Because there can be two column named Title. And how are you going to identify them otherwise?

The ``UniversalTableContent`` contains the data of the table. 
It is build out of a list of ``UniversalTableContentRow``'s. 

A ``UniversalTableContentRow`` contains the data of one row of the table. (Or multiple if it is grouped). It is saved by the id(!) of the column, not by the name.

I tryed to hide the intern structure of the table as much as possible. That's why these classes have a lot of clone-methods or "copyToOtherStructure"-methods. 

### What now?
So we have a representation for our table, and we can convert the data of The DataTank to our representation.

Now we need to execute the query on these tables.

### Let's start in the ``UniversalInterpreter``

When the user want to evaluate something he calls the interpret($tree) method on this class. 

First we could optimize the tree, but that's not implemented yet.

So we start executing it...

### Executing the query.

Evaluation happens in two steps, first we check the tree and create all headers for the tables we will return. Second we execute all querys.

The UniversalInterpreter looks which filter is at the top of the tree and creates an executer for it. 
It creates an environment(*) with an empty table and then asks the executer to create his header and execute his filter. 

(*) = see later.

### In the executer, evaluating the headers (simplified version)

This executer first looks which filter is underneath him, creates an executer for it and asks his header. This executer does the same, and so on. When we reach an identifier the recursion stops and it asks the header to the ``UniversalTableManager`` and returns it. And then we go back up. The filters combine and create new headers and return these. Till we are back at the top.

### In the executer, evaluating the content (simplified version)

Evaluating content also happens recursive. Filters combine tables and return these...
There are also special kinds of tables (this information is kept in the header): a column is a table, a table with one row is a table, and a table which will always return one column and one row is also a table. (But some need special threatment)

### What with expressions? => The ``Environment``

ColumnSelectionFilters and FilterByExpressionFilters also evaluate expressions. But these depend on the data in the source. 

So, we first execute the source-filter and then give the result in the ``Environment`` to the expressions. This way they can access columns and combine them in all kind of ways to return true (case of the FilterByExpressionFilter) or return a new column (ColumnSelectionFilter).


### ColumnSelectionFilter also has an enviroment, but it has a source too...

Indeed, but the Environment of the ColumnSelectionFilter is only used if it is used in nested query's. So it is used if there is a ColumnSelectionFilter in the expression of a ColumnSelectionFilter or a FilterByExpressionFilter.

### That's the flow of the execution. 

To see how the individual filters work you could look in the code of the Executers.

They are splitted in three methods: (see base class UniversalFilterNodeExecuter)

 - ``initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter)``
  where ``$filter`` is the matching node in the Filter Syntax Tree, where ``$topenv`` is the Environment used to evaluate the expression and ``$interpreter`` is the object that is used to get an executer for a certain Filter Syntax Node.
 - ``getExpressionHeader()`` which gets the Header of the table it will return.
 - ``evaluateAsExpression()`` which evaluates the expression and returns the content.



