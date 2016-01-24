<?php

namespace Article\Model;

use Micro\Database\Model\ModelAbstract;
use App\Model\Languages;

class ArticlesLang extends ModelAbstract
{
    protected $_name = 'articles_lang';

    protected $_referenceMap = array(
        'Article' => array(
            'columns' => 'article_id',
            'refTableClass' => Articles::class,
            'refColumns' => 'id',
            'onDelete'  => self::CASCADE,
        ),
        'Language' => array(
            'columns' => 'language_id',
            'refTableClass' => Languages::class,
            'refColumns' => 'id',
            'onDelete' => self::CASCADE,
        )
    );
}