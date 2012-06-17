createphp
=========

This is a small standalone library designed to make it easier to integrate CreateJS
into existing PHP applications/frameworks.

Usage
-----

To use createphp, you need to implement the rdfMapper interface, instantiate it with a
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
                'title' => array
                (
                    'attributes' => array
                    (
                        'property' => 'dcterms:title'
                    )
                ),
                'content' => array(
                    'attributes' => array
                    (
                        'property' => 'sioc:content'
                    )
                )
            )
        )
    )
);

$mapper = new my_mapper_class;
$loader = new createphp\arrayLoader($config);
$manager = $loader->get_manager($mapper);
$controller = $manager->get_controller('blog_article', $object);
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
$controller->title->set_attribute('class', 'headline');
$controller->content->set_attribute('class', 'content-inner');
$controller->content->set_template('<div class="content-outer"><span __ATTRIBUTES__>__CONTENT__</span></div>');

echo $controller->render_start('article');
$i = 0;
foreach ($controller->get_children() as $fieldname => $node)
{
    $node->set_attribute('id', 'childnode-' . $i);
    echo $node;
    $i++;
}
echo $controller->render_end();
?>
```

If you include the CreateJS files into your page, all specified fields will become editable.

### Implementing the REST backend

To actually save the data, you will have to provide an access point for the REST service, like so:

```php
<?php
$loader = new createphp\arrayLoader(load_my_configuration_from_somewhere());
$manager = $loader->get_manager(new my_mapper_class);
$controller = $manager->get_controller('blog_article');

$received_data = json_decode(file_get_contents("php://input"), true);
$service = $manager->get_resthandler($received_data);

$jsonld = $service->run($controller);
send_as_json($jsonld);
?>
```

### Registering Workflows

In addition to the CreateJS's builtin Create and Update support, you can also define additional workflows.
 These are read per-object when an appropriate content field is focused in the HTML page. CreateJS then sends a
GET request which can hold the current model ID. You can implement a backend URL like so:

```php
<?php
$loader = new createphp\arrayLoader($config_array);
$manager = $loader->get_manager(new my_mapper_class);

$manager->register_workflow($workflow_name, new my_workflow_class);

$toolbar_config = $manager->get_workflows($object_identifier);
send_as_json($toolbar_config);
?>
```

See the CreateJS documentation for available configuration options in workflows

Word of Warning
---------------
The code is still very much in development. While it's kept in a constantly running
state, please note that the API might still change considerably. Suggestions and
feedback are of course welcome!