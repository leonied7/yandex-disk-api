<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 15.03.2019
 * Time: 11:14
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Http;


use Leonied7\Yandex\Disk\Query\Builder;

class Response extends Message
{
    const OK_STATUS = '200 OK';
    const TYPE_XML = 'application/xml';
    const TYPE_TEXT = 'text/plain';

    protected $code;
    protected $type;

    public function __construct(Builder $builder, $code, $contentType)
    {
        $this->code = $code;
        $this->type = $contentType;
        parent::__construct($builder);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getType()
    {
        return $this->type;
    }
}