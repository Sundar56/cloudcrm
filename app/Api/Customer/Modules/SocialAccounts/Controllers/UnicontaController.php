<?php

namespace App\Api\Customer\Modules\SocialAccounts\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Api\BaseController;
use App\Services\UnicontaService;
use Illuminate\Support\Facades\Redis;

class UnicontaController extends BaseController
{
    protected $unicontaService;
    public function __construct(UnicontaService $unicontaService)
    {
        $this->unicontaService = $unicontaService;
    }

    /**
     * Handle the uniconta login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth(Request $request)
    {

        $response = $this->unicontaService->auth($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Get uniconta login details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuth(Request $request) {
        $response = $this->unicontaService->getAuth($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Get uniconta all customers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomers(Request $request)
    {
        $response = $this->unicontaService->getCustomers($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Get uniconta customer.
     *
     * @param    customer $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomer($account, Request $request)
    {

        $response = $this->unicontaService->getCustomer($account, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * update uniconta customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCustomer(Request $request)
    {
        $response = $this->unicontaService->updateCustomer($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Handle the store uniconta customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCustomer(Request $request)
    {
        $response = $this->unicontaService->storeCustomer($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Delete uniconta customer.
     *
     * @param   uniconta customer $id and account
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCustomer(Request $request)
    {
        $response = $this->unicontaService->deleteCustomer($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }


    public function staticData(Request $request)
    {
        $response = $this->unicontaService->staticData($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * check uniconta connet.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkconnect(Request $request)
    {
        $response = $this->unicontaService->checkconnect($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * get uniconta select option.
     *
     *  @param   select menu name
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectOption(Request $request)
    {
        $response = $this->unicontaService->selectOption($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Handle the store uniconta customer contact.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeContact(Request $request)
    {
        $response = $this->unicontaService->storeContact($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * update customer Contact.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateContact(Request $request)
    {
        $response = $this->unicontaService->updateContact($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Get uniconta contact.
     *
     * @param    customer $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContact($id, Request $request)
    {
        $response = $this->unicontaService->getContact($id, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Get uniconta contact.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContacts($account, Request $request)
    {
        $response = $this->unicontaService->getContacts($account, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Delete uniconta customer contact.
     *
     * @param   uniconta contact $id and account
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteContact(Request $request)
    {
        $response = $this->unicontaService->deleteContact($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }


    /**
     * Get uniconta address.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAddress($account, Request $request)
    {
        $response = $this->unicontaService->getAddress($account, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Get uniconta delivery address for single.
     *
     * @param    customer $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeliveryAddress($id, Request $request)
    {
        $response = $this->unicontaService->getDeliveryAddress($id, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Handle the store uniconta customer delivery address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAddress(Request $request)
    {
        $response = $this->unicontaService->storeAddress($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * update Delivery address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAddress(Request $request)
    {
        $response = $this->unicontaService->updateAddress($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Delete uniconta customer delivery address.
     *
     * @param   uniconta contact $id and account
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAddress(Request $request)
    {
        $response = $this->unicontaService->deleteAddress($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
}
