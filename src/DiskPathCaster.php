<?php


namespace Colombo\Libs\DiskPathTools;


use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class DiskPathCaster implements CastsAttributes
{
    
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return mixed|void
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return DiskPathInfo::parse($value);
    }
    
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!($value instanceof DiskPathInfo)) {
            throw new \Exception("Value of $key must instanceof ".DiskPathInfo::class);
        }
        return $value->__toString();
    }
}