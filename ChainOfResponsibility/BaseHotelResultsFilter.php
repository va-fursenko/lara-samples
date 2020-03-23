<?php

/**
 * Class BaseSearchResultsFilter
 *
 * Фильтр результатов поиска отелей
 *
 * @copyright Online Express, Ltd. (www.online-express.ru)
 * @author Viktor.Fursenko
 * @project oex
 * @date 22.10.2019
 * @time 14:16
 * @version 1.0
 * @link
 *
 * @method HotelResult[] handle(HotelResult[] $hotelResults)
 * @method HotelResult[] filter(HotelResult[] $hotelResults)
 */
abstract class BaseHotelResultsFilter extends BaseSearchFilter
{
    /**
     * @var HotelSearchRQ
     */
    protected $searchRQ;

    /**
     * @var bool
     */
    protected $isLite;

    /**
     * BaseHotelResultsFilter constructor
     *
     * @param HotelSearchRQ $hotelSearchRQ
     * @param bool          $isLite
     */
    public function __construct(HotelSearchRQ $hotelSearchRQ, bool $isLite)
    {
        $this->searchRQ = $hotelSearchRQ;
        $this->isLite   = $isLite;
    }

    /**
     * Фильтрация по всем результатам размещений с помощью коллбэка
     * Результаты поиска по отелю, оставшиеся без размещений пропускаются
     *
     * @param HotelResult[] $results
     * @return HotelResult[]
     */
    protected function filterHotelAccomodations(array $results) : array
    {
        if ($results && !(reset($results) instanceof HotelResult)) {
            return $results;
        }
        $filteredResults = [];
        foreach ($results as $key => $hotelResult) {
            $resultAccomodations = [];
            foreach ($hotelResult->results as $accomodationKey => $accomodation) {
                if (!$this->saveAccomodation($accomodation)) {
                    continue;
                }
                $resultAccomodations[$accomodationKey] = $accomodation;
            }
            if ($resultAccomodations) {
                $hotelResult->results = $resultAccomodations;
                $filteredResults[$key] = $hotelResult;
            }
        }
        return $filteredResults;
    }

    /**
     * Флаг сохранения размещения в результате
     *
     * @param HotelAccomodation $accomodation
     * @return bool - true если нужно сохранить размещение и false - если его нужно исключить из результатов
     */
    protected function saveAccomodation(HotelAccomodation $accomodation) : bool
    {
        return true;
    }

    /**
     * Остаются только отели, у которых имя содержит целиком фразу, переданную в строке запроса
     *
     * @param HotelResult[] $hotelResults Неотфильтрованные результаты поиска
     * @return HotelResult[] Отфильтрованные результаты поиска
     */
    public function filterHotelResults(array $hotelResults) : array
    {
        $filteredResults = []; // Не хочу использовать array_filter() из-за передачи имени метода в строке
        foreach ($hotelResults as $key => $hotelResult) {
            if ($this->saveHotelResult($hotelResult)) {
                $filteredResults[$key] = $hotelResult;
            }
        }
        return $filteredResults;
    }

    /**
     * Флаг сохранения результата по отелю
     *
     * @param HotelResult $hotelResult
     * @return bool - true если нужно сохранить результат и false - если его нужно исключить
     */
    protected function saveHotelResult(HotelResult $hotelResult) : bool
    {
        return true;
    }
}