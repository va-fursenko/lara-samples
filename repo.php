<?php

namespace Onex\DBPackage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Onex\DBPackage\Models\AnnotatedModel;

/**
 * Этап рефакторинга папки с разрознёнными репозиториями, нарушающими DRY и принципы наследования и ещё много какие принципы
 *
 * На входе в задаче была пачка 50-80 разрозненных репозиториев, имеющих чаще всего одинаковые методы получения одной записи или 
 * списка записей. Первым делом, был заведён интерфейс абстрактного репозитория, в трейт вынесен общий функционал, 
 * независимый от целевой сущности репы.
 *
 * В первую очередь менялись ключевые, самые часто используемые репозитории проекта. Подключался трейт, добавлятся PHPDock 
 * с конкретной типизацией методов. Класс наследовался от общего абстрактного предка BaseRepository, тоже реализующего этот интерфейс.
 * 
 * Если все репозитории успешно переведены на использование этого функционала, выносим его в BaseRepository и удаляем трейт.
 *
 * Если некоторый функционал использует лишь часть репозиториев, но больше одного, то действуем аналогично, но по окончании 
 * унификации переносим функционал не в BaseRepository, а в его абстрактного потомка и наследуем репы от него. 
 * 
 * Trait RepositoryGetById
 * @package Onex\DBPackage
 *
 * Общие для всех репозиториев методы
 *
 * В использующих данный трейт классах типизировать методы для IDE можно с помощью PHPDock
 *
 * @copyright Online Express, Ltd. (www.online-express.ru)
 * @author Viktor.Fursenko
 * @project oex
 * @date 07.11.2019
 * @time 16:13
 * @version 1.0
 * @link
 */
trait RepositoryCommon // Функционал описан в интерфейсе IRepo, но не может быть тут задействован
{
    /**
     * Класс базовой модели
     *
     * @return string
     */
    abstract public function getBaseModelClass() : string;

    /**
     * Возвращает модель по id
     *
     * @param int  $id      ID модели
     * @param bool $orFail  Флаг падения с исключением в случае не нахождения модели
     * @param bool $cache   Флаг кеширования
     * @return AnnotatedModel|null
     * @throws ModelNotFoundException
     */
    public function getById(int $id, $orFail = true, $cache = true) : ?AnnotatedModel
    {
        /** @var AnnotatedModel $className */
        $className = $this->getBaseModelClass();
        $result = function () use ($className, $id, $orFail) {
            return $orFail
                ? $className::findOrFail($id)
                : $className::find($id);
        };
        if (!$cache) {
            return $result();
        }
        $cacheTag = __FUNCTION__ . "_{$className}_" . $id;
        return Cache::remember($cacheTag, 3600, $result);
    }

    /**
     * Возвращает список моделей по списку id, проиндексированный по этому полю
     *
     * @param array|int[]  $ids     Массив ID
     * @param string|array $columns Выбираемые поля
     * @param string|array $with    Жадно загружаемые связи
     * @param bool         $cache   Флаг кеширования (1 ч). Обычно, список id редко повторяется и кешировать нет смысла
     * @return AnnotatedModel[]
     */
    public function getByIds(array $ids, $columns = '*', $with = '', $cache = false) : array
    {
        /** @var AnnotatedModel $className */
        $className = $this->getBaseModelClass();
        $result = function () use ($ids, $columns, $with, $className) {
            $result = $className::whereIn('id', $ids);
            if ($with) {
                $result->with($with);
            }
            return $result->get($columns)
                ->keyBy('id')
                ->all();
        };
        if (!$cache) {
            return $result();
        }
        $cacheTag = __FUNCTION__ . "_{$className}_" . implode(',', $ids) . '_' . (is_array($columns) ? implode(',', $columns) : $columns);
        return Cache::remember($cacheTag, 3600, $result);
    }

    /**
     * Возвращает список всех моделей, проиндексированный по id
     *
     * @param string|array $columns Выбираемые поля
     * @param bool         $cache   Флаг кеширования (1 ч). Обычно, список id редко повторяется и кешировать нет смысла
     * @param string|array $orderBy Столбец или массив столбцов для сортировки (пока просто без ASC или DESC)
     * @return AnnotatedModel[]
     */
    public function getAll($columns = '*', $cache = false, $orderBy = null) : array
    {
        /** @var AnnotatedModel $className */
        $className = $this->getBaseModelClass();
        $result = function () use ($columns, $className, $orderBy) {
            /** @var Builder $result */
            $result = $className::query();
            if ($orderBy) {
                $result->orderBy(...(is_array($orderBy) ? $orderBy : [$orderBy]));
            }
            return $result->get($columns)
                ->keyBy('id')
                ->all();
        };
        if (!$cache) {
            return $result();
        }
        $cacheTag = __FUNCTION__ . "_{$className}_" . (is_array($columns) ? implode(',', $columns) : $columns);
        return Cache::remember($cacheTag, 3600, $result);
    }
}
