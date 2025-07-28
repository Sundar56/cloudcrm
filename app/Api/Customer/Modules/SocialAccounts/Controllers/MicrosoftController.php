<?php

namespace App\Api\Customer\Modules\SocialAccounts\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CompanyDatabaseService;
use App\Http\Controllers\Api\BaseController;
use App\Services\MicrosoftService;
use Illuminate\Support\Facades\Redis;

class MicrosoftController extends BaseController
{
    protected $companyDatabaseService;
    protected $microsoftService;
    public function __construct(CompanyDatabaseService $companyDatabaseService, MicrosoftService $microsoftService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
        $this->microsoftService = $microsoftService;
    }

    /**
     * Handle the user Microsoft login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth(Request $request)
    {
        $response = $this->microsoftService->auth($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Get microsoft all calendar events.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalendarevents(Request $request)
    {

        $response = $this->microsoftService->getCalendarevents($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Handle the store Microsoft calendar Event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCalendarEvent(Request $request)
    {

        $response = $this->microsoftService->storeCalendarEvent($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Delete Microsoft calendar Event.
     *
     * @param   Microsoft calendar Event $id 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCalendarEvent($id, Request $request)
    {
        $response = $this->microsoftService->deleteCalendarEvent($id, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * update Microsoft calendar Event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCalendarEvent($id, Request $request)
    {
        $response = $this->microsoftService->updateCalendarEvent($id, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);

    }

     /**
     * Get Microsoft calendar Event.
     *
     * @param    $id calendar event id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalendarEvent($id, Request $request)
    {
        $response = $this->microsoftService->getCalendarEvent($id, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    public function microsoftconnect(Request $request){
        $response = $this->microsoftService->microsoftconnect($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    public function getIntegrate(Request $request)  {
        $response = $this->microsoftService->getIntegrate($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    public function storeIntegrate (Request $request){
        $response = $this->microsoftService->storeIntegrate($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    public function integrateMenu(Request $request){
        $response = $this->microsoftService->integrateMenu($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

     /**
     * check Microsoft connet.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkconnect(Request $request) {
        $response = $this->microsoftService->checkconnect($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    public function eventResponse(Request $request){
        $response = $this->microsoftService->eventResponse($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    public function syncEvent(Request $request){
        $response = $this->microsoftService->syncMicrosoftEvents($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    public function synctoggle(Request $request) {
        $response = $this->microsoftService->synctoggle($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    public function checksynctoggle(Request $request){
        $response = $this->microsoftService->checksynctoggle($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Users Email list.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function usersMailList(Request $request) 
    {
        
        $response = $this->microsoftService->usersMailList($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Get Events by date.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventsByDate(Request $request) 
    {
        
        $response = $this->microsoftService->getEventsByDate($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
}
