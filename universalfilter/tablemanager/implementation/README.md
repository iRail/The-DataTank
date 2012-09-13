Where The Abstract Filter Layer and The DataTank come together...
=================================================================

This folder contains the implementation of the interface ``universalfilter/tablemanager/IUniversalFilterTableManager.interface.php`` specific for The-DataTank.

If you want to use The Abstract Filter Layer in another software-package, you need to reimplement the interface ``IUniversalFilterTableManager``.
You can find more information on how to do that in ``universalfilter/tablemanager/README.md``.



Identifiers for tables
----------------------
(as interpreted by the UniversalTableManager)

If you have a resource `data` in a package `testpackage`, the name of the table is: `testpackage.data`.

If you also have restparameters it becomes: `testpackage.data.restparam.restparam`.

With multiple packages: `testpackage.subpackage.data.restparam.restparam`.

Hiërarchical data is divided in multiple tables. E.g. if this data is placed in `/package/test/`:
    <root>
        <a x="x" y="y">
            <z d="d"/>
        </a>
        <a x="x" y="y">
            <z d="d"/>
        </a>
        <a x="x" y="y">
            <z d="d"/>
        </a>
        <b x="x" y="y">
            <z e="e"/>
        </b>
    </root>

You have a table `package.test`:
    | index |   value    |
    |   a   | <<object>> |
    |   b   | <<object>> |

You have a subtable for "a": `package.test:a`:
    |   x   |   y   |     z      |
    |   x   |   y   | <<object>> |
    |   x   |   y   | <<object>> |
    |   x   |   y   | <<object>> |

And also a subtable "a.0.z": `package.test:a.0.z`:
    |   index   |   value   |
    |     d     |     d     |


About the implementation
------------------------

### So, we need to convert the data in The DataTank to tables...

The implementation for tabular data is straightforward. 

For the conversion from php-object to the table and back: see universalfilters/tablemanager/implementation/tools for the conversion classes

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
   