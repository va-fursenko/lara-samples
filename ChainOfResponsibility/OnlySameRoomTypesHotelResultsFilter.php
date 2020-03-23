<?php

/**
 * Class SameRoomTypesHotelResultsFilter
 *
 * При поиске более одного номера фильтрация из результатов размещений с номерами разного типа
 *
 * @copyright Online Express, Ltd. (www.online-express.ru)
 * @author Viktor.Fursenko
 * @project oex
 * @date 22.10.2019
 * @time 15:08
 * @version 1.0
 * @link
 */
class OnlySameRoomTypesHotelResultsFilter extends BaseHotelResultsFilter
{
    /**
     * При поиске больше одного номера пропускаем размещения с разными типами номеров
     *
     * @param HotelResult[] $results
     * @return HotelResult[]
     */
    public function filter($results)
    {
        // Применяется только при полном поиске
        // При поиске 1 номера фильтровать нечего
        // Так же этот фильтр отключается настройками Хантера
        if (!$results || $this->isLite || count($this->searchRQ->rooms) == 1 || !Hunter::isOnlySameAccomodation()) {
            return $results;
        }
        return $this->filterHotelAccomodations($results);
    }

    /**
     * Флаг сохранения размещения в результате - Все номера размещения имеют одинаковый тип
     *
     * @param HotelAccomodation $accomodation
     * @return bool
     */
    protected function saveAccomodation(HotelAccomodation $accomodation) : bool
    {
        $roomType = $roomName = null;
        foreach ($accomodation->rooms as $roomKey => $room) {
            if ($roomType && $roomType !== $room->supplierRoomTypeCode) {
                return false;
            }
            $roomType = $room->supplierRoomTypeCode;
            if ($roomName && $roomName !== $room->name) {
                return false;
            }
            $roomName = $room->name;
        }
        return true;
    }
}