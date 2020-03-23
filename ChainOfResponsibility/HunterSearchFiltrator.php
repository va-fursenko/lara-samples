<?php

/**
 * Class HunterSearchFiltrator
 *
 * Класс фильтрации поисковых параметров
 *
 * @copyright Online Express, Ltd. (www.online-express.ru)
 * @author Viktor.Fursenko
 * @project oex
 * @date 21.10.2019
 * @time 15:34
 * @version 1.0
 * @link
 */
abstract class HunterSearchFiltrator
{
    /**
     * Первый фильтр с рекурсивной ссылкой на второй и последующие
     *
     * @var ISearchFilter
     */
    private $firstFilter;

    /**
     * HunterSearchFiltrator constructor.
     */
  /*public function __construct()
    {
        $this->setFirst(new SomeSearchFilter())
            ->setNext(new YetAnotherSearchFilter())
            ->setNext(new NextOneSearchFilter());
    }*/

    /**
     * Создание первого фильтра и возвращение его объекта
     *
     * @param ISearchFilter $filter
     * @return ISearchFilter
     */
    final protected function setFirst(ISearchFilter $filter) : ISearchFilter
    {
        return $this->firstFilter = $filter;
    }

    /**
     * Применение к входным данным указанных в конструкторе фильтров
     *
     * @param mixed $object
     * @return mixed|null
     */
    final public function filter($object)
    {
        return $this->firstFilter->handle($object);
    }
}