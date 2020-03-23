<?php

/**
 * Class ByNameHotelResultsFilter
 *
 * Фильтрация отелей в результатах по имени отеля (если оно задано в запросе)
 *
 * @copyright Online Express, Ltd. (www.online-express.ru)
 * @author Viktor.Fursenko
 * @project oex
 * @date 22.10.2019
 * @time 15:08
 * @version 1.0
 * @link
 */
class NameHotelResultsFilter extends BaseHotelResultsFilter
{
    /**
     * @var string
     */
    private $searchHotelTerm;

    /**
     * Остаются только отели, у которых имя содержит целиком фразу, переданную в строке запроса
     *
     * @param HotelResult[] $results
     * @return HotelResult[]
     */
    public function filter($results)
    {
        // Если в поисковом запросе нет имени отеля, то и фильтровать нечего
        if (!$results || !$this->searchRQ->hotelName) {
            return $results;
        }
        // Сохраняем перед циклом поисковую фразу, которую мы расчитываем видеть в имени отелей
        $this->searchHotelTerm = mb_strtolower(trim($this->searchRQ->hotelName));
        return $this->filterHotelResults($results);
    }

    /**
     * Остаются только отели, у которых имя содержит целиком фразу, переданную в строке запроса
     *
     * @param HotelResult $hotelResult
     * @return bool
     */
    protected function saveHotelResult(HotelResult $hotelResult) : bool
    {
        return mb_stripos(mb_strtolower($hotelResult->name), $this->searchHotelTerm) !== false;
    }
}