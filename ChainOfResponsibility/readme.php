<?php

/**
 * При поиске отелей в нашей системе не все ограничения и параметры поиска можно применить в поисковом запросе.
 * Так же, иногда появляется бизнес-запрос на фильтрацию из результатов некоторых отелей
 * или отдельных размещений в отдельных отелях. Мне показалось самым удачным использовать тут цепочку вызовов
 *
 * Опять же, от Лары тут только DI, но разве в бизнес-логике должно быть много фреймворка?
 *
 * Рядом в папке положил несколько использованных классов. К сожалению, наш движок поиска так и "не научился" DI
 * Так что именно в нём приходилось подключать всё нужное через app()->make()
 */

// Фильтирация параметров поисковых задач при поиске отелей
$filtrator = app()->make(IHotelsSearchBuilderFiltrator::class);
$builder = $filtrator->filter($builder);

// Фильтирация результатов при поиске отелей
$filtrator = app()->make(IHotelsSearchResultsFiltrator::class, [$request, $this->isLite()]);
$results = $filtrator->filter($results);


// Организуем сервис-провайдер для гибкого DI
class HotelsSearchResultsFiltratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(IHotelsSearchResultsFiltrator::class, function ($app, $params) {
            if (count($params) < 2) {
                throw new \Exception(IHotelsSearchResultsFiltrator::class . " app::make() needs 2 parameters: (HotelSearchRQ)'request', (bool)'isLite'");
            }
            if (empty($params[0]) || !$params[0] instanceof HotelSearchRQ) { 
                throw new \Exception(IHotelsSearchResultsFiltrator::class . " app::make() needs " . HotelSearchRQ::class . ' parameter');
            }
            return new HotelsSearchResultsFiltrator($params[0], (bool)$params[1]);
        });
    }
}
