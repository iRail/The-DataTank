The Abstract Filter Layer : Common Functionality
================================================

This package contains some common functionality that is used through the layer.

BigList, BigMap and BigDataBlockManager
---------------------------------------

I use this classes in the implementation of the filter so I don't have to worry about very big datasets. 
If that's the case, you just have to implement these classes efficiëntly...

### BigList
Implements a list that can possibly grow very big.

Current implementation:
Divides the list in blocks of 100000 items and saves those individualy in the BigBlockDataBlockManager...

### BigMap
Implements a map that can possibly grow very big.

Not implemented correctly yet. It still keeps all the data in memory...


### BigDataBlockManager

This class is used by BigList and BigMap

You can give this class a block of data with a name and then later ask a block again (by it's name).
The BigDataBlockManager keeps 30 blocks of data in memory, if there are more it writes them to file.

Please note that's the blocks should be "big enough", as only 30 blocks are kept in memory... (!)


### The current implementation

BigMap is not implemented because BigDataBlockManager wants blocks of data that are big enough...
For now it does everything in memory.

If you would need to implement BigMap (you need Group By on a very big dataset), 
I suggest you to implement BigMap as a Lineair Hashing Table (<http://en.wikipedia.org/wiki/Linear_hashing>)
where each "bucket" is a block...

Other things...
---------------

HashString contains a global function for converting a string to an other unique string where not all characters are allowed.
Used in BigDataBlockManager (for mapping the name of a block to a file) and in the BaseHashingFilterExecuter (for grouping and distinct).