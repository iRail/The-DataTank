# The HTTP Range 14 problem

The router passes an URI to a controller. To understand what's happening you need to understand this:

When you want to refer to something, you can use URIs (unique resource identifiers). There is however a difference between a real-world object and the representation of this thing. For the more academic: what I'm going to try to explain is the HTTP range 14 discussion. Look it up if you want to know more.

For instance, I can reference you as a person inside the iRail domain and give you a URI (don't confuse this with an URL): iRail.be/yourname. It is just a way to identify a real-world object. iRail however knows some things about you. For instance, we know what projects you're working on, we know which meetings you attended and maybe we provided you with an e-mail address. This information represents you within iRail. To get (with HTTP GET) this information, we do not need to GET your person's URI, but we need to get a URI of your representation within our organisation. This will be: iRail.be/yourname.about - This URI will do content negotiation and return the right output format  (json, xml, rdf+xml, html...).

http://www.w3.org/TR/cooluris/#r303gendocument - this is an interesting read. The page describes all possible solutions. 4.2 is more or less our solution, although we use real/world/uri.about as our representation URI and not "id" redirected to a "doc" in the middle of the URI (this is the dbpedia approach).

There are a couple of possible scenarios the controllers need to be able to handle:

## Read scenarios

### Scenario 1: GET request on a real-world object URI

eg: http://api.iRail.be/NMBS/Liveboard/Brussels-North

Since this is not the representation, but a URI for the real-world arrival/departure panels at the Brussels North station, we need to redirect the user to the representation. We will send him a HTTP/1.1 303 See Other message. The location header should contain:

Location: http://api.iRail.be/NMBS/Liveboard/Brussels-North.about

### Scenario 2: GET request on .about

eg: http://api.iRail.be/NMBS/Liveboard/Brussels-North.about

We'll do content negotiation (see 4.7 http://www.w3.org/TR/cooluris/#implementation) using the Accept headers of the request. If no or no valid accept parameter is sent, an error will be shown. When a valid parameters is set, we'll continue the process of getting an object model and print it according to the right formatter specified in the header. In the response headers, the Content-Location parameter will get http://api.iRail.be/NMBS/Liveboard/Brussels-North.json when the Accept headers specified json as prefered result.

### Scenario 3: GET request on .json/.xml/.xxx

eg: http://api.iRail.be/NMBS/Liveboard/Brussels-North.json

We don't have to do content negotiation since the representation type is in the URI. We can select the formatter directly and represent the object neatly.

## Create, update & delete scenarios

Reading a resource, or GET-ting it, is only 1 part of our CRUD system. We still have 3 HTTP methods left: PUT, POST and DELETE, which represent respectively Create, Update and Delete actions.

### Scenario 1: PUT on a collection

eg: PUT https://user:pwd@api.thedatatank.com/cityofghent/

Depending on what PUT parameters you add, this will add a resource to the package cityofghent.

### Scenario 2: DELETE, PUT or POST on a representation

You cannot alter representations. An exception will be returned.

### Scenario 3: PUT-ting a resource or POST-ing a package

We do not allow you to use PUT on a resource. You can only add resources to a package.

You cannot POST to a package since a package does not have any kind of data nor meta-data in The DataTank.

#### Scenario 4: POST-ing a resource

eg: POST https://usr:pwd@api.thedatatank.com/cityofghent/heatmap

Provided with the right parameters, this can change the meta-data of the heatmap resource. You can also add filters to set meta-data on a subresource.

# Controllers

To implement this using glue and our router, we did this:

## AController.class.php

The abstract Controller contains 4 functions: GET(), DELETE(), POST() and PUT(). Every controller extends from this class.

## RController.class.php

The R is for Read (cRud). It will handle all calls to a specific representation. It returns exceptions on calls to DELETE, UPDATE or POST.

If the format .about is given, the FormatterFactory is going to do content negotiation. This means: check the Accept header and choose the best one. If no format is found, return a default one.

## CUDController.class.php

CUD is for Create, Update and Delete (CrUD). It performs the right model functions to make the call happen. When GET is used, it seems like they have done a GET request to a real-world object. We're going to redirect them (HTTP 303 See Other) to the .about URI.


