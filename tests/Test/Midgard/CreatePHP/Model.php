<?php

namespace Test\Midgard\CreatePHP;

class Model
{
    public function getTitle()
    {
        return 'the title';
    }
    public function getContent()
    {
        return 'the content';
    }
    public function getTags()
    {
        return array('test', 'php');
    }
    public function getChildren()
    {
        return array(new Child());
    }
}