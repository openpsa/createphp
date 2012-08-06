CreatePHP
=========

This is a small standalone library designed to make it easier to integrate [Create.js](http://createjs.org)
into existing PHP applications/frameworks. You can see a live demonstration of an integration
with the MidCOM framework under http://demo.contentcontrol-berlin.de

[![Build Status](https://secure.travis-ci.org/flack/createphp.png?branch=master)](http://travis-ci.org/flack/createphp)

Usage
-----

To use CreatePHP, you need to implement the RdfMapperInterface and provide metadata to map between
your domain models and RDF.


Tutorial
--------

This tutorial shows how to use CreatePHP with the ArrayLoader that bootstraps
Manager, which is a sort of micro service container.

Instantiate ArrayLoader  with a configuration for your data source:

```php
<?php
$object = load_your_data_from_somewhere();

$config = array
(
    'workflows' => array(
        'delete' => 'my_delete_workflow_class'
    ),
    'types' => array(
        'My\\Blog\\Model\\Article' => array(
            'config' => array(
                'storage' => 'some_db_table',
            ),
            'typeof' => 'sioc:Blog',
            'vocabularies' => array(
               'dcterms' => 'http://purl.org/dc/terms/',
               'sioc' => 'http://rdfs.org/sioc/ns#'
            ),
            'properties' => array(
                'title' => array(
                    'property' => 'dcterms:title'
                ),
                'content' => array(
                    'property' => 'sioc:content'
                ),
            ),
        ),
    )
);

$object = new \My\Blog\Model\Article('Some title', 'Article content');
$mapper = new my_mapper_class;
$loader = new Midgard\CreatePHP\ArrayLoader($config);
$manager = $loader->getManager($mapper);
$entity = $manager->getType(get_class($object), $object);
```

### Rendering HTML

Using the default markup is as simple as this:

```php
<?php
echo $entity
?>
```

This will output something like

```html
<div about="http://some_domain.com/some_unique_identifier"
     xmlns:dcterms="http://purl.org/dc/terms/"
     xmlns:sioc="http://rdfs.org/sioc/ns#"
     typeof="sioc:Blog">

     <div property="dcterms:title">
         Some title
     </div>

     <div property="sioc:content">
         Article content
     </div>
</div>
```

Of course, the markup is completely configurable, and you can also render the
individual fields separately:

```php
<?php
$entity->title->setAttribute('class', 'headline');
$entity->content->setAttribute('class', 'content-inner');
$entity->content->setTemplate('<div class="content-outer"><span __ATTRIBUTES__>__CONTENT__</span></div>');

echo $entity->renderStart('article');
$i = 0;
foreach ($entity->getChildren() as $fieldname => $node) {
    $node->setAttribute('id', 'childnode-' . $i);
    echo $node;
    $i++;
}
echo $entity->renderEnd();
?>
```

If you include the Create.js files into your page, all specified fields will become editable.

### Implementing the REST backend

To actually save the data, you will have to provide an access point for the REST service, like so:

```php
<?php
$loader = new Midgard\CreatePHP\ArrayLoader(load_my_configuration_from_somewhere());
$manager = $loader->getManager(new my_mapper_class);
$type = $manager->getType('My\\Blog\\Model\\Article');

$received_data = json_decode(file_get_contents("php://input"), true);
$service = $manager->getRestHandler($received_data);

$jsonld = $service->run($type);
send_as_json($jsonld);
?>
```

### Registering Workflows

In addition to the Create.js's builtin Create and Update support, you can also define additional workflows.
 These are read per-object when an appropriate content field is focused in the HTML page. Create.js then sends a
GET request which can hold the current model ID. You can implement a backend URL like so:

```php
<?php
$loader = new Midgard\CreatePHP\ArrayLoader($config_array);
$manager = $loader->getManager(new my_mapper_class);

$manager->registerWorkflow($workflow_name, new my_workflow_class);

$toolbar_config = $manager->getWorkflows($object_identifier);
send_as_json($toolbar_config);
?>
```

See the Create.js documentation for available configuration options in workflows

# Reference

## Type system: Types, Entities and Nodes

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


Word of Warning
---------------
The code is still very much in development. While it's kept in a constantly running
state, please note that the API might still change considerably. Suggestions and
feedback are of course welcome!
