<?php

namespace Article\Model;

use Micro\Database\Model\ModelAbstract;

class Articles extends ModelAbstract
{
    protected $_name = 'articles';

    protected $_dependentTables = array(ArticlesLang::class);
}