[CreatePHP Documentation](index.md)

# Reference

## Type system: Types, Entities and Collections

CreatePHP defines interfaces for the definition of RDF types, properties and
collections. Then it defines extended interfaces for types bound to actual
values. Think of: type = class, entity = object.

Additionally, type definitions may implement the NodeInterface to expose
functionality to render themselves in a DOM-like logic.

The normal workflow is to create the Type, PropertyDefinition and
CollectionDefinition instances, configure eventual settings (including Node
settings if they implement NodeInterface) and then bind the type to a domain
model object with createWithObject. createWithObject returns the Entity for
this type bound to the value.


### Collections

A collection is a list of children entities of an entity. A collection may
contain children of different types, but the metadata must specify the allowed
child types. In the RDFa model, collections have a couple of attributes:

* ``about`` the containing subject (can be inherited from containing HTML)
* ``rel`` identifies the HTML element as a container of a collection. The value
of the ``rel`` attribute is irrelevant for create.js and VIE.
* ``rev`` if present, defines the attribute to be used on newly created entities
in that collection that links back to the containing entity as specified in
``about``. Createphp uses this to determine the parent entity when persisting a
new entity.


## Metadata Factory

To avoid building the type tree with verbose code, there is the
Metadata\RdfTypeFactory. It provides TypeInterface instances for given class
names. There are several drivers available:

* RdfDriverArray: You pass in an array of configuration when bootstrapping the
  driver (i.e. through ArrayLoader)
* RdfDriverXml: Reads XML files with configuration, following a naming scheme
* RdfDriverFeelingLucky: Uses introspection to guess at properties. Good for a
  quick hack, but does not produce meaningful RDF.

Look at the driver phpdoc for the exact syntax to use for configuration.
