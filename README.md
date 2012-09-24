# Installation

To install the datatank, just clone our repo (develop branch, or master branch starting from 12th of august 2012). Then just deploy it somewhere on your server and 
go to the /installer part of the datatank deployment in your browser. 
I.e. http://myhost/installer, also RENAME the Config.example.class.php to Config.class.php, that should provide with all the necessary info to get you started!

# File structure

The file structure has come a long way since we started our development, here's an overview of what you can find in the different folders and what their purpose is.

aspects
-------

Contains aspects based programming components namely error logging, request logging and caching.

controllers
------------

Contains our controllers, which are one of the earliest stops in which a request is handled. You'll see that
for example CUDController and RController are listed in this folder because CUD and Read operations are different 
from eachother. Also filter end-points such as SQL and spectql have their own way of processing a request, they also
have a separate controller.
 
custom
-------

This folder contains custom implementations concerning our resources. In this folder you will find 
the different resourcestrategies ( CSV, XML, JSON, ... ). This is also the folder where our formatters reside.
Last but not least this is also where our installed resources can be placed, in the folder "package". For some examples
on how these resources are built take a look at some examples: https://github.com/iRail/The-DataTank-examples/.

examples
--------

This folder contains some example data you can use to play around with in the datatank.

includes
--------

This folder contains all the 3rd party software we use to get some stuff done. Every piece is in terms with 
our own code license which is aGPL.

installer
---------

This folder contains all the necessary files and business logic to get the installer webapp working. This 
provides you with a quick and easy wizard to apply your settings to the datatank so it can start opening data.

lib
---

Folder containing the parse engine for lime. Don't pay too much attention to this folder :).

model
-----

This folder contains ALL the files that represent our entire resourcesmodel. Also necessary tools such as 
our queries for the back-end are stored in this folder.

The-Semantifier
----------------

This folder is a placeholder for a semantic plug-in found at https://github.com/iRail/The-Semantifier.

unittests
---------

Contains some simple end-to-end unittests, hasn't been used for a while now but it may come in handy someday, so we won't delete 
this folder just yet.

universalfilter
---------------

This folder contains all the business logic behind our universal filter layer. Upon this layer one can build its own
query languages and let the logic of that language be handled by our universal layer.

Config.example.class.php
------------------------

Rename this to Config.class.php, the installer will look for this file and will urge you to fill this file in with
the necessary information step by step.

router
------

This file will tell our first stop of a HTTP request what URI template has to be handled by what controller.

TDT.class
---------

Contains some utility functions and classes for the datatank.

# Branching

we will be using git in a pattern similar to [Vincent Driessen's workflow](http://nvie.com/posts/a-successful-git-branching-model/). While feature branches are encouraged, they are not required to work on the project.

![Branching Model](http://nvie.com/img/2009/12/Screen-shot-2009-12-24-at-11.32.03.png)

## develop

This is the development branch, this branch will be merged with separate branches where big features will be implemented separatly. When a branch is adult enough to merge it with the development branch, it will be merged and deleted. For small fixes and very small features the development will be adjusted without branching first. This branch is the most up to date version of The DataTank.
Take a look at the issues-section for further discussion and features! At given moments in time we will replace ( or merge ) our develop branch with our master branch. 
This will result in the master having the latest working stable code. This does not mean however we don't have working code in our develop branch. If you want to enjoy 
the latest features, you might want to clone from the develop branch. Also pull requests will only be applied to the develop branch.

## master

As stated in the above section, this will contain the latest stable code and will only be updated a few times a year. The working branch
will always be develop!

## acl

This branch contains some more additional functionality. It provides the possibility to use access lists for requests and resources.

## OAuth

This branch implements OAuth for the datatank. Note that this uses a v1.0a OAuth API! There is however
some trouble with using an OAuth in a API only framework. A small html form is a minimum requirement to make OAuth 
work, and since the datatank does not provide front-end code this had to be a different part of the datatank. AN IMPORTANT NOTE is that
the datatank is part of the iRail NPO and within this NPO there's also a project going on which provides an OAuth proxy. Thus the
usage of OAuth will probably be delegated to this project instead of using the implemented OAuth in our datatank.

## iSoc12

This was a working-on-features-branch during the iRail summer of code. This branch will (or is already) be merged with the develop.

# Coding standards

In order to code properly we use some standards such as

* 1 indent = 4 spaces

* every php files starts with a comment section explaining what the file does, what the author is
  to whom the copyright belongs and what license it holds.
  
* functions are camelCaseNotation()

* variables are also $camelCaseNotation ( although you might see an _ notation here and there, this is mostly when we query variables, if not...it's our fault and bad practise and you should learn from our mistake :) )

In future releases we will be using the [fig standards](http://www.php-fig.org/).

# Read more

If you want to read more about The DataTank please visit http://thedatatank.com for developer documentation, and http://thedatatank.com for introductions.

The DataTank is free software (AGPL, © 2011,2012 iRail NPO, 2012 OKFN Belgium) to create an API for non-local/dynamic data in no time.

Any questions? Add a support issue.

-Pieter, Jan and Lieven
