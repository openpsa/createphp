createphp
=========

This is a small standalone library designed to make it easier to integrate CreateJS
into existing PHP applications/frameworks.

Usage
-----

To use createphp, you need to implement the rdfMapper class, instantiate it with a
configuration for your data source and then you're good to go

```php
<?php
$object = load_your_data_from_somewhere();

$config = array
(
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
                'rdf_name' => 'dcterms:title',
            ),
            'content' => array(
                'rdf_name' => 'sioc:content',
            ),
        ),
    )
);

$mapper = new my_mapper_class;
$manager = new createphp\arrayManager($mapper, $config);
$controller = $manager->get_controller('blog_article', $object);

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
individual fields separately. If you include the CreateJS files into your page,
all specified fields will become editable. To actually save the data, you will
have to provide an access point for the REST service, like so:

```php
<?php
$mapper = new my_mapper_class;
$manager = new createphp\arrayManager($mapper, load_my_configuration_from_somewhere());
$controller = $manager->get_controller('blog_article');

$received_data = json_decode(file_get_contents("php://input"), true);
$service = new createphp\restservice($mapper, $received_data);

$service->run($controller);
?>
```

Word of Warning
---------------
The code is still very much in development. While it's kept in a constantly running
state, please note that the API might still change considerably. Suggestions and
feedback are of course welcome!