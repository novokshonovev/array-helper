<?php
namespace dowlatow\helpers;

use Yii;
use yii\base\Object;
use yii\db\ActiveRecord;

class ArrayHelper extends \yii\helpers\ArrayHelper
{
    /**
     * Вычисляет процентное представление элементов массива от базы(100%)
     *
     * @param $base - база для расчета процентов, число принимаемое за 100%
     * @param $array - массив чисел по которому необходимо вычислить проценты
     * @param int $percentageAccuracy - точность представления см. round
     * @return array - массив прецентных представлений, ключи совпадают с ключами входного массива
     */
    public static function percentsOfBase($base, $array, $percentageAccuracy = 0)
    {
        $percentsOfBase = [];
        foreach ($array as $key => $value) {
            $percentsOfBase[$key] = round(($value / $base) * 100, $percentageAccuracy);
        }
        return $percentsOfBase;
    }

    /**
     * Вычисляет массив процентных значений по массиву
     * 1. Вычисляет базу для расчета процентов (значение принимаемое за 100%) как сумму значений массива
     * 2. Вычисляет номализованное или не нормализованное процентное представление элементов от базы
     * @param $array - массив чисел по которому необходимо вычислить проценты
     * @param int $percentageAccuracy - точность представления см. round()
     * @param bool $normalize - если true, то результат номализуется (сумма процентов по массиву = 100%) см. Normalize()
     * @return array|mixed - массив прецентных представлений, ключи совпадают с ключами входного массива
     */
    public static function percents($array, $percentageAccuracy = 0, $normalize = true)
    {
        $base = array_sum($array);
        if ($normalize) {
            return ArrayHelper::normalize(ArrayHelper::percentsOfBase($base, $array, $percentageAccuracy), $percentageAccuracy);
        } else {
            return ArrayHelper::percentsOfBase($base, $array, $percentageAccuracy);
        }

    }

    /**
     * Выполняет простейшую нормализацию массива процентов
     * Сумма процентов не может быть более 100%:
     * 1. Находит максимальное значение (если их несколько, то последнее)
     * 2. Вычисляет нормализованное максимальное значение: 100 - сумма всех остальных значений
     * @param $array - массов процентов для номализации
     * @param int $percentageAccuracy - точность представления см. round()
     * @return mixed - массив прецентных представлений, ключи совпадают с ключами входного массива
     */
    public static function normalize($array, $percentageAccuracy = 0)
    {
        $maxKeys = array_keys($array, max($array));
        if (!empty($maxKeys)) {
            $key         = array_pop($maxKeys);
            $newMax      = 100 - (array_sum($array) - max($array));
            $a           = round($newMax, $percentageAccuracy);
            $array[$key] = $a;
        }
        return $array;
    }


    /**
     * Вычисляет разницу между двумя массивами объектов по заданному полю
     * Поле по которому будет выполняться сравнение, должно быть public и приводится к строке
     * @param array $first
     *              первый массив
     * @param array $second
     *              второй массив
     * @param string $field
     *              имя поля по которому выполняется сравнение
     * @return array
     *              массив, содержащий элементы первого массива, не найденные во втором
     *              Если оба массива изначально пусты или полностью совпадают, то возвращается пустой массив
     */
    public static function diffObjectArrayByField(array $first, array $second, $field)
    {
        if (empty($first) && empty($second)) {
            return [];
        }

        $result = [];

        $firstValues  = self::map($first, $field, $field);
        $secondValues = self::map($second, $field, $field);
        $diffValues   = array_diff($firstValues, $secondValues);

        if (!empty($diffValues)) {
            foreach ($first as $obj) {
                if (in_array($obj->{$field}, $diffValues)) {
                    $result[] = $obj;
                }
            }
        }
        return $result;
    }

    /**
     * Проверяет входит ли объект в массив объектов
     * проверка производится по определенному полю
     * типы объектов не сравниваются
     * @param Object $object
     * @param array $array
     * @param string $field
     * @return bool
     */
    public static function objectInArrayByField(Object $object, array $array, $field)
    {
        if (empty($object) || empty($array) || empty($field)) {
            return false;
        }
        $flatArray = self::map($array, $field, $field);
        return in_array($object->{$field}, $flatArray);
    }

    /**
     * Проверяет наличие AR $object в массиве $object
     * если любой из аргументов пуст позвращается false
     * @param ActiveRecord $object
     * @param ActiveRecord[] $array
     * @return bool
     */
    public static function activeRecordInArray(ActiveRecord $object, $array)
    {
        if (empty($object) || empty($array)) {
            return false;
        }
        foreach ($array as $element) {
            if ($object->equals($element)) {
                return true;
            }
        }
        return false;
    }

    public static function merge($a, $b)
    {
        if (empty($a) && empty($b)) {
            return [];
        }
        if (empty($a))
        {
            return $b;
        }
        if (empty($b))
        {
            return $a;
        }
        return parent::merge($a, $b);
    }
}
?>