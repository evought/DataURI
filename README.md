README.md

Eric Vought

2015-01-25 DataURI PHP library

#What This Project Is#

The DataUri class provides a convenient way to access and construct 
data URIs, but should not be relied upon for enforcing RFC 2397 standards.

This class will not:

- Validate the media-type provided/parsed
- Validate the encoded data provided/parsed

Examples of how to use the class are in FlyingTopHat's blog post, ["Using Data URI's in PHP"](http://www.flyingtophat.co.uk/blog/2012/09/08/using-data-uris-in-php.html).

#Source and History#

DataURI is a class originally written by [FlyTopHat](http://www.flyingtophat.co.uk) and was used as an example in a [2012 blog post](http://www.flyingtophat.co.uk/blog/2012/09/08/using-data-uris-in-php.html) and as a [Gist](https://gist.github.com/FlyingTopHat/3661056).

In 2014, I forked the Gist in order to package it as a micro-library for use by Composer as a dependency in other projects.
Using a Gist-based micro-library in composer requires creating a custom VCS-based repository in your composer.json. This is clunky but acceptable in an application but becomes difficult if the host project is itself used as a Composer-dependency because the custom repository definition is not (by design) included in the host project.
The repository definition and package requirement must be repeated in each enclosing project--- effectively defeating the purpose of automatic dependency management.

So, in 2015, I forked my fork of DataURI, this time to move it to a full-fledged GitHub repository so that it could be made into an actual composer-managed library in the Packagist repository and the extra machinery needed to turn it into
a production component could be added.

#Licensing#

All of my modifications and additions are under an MIT license. FlyTopHat, by publishing as a Gist, granted a license to view and fork his code (and by publishing it as demonstration code in a tutorial conveyed a broader intent to share it generally in an educational context). Composer/Packagist is simply an automated method for forking Github projects, allowing one to track where the code came from and automatically fetch changes. Therefore my interpretation is that packaging the code as a Composer dependency through Packagist is permitted through the terms already granted. Without licensing clarification from FlyingTopHat, other uses might be questionable.

