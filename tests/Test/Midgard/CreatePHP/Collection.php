<?php

namespace Test\Midgard\CreatePHP;

class Collection
{
    public function getTitle()
    {
        return 'the collection title';
    }

    public function getSubject()
    {
        return '/the/subject/collection';
    }

    public function getChildren()
    {
        return array(
            new Model('title 1', 'content 1', '/the/subject/model/1'),
            new Model('title 2', 'content 2', '/the/subject/model/2')
        );
    }

}