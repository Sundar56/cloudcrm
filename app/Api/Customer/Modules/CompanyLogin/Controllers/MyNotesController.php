<?php

namespace App\Api\Customer\Modules\CompanyLogin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Services\MyNotesService;

class MyNotesController extends BaseController
{
    protected $myNotesService;
    public function __construct(MyNotesService $myNotesService)
    {
        $this->myNotesService = $myNotesService;
    }


    /**
     * Store My notes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function storeNote(Request $request)
    {

        $response = $this->myNotesService->storeNote($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * get My note.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getNote($id, Request $request)
    {

        $response = $this->myNotesService->getNote($id, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * get all notes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getNotes(Request $request)
    {

        $response = $this->myNotesService->getNotes($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

     /**
     * update My notes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function updateNote(Request $request)
    {

        $response = $this->myNotesService->updateNote($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }


     /**
     * Delete notes.
     *
     * @param   notes  $id 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteNotes($id, Request $request)
    {
        $response = $this->myNotesService->deleteNotes($id, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);

    }


}
