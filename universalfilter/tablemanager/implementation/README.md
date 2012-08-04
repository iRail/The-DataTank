Where The Abstract Filter Layer and The DataTank come together...
=================================================================

This folder contains the implementation of the interface ``universalfilter/tablemanager/IUniversalFilterTableManager.interface.php`` specific for The-DataTank.

If you want to use The Abstract Filter Layer in another software-package, you need to reimplement the interface ``IUniversalFilterTableManager``.
You can find more information on how to do that in ``universalfilter/tablemanager/README.md``.


About the implementation
------------------------

### So, we need to convert the data in The DataTank to tables...

The implementation for tabular data is straightforward. For xml we are still discussing which way it will be implemented. For the moment, an xml is seen as multiple tables. For example for a html file ``package.examplefile`` would be a table, and also ``package.examplefile.body`` and also ``package.examplefile.body.p``. The last one would return a table of all paragraphs that are directly in the body element.

For the conversion from php-object to the table and back: see universalfilters/tablemanager/implementation/tools for the conversion classes

> Editor note: maybe we should some sort of Xquery expression to describe which elements we want to use as a table. However maybe that's just overkill... And maybe we should implement that as a filter itself...

### We also implemented the runFilterOnSource method to run filters directly on the source.

For more info, ask Jan ;)


Ideas about future development
------------------------------

 - Add ".?" tables.
   E.g. If you have a resource ``gentsefeesten.dag15``, with columnNames: Titel, Datum, ...
   Then the table: ``gentsefeesten.dag15.?`` would return the following table:
   
   <table>
      <tr>
         <th>Field</th>
      </tr>
      <tr>
         <td>Titel</td>
      </tr>
      <tr>
         <td>Datum</td>
      </tr>
      <tr>
         <td>...</td>
      </tr>
   </table>
   