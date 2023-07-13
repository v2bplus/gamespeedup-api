<?php
namespace Services\Api;

use Ramsey\Uuid\Uuid as LIB;

class Uuid
{
    public static function id($name = null)
    {
        if (is_null($name)) {
            $uuid = LIB::uuid4();
        } elseif (stristr($name, 'http') == false) {
            $uuid = LIB::uuid5(LIB::NAMESPACE_DNS, $name);
        } else {
            $uuid = LIB::uuid5(LIB::NAMESPACE_URL, $name);
        }
        return $uuid->toString();
    }
}
