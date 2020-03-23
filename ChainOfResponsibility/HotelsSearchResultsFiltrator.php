<?php

/**
 * Class HotelsSearchResultsFiltrator
 *
 * Фильтратор результатов поиска отелей
 *
 * @copyright Online Express, Ltd. (www.online-express.ru)
 * @author Viktor.Fursenko
 * @project oex
 * @date 21.10.2019
 * @time 16:57
 * @version 1.0
 * @link
 */
class HotelsSearchResultsFiltrator extends HunterSearchFiltrator
{
    /**
     * HotelsSearchResultsFiltrator constructor
     *
     * @param HotelSearchRQ $request
     * @param bool $isLite
     */
    public function __construct(HotelSearchRQ $request, bool $isLite)
    {
        $this->setFirst(new RegionHotelResultsFilter($request, $isLite))          // Оставляет отели только того региона, по которому был поиск
            ->setNext(new GeoPoligonHotelResultsFilter($request, $isLite))        // Фильтрация результатов по геополигонам и точкам
            ->setNext(new OnlyAvailableHotelResultsFilter($request, $isLite))     // Обработка поля $request->onlyAvailable
            ->setNext(new OnRequestOnlyMMtHotelResultsFilter($request, $isLite))  // Размещения под запрос оставляем только у ММт
            ->setNext(new OnlySameRoomTypesHotelResultsFilter($request, $isLite)) // При поиске нескольких номеров оставляем только размещения с одинаковым номерами
            ->setNext(new OnlySameMealsHotelResultsFilter($request, $isLite))     // При поиске нескольких номеров оставляем только размещения с одинаковым питанием во всех номерах
            ->setNext(new NameHotelResultsFilter($request, $isLite))              // Остаются только отели, у которых имя содержит целиком фразу, переданную в строке запроса
        ;
    }
}