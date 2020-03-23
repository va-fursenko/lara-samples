<?php

/**
 * Class HotelsSearchParamsFiltrator
 *
 * Фильтратор параметров и результатов поисковых задач для отелей
 *
 * @copyright Online Express, Ltd. (www.online-express.ru)
 * @author Viktor.Fursenko
 * @project oex
 * @date 21.10.2019
 * @time 16:57
 * @version 1.0
 * @link
 */
class HotelsSearchBuilderFiltrator extends HunterSearchFiltrator
{
    /**
     * HotelsSearchParamsFiltrator constructor.
     */
    public function __construct()
    {
        $this->setFirst(new OnRequestOnlyMMtSearchBuilderFilter());
        //->setNext(new YetAnotherSearchFilter())
        //->setNext(new NextOneSearchFilter());
    }
}