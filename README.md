CreatePHP
=========

This is a small standalone library designed to make it easier to integrate [Create.js](http://createjs.org)
into existing PHP applications/frameworks. You can see a live demonstration of an integration
with the MidCOM framework under http://demo.contentcontrol-berlin.de

Usage
-----

To use CreatePHP, you need to implement the RdfMapper interface, instantiate it with a
configuration for your data source, and then you're good to go

```php
<?php
$object = load_your_data_from_somewhere();

$config = array
(
    'workflows' => array(
        'delete' => 'my_delete_workflow_class'
    ),
    'controllers' => array(
        'blog_article' => array(
            'storage' => 'some_db_table',
            'attributes' => array(
                'typeof' => 'sioc:Blog',
            ),
            'vocabularies' => array(
               'dcterms' => 'http://purl.org/dc/terms/',
               'sioc' => 'http://rdfs.org/sioc/ns#'
            ),
            'properties' => array(
                'title' => array(
                    'attributes' => array(
                        'property' => 'dcterms:title'
                    )
                ),
                'content' => array(
                    'attributes' => array(
                        'property' => 'sioc:content'
                    )
                )
            )
        )
    )
);

$mapper = new my_mapper_class;
$loader = new Midgard\CreatePHP\ArrayLoader($config);
$manager = $loader->getManager($mapper);
$controller = $manager->getController('blog_article', $object);
```

### Rendering HTML

Using the default markup is as simple as this:

```php
<?php
echo $controller
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
$controller->title->setAttribute('class', 'headline');
$controller->content->setAttribute('class', 'content-inner');
$controller->content->setTemplate('<div class="content-outer"><span __ATTRIBUTES__>__CONTENT__</span></div>');

echo $controller->renderStart('article');
$i = 0;
foreach ($controller->getChildren() as $fieldname => $node) {
    $node->setAttribute('id', 'childnode-' . $i);
    echo $node;
    $i++;
}
echo $controller->renderEnd();
?>
```

If you include the Create.js files into your page, all specified fields will become editable.

### Implementing the REST backend

To actually save the data, you will have to provide an access point for the REST service, like so:

```php
<?php
$loader = new Midgard\CreatePHP\ArrayLoader(load_my_configuration_from_somewhere());
$manager = $loader->getManager(new my_mapper_class);
$controller = $manager->getController('blog_article');

$received_data = json_decode(file_get_contents("php://input"), true);
$service = $manager->getRestHandler($received_data);

$jsonld = $service->run($controller);
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

Word of Warning
---------------
The code is still very much in development. While it's kept in a constantly running
state, please note that the API might still change considerably. Suggestions and
feedback are of course welcome!
