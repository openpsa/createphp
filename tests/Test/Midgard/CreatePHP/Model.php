<?php

namespace Test\Midgard\CreatePHP;

class Model
{
    private $title;
    private $content;
    private $subject;

    public function __construct($title = 'the model title',
                                $content = 'the model content',
                                $subject = '/the/subject/model')
    {
        $this->title = $title;
        $this->content = $content;
        $this->subject = $subject;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function getSubject()
    {
        return $this->subject;
    }
}