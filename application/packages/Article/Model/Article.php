<?php

namespace Article\Model;

use Micro\Database\Table\TableAbstract;

class Article extends TableAbstract
{
    protected $_name = 'articles';

    public function __construct()
    {
        parent::__construct(app('db'));
    }
}