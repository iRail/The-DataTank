The Abstract Filter Layer : ``IUniversalTableManager``
======================================================

The UniversalTableManager implements an abstraction to see everything as a collection of tables.
So, if you want to use this library in some kind of software, you need to implement this interface.

It's a simple interface with four methods, with two of the four methods that are optional.

``getTableHeader($globalTableIdentifier)``
------------------------------------------
Implementing this method is required. What does it do? 
It is supposed to give back the header of the table represented by $globalTableIdentifier.

### The globalTableIdentifier... What are possible string?
You can interpret $globalTableIdentifier any way you want...

For example, it could contain string in the format ``package.package.resource.subtable`` (as implemented in The-DataTank), 
but it could also contain uri's or some other name for something that returns a table.

*The filter-implementation does not care what kind of string you use for the representation of a table!*

But there are some things you have to keep in mind:
 - the SQLTokenizer *does not* tokenize this query correctly:
    
        SELECT Title, Date FROM http://foo.bar/foo.table

   (The tokenizer splits on "/" and that's not what you want in this case...)

   But if you correct the Tokenizer, it will work...

 - The Spectql tokenizer is written to convert .../package/resource{...} into: "package.resource", 
   so you have to change that too if you want to use for example uri's.

Conclusion: $globalTableIdentifier can be anyting. It's whatever the users use to identify a table.

### What should this method do?
This method should give back an object of the class ``universalfilter/data/UniversalFilterTableHeader.class.php``.
This class contains information about the names of the columns.

To construct a header you call ``new UniversalFilterTableHeader($columns, false, false);``
Where columns is an array of ``UniversalFilterTableHeaderColumnInfo``-objects.

These objects require at least one argument in the constructor: the full name of the column.
For example, if the full name is ``array("foo", "bar", "Title");`` you can access this field by the names:

    Title
    bar.Title
    foo.bar.Title

Other than the full name, you can also give information about links to other tables. (Some sort of FK's)

But that information is not used yet in the implementation of the filters...
(The planned use was to implement the SQL operation "->")

``getTableContent($globalTableIdentifier, $header)``
----------------------------------------------------
This is also a required method. This method gives back the content of the table represented by ``$globalTableIdentifier``.
You also get the $header you created in ``getTableHeader``.

### What should this method do?
This method should give back an object of the class ``universalfilter/data/UniversalFilterTableContent.class.php``.

What does a class like that contain? It contains rows. (of the class ``universalfilter/data/UniversalFilterTableContentRow.class.php``)

So you add all rows to this class. 

#### A row...
You create a new row by calling ``$row = new UniversalFilterTableContentRow()``.
Then you can add new values by calling ``$row->defineValue($columnId, $theValue)``.

``$theValue`` is the value you want to add.  
``$columnId`` is a bit more complex... 
ColumnId's are automatically generated for each column you added to the header in the previous method. 
So, you first have to search for the correct columnId in the header, before you can add a value.


The optional methods
--------------------

### ``runFilterOnSource($query, $sourceId)``
If you don't want to implement this method: just ``return $query;``.

### ``getSourceIdFromIdentifier($globalTableIdentifier)``
If you don't want to implement this method: just ``return "";``.

### What are these methods supposed to do?
They are used to pass the Abstract Filter Tree to execute directly to the source.
You can find more information on this in the README.md in ``universalfilter/sourcefilterbinding``

How do we transform PHP-objects to tables ?
-------------------------------------------

This transformation will be explained by a series of examples. First lets assume that we do not
go deeper into an object. (e.g. we do not zoom in on a piece of the object we want to query.) The 
return value I get from a the ResourceModel will either be an stdClass (object) or an array.

Case 1) We get an object.
    In case of an object, the names of the datamembers are important, and have to be used, not thrown away. Ofcourse
    the values of these datamembers are important, and have to be saved as well. This will result in a somewhat hash-map
    like datastructure (e.g. { datamember1:"value1", datamember2:"value2" } ).

    
    Resulting table:

    | index | value |
    |:-----:|:-----:|
    | field1| value1|
    | field2| value2|

Case 2) We got an array.
    With arrays we make a distinction between associative arrays and numerical arrays. 
    We define an associative array when minimum 1 field is a not a number.

    In **numerical arrays** the values kept in it are just like rows in the table. Thus, the values have to be interpreted as rows.

    a) If an object is the value of a numerical index then all the fields of this object become columnheadernames.
        e.g.: 
            [
              {field1:"value1", field2:"value2"},
              {field1:"value1b", field2:"value2b"}
            ]
       Resulting table:

       | field1 | field2 |
       |:------:|:------:|
       | value1 | value2 |
       | value1b| value2b|


    b) If the numerical index's value contains a string of some sort then the table has a column named "value".
        e.g.: 
            [
              "string1",
              "string2"
            ]
       Resulting table:
          
       | value |
       |:-----:|
       |string1|
       |string2|

    c) If the value is an array, the columnames become "index_"+$i.
        e.g.: 
            [
              ["string1", "A" => "B"],
              ["string2"]
            ]
       Resulting table:
          
       | index_1 | index_A |
       |:-------:|:-------:|
       | string1 |    B    |
       | string2 | null    |
            

    With associative arrays the indexes, or more correctly keys, are probably also important. Note that we don't save the information of "keys" in transforming
    numerical arrays to tables. To save the "key" information on which a certain value is mapped, we add an extra "index" column to the resulting table. The rest
    of the info is transformed to a table in the exact same way as numerical arrays are being transformed.

       e.g.: 
           [
             "SomePK1" => {veld1:"value1", veld2:"value2"},
             "SomePK2" => {veld1:"value1b", veld2:"value2b"}
           ]

       Resulting table:

       | index | field1 | field2 |
       |:-----:|:------:|:------:|
       |SomePK1| value1 | value2 | 
       |SomePK2| value1b| value2b|
