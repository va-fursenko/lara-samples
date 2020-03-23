<?php

namespace App\Traits;

/**
 * Хоть и не лара, но на входе в проект столкнулся с тем, что в некоторых участках кода авторы не знали, что вложенные объекты 
 * при использовании clone не клонируются, а передаются по ссылке. Или знали, но неправильно это обходили.
 *
 * Trait Cloneable
 * @package App\Traits
 *
 * Рекурсивное клонирование объектов и массивов
 *
 * @copyright Online Express, Ltd. (www.online-express.ru)
 * @author Viktor.Fursenko
 * @project oex
 * @date 24.10.2019
 * @time 12:10
 * @version 1.0
 * @link
 */
trait RecursiveClone
{
    /**
     * Рекурсивное клонирование вложенных объектов
     */
    public function __clone()
    {
        foreach ($this as $field => $value) {
            if (is_object($value) || is_array($value)) {
                $this->$field = $this->cloneValue($value);
            }
        }
    }

    /**
     * Рекурсивное клонирование переменной
     *
     * @param object|mixed $value
     * @return object|mixed
     */
    private function cloneValue($value)
    {
        if (is_object($value)) {
            $result = clone $value;
            foreach ($result as $field => $subValue) {
                if (is_array($subValue) || is_object($subValue)) {
                    $result->$field = $this->cloneValue($subValue);
                }
            }
            return $result;
        }
        if (is_array($value)) {
            foreach ($value as $key => $subValue) {
                if (is_array($value) || is_object($value)) {
                    $value[$key] = $this->cloneValue($subValue);
                }
            }
            return $value;
        }
        return $value;
    }
}
