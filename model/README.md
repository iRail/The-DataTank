# model

This folder represents the entire resource model that is being used throughout the framework. In the following sections every important class will be explained.

## ResourcesModel

This is one of the most important classes in the entire framework. It represents the entire resourcesmodel, meaning it provides functionality to ask, alter, create and delete resources. It mainly uses factories (Core, Installed,Ontology, GenericResource -factory ) to handle all of its requests. To alter the back-end it uses DBQueries.class.php, which contains all of the SQL statements done to the back-end. A third major interest of the ResourcesModel is the Doc.class.php. This class contains the documentation of EVERYTHING IN THE UNIVERSE..... The DataTank universe that is ofcourse. Everytime something changes in the back-end through the ResourcesModel, it will notify the Doc class to update its documentation, keeping our documentation up to date 24/7.

// TODO go over all the other classes.