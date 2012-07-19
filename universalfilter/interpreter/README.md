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

### Executing the query.
Let's start in the UniversalInterpreter. When the user want to evaluate something he calls the interpret($tree) method on this class. 

TODO: more comming soon...