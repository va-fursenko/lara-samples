<?php

namespace App\Http\Controllers\Etours;

use App\Http\Controllers\MainViewController;
use App\Http\Requests\AutocompleteRequest;
use App\Http\Requests\Etours\{CreateSearchUrlRequest,
    TourActualizeRequest,
    TourBookingRequest,
    TourCanBookingPenaltyRequest,
    TourCreateOrderRequest,
    TourDetailsRequest,
    TourGetAviaTariffInfoRequest,
    TourGetAviaBrandedRequest,
    TourGetCancelTripPriceRequest,
    TourGetInsurancesRequest,
    TourGetTransfersRequest,
    TourGetPenaltiesRequest,
    TourGetAvailableTemplatesRequest,
    TourGetVisasRequest,
    TourSearchRequest};
use App\Hunter\Engines\Tours\Communication\{Forms\TourSearchForm,
    TourActualizeRQ,
    TourBookingRQ,
    TourCanBookingPenaltyRQ,
    TourCreateOrderRQ,
    TourGetAviaTariffInfoRQ,
    TourGetAviaBrandedRQ,
    TourGetCancelTripPriceRQ,
    TourGetDetailsRQ,
    TourGetInsurancesRQ,
    TourGetPenaltiesRQ,
    TourGetTransfersRQ,
    TourGetVisasRQ,
    TourSearchRQ,
    TourSearchRS};
use App\Hunter\Engines\Tours\Formatters\{FrontToursActualizeFormatter,
    FrontToursAviaTariffInfoFormatter,
    FrontToursAviaTariffsFormatter,
    FrontToursBookingFormatter,
    FrontToursCancelTripPriceFormatter,
    FrontToursDetailsFormatter,
    FrontToursFormatter,
    FrontToursInsurancesFormatter,
    FrontToursPenaltiesFormatter,
    FrontToursSearchFormatter,
    FrontToursTransfersFormatter,
    FrontToursVisasFormatter};
use App\Services\Etours\TourTemplatesService;
use App\Traits\LogError;
use AutocompleteHelper;
use City;
use ExtraToursAPIService;
use Hotel;
use HunterEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use ObjectType;
use Onex\DBPackage\Repositories\Interfaces\IAgencyBalanceRepo;
use Onex\DBPackage\Repositories\Interfaces\IUsersRepo;
use Throwable;

/**
 * Class FrontTourController
 * @package App\Http\Controllers\Etours
 *
 * Контроллер работы с фронтом
 *
 * @copyright Online Express, Ltd. (www.online-express.ru)
 * @project oex
 * @version 1.0
 * @link
 */
class FrontTourController extends MainViewController
{
    use AutocompleteHelper, ObjectType, LogError;

    /**
     * @var ExtraToursAPIService
     */
    private $apiService;

    /**
     * @var TourTemplatesService
     */
    private $tourTemplatesService;

    /**
     * TourSearchController constructor.
     * @param IUsersRepo $usersRepo
     * @param IAgencyBalanceRepo $agencyBalanceRepo
     * @param ExtraToursAPIService $apiService
     * @param TourTemplatesService $tourTemplatesService
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(IUsersRepo $usersRepo,
                                IAgencyBalanceRepo $agencyBalanceRepo,
                                ExtraToursAPIService $apiService,
                                TourTemplatesService $tourTemplatesService)
    {
        parent::__construct($usersRepo, $agencyBalanceRepo);
        $this->initWebAutocomplete();
        $this->apiService = $apiService;
        $this->tourTemplatesService = $tourTemplatesService;
    }

    /**
     * Поиск по городу (первичный поиск)
     *
     * @param TourSearchRequest
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getResults(TourSearchRequest $request)
    {
        try {
            /** @var TourSearchRQ $onexRequest */
            $onexRequest = $request->fillRequest(new TourSearchRQ());
            $response = $this->apiService->search($onexRequest);
        } catch (Throwable $e) {
            static::logError($e);
            return $this->outputError(410, 'Невозможно выполнить поиск', $e);
        }
        // Форматируем в удобный для фронта вид и отдаём из контроллера
        return response()->json(
            (new FrontToursSearchFormatter())->format($response),
            $response->getStatusCode()
        );
    }

    /**
     * Перепоиск трансферов
     *
     * @param TourGetTransfersRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransfers(TourGetTransfersRequest $request)
    {
        try {
            /** @var TourGetTransfersRQ $onexRequest */
            $onexRequest = $request->fillRequest(new TourGetTransfersRQ());
            $response = $this->apiService->getTransfers($onexRequest);
        } catch (Throwable $e) {
            return $this->outputError(406, 'Невозможно найти трансферы', $e);
        }
        return response()->json(
            (new FrontToursTransfersFormatter())->format($response),
            $response->getStatusCode()
        );
    }

    /**
     * Штрафы корзины
     *
     * @param TourGetPenaltiesRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getPenalties(TourGetPenaltiesRequest $request)
    {
        try {
            /** @var TourGetPenaltiesRQ $onexRequest */
            $onexRequest = $request->fillRequest(new TourGetPenaltiesRQ());
            $response = $this->apiService->getPenalties($onexRequest);
        } catch (Throwable $e) {
            return $this->outputError(406, 'Невозможно получить штрафы', $e);
        }
        return response()->json(
            (new FrontToursPenaltiesFormatter())->format($response),
            $response->getStatusCode()
        );
    }

    /**
     * Получение информации по тарифам авиа
     *
     * @param TourGetAviaTariffInfoRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAviaTariffInfo(TourGetAviaTariffInfoRequest $request)
    {
        try {
            /** @var TourGetAviaTariffInfoRQ $onexRequest */
            $onexRequest = $request->fillRequest(new TourGetAviaTariffInfoRQ());
            $response = $this->apiService->getAviaTariffInfo($onexRequest);
        } catch (Throwable $e) {
            return $this->outputError(406, 'Невозможно получить информацию по тарифам авиа', $e);
        }
        return response()->json(
            (new FrontToursAviaTariffInfoFormatter())->format($response),
            $response->getStatusCode()
        );
    }

    /**
     * Перепоиск виз
     *
     * @param TourGetVisasRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVisas(TourGetVisasRequest $request)
    {
        try {
            /** @var TourGetVisasRQ $onexRequest */
            $onexRequest = $request->fillRequest(new TourGetVisasRQ());
            $response = $this->apiService->getVisas($onexRequest);
        } catch (Throwable $e) {
            return $this->outputError(406, 'Невозможно найти визы', $e);
        }
        return response()->json(
            (new FrontToursVisasFormatter())->format($response),
            $response->getStatusCode()
        );
    }

    /**
     * Перепоиск страховок
     *
     * @param TourGetInsurancesRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInsurances(TourGetInsurancesRequest $request)
    {
        try {
            /** @var TourGetInsurancesRQ $onexRequest */
            $onexRequest = $request->fillRequest(new TourGetInsurancesRQ());
            $response = $this->apiService->getInsurances($onexRequest);
        } catch (Throwable $e) {
            return $this->outputError(406, 'Невозможно найти страховки', $e);
        }
        return response()->json(
            (new FrontToursInsurancesFormatter())->format($response),
            $response->getStatusCode()
        );
    }

    /**
     * Бронирование
     *
     * @param TourBookingRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function booking(TourBookingRequest $request)
    {
        try {
            /** @var TourBookingRQ $onexRequest */
            $onexRequest = $request->fillRequest(new TourBookingRQ());
            $response = $this->apiService->booking($onexRequest);
        } catch (Throwable $e) {
            return $this->outputError(406, 'Невозможно забронировать', $e);
        }
        return response()->json(
            (new FrontToursBookingFormatter())->formatBookingRS($response),
            $response->getStatusCode()
        );
    }
    
    // to be . . . . . . . . . . . . .
}
