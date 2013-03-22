CreatePHP
=========

This is a small standalone library designed to make it easier to integrate [Create.js](http://createjs.org)
into existing PHP applications/frameworks. You can see a live demonstration of an integration
with the MidCOM framework under http://demo.createphp.org

[![Build Status](https://secure.travis-ci.org/flack/createphp.png?branch=master)](http://travis-ci.org/flack/createphp)

Usage
-----

To use CreatePHP, you need to implement the RdfMapperInterface and provide metadata to map between
your domain models and RDF. See the Mapper subfolder for a couple of abstract classes that might
be useful to write your own mapper.

Installation
-----

CreatePHP is available on [Packagist](https://packagist.org/packages/midgard/createphp), so you can
simply include it in your `composer.json`. Or you download it the old-fashioned way and register it
in any PSR2-compatible autoloader.

Documentation
--------

Documentation is available in the [documentation/ folder](documentation/index.md)


Word of Warning
---------------
The code is still under development. While it's kept in a constantly running
state, please note that the API might still change. Suggestions and
feedback are of course welcome!
