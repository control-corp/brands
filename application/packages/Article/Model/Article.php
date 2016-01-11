<?php

namespace Article\Model;

use Micro\Database\Table\TableAbstract;

class Article extends TableAbstract
{
    protected $_name = 'articles';

    public function __construct($config = array())
    {
        static::setDefaultAdapter(app('db'));

        static::setDefaultMetadataCache(app('cache'));

        parent::__construct($config);
    }
}