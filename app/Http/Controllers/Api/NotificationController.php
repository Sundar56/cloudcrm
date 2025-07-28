<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Exception;
use App\Traits\sendNotification;

class NotificationController extends BaseController
{
    use sendNotification;
    /**
     * Company Notify Message Endpoint
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyNotificationMessage(Request $request)
    {
        try {
            $channel  = 'migrationchannel';
            $response = $this->sendNotification($channel, $request->state);
            return $this->sendResponse($response, 'Company Notification sent successfully!');
        } catch (Exception $e) {
            return $this->sendError(
                'An error occurred while sending the notification.',
                ['error' => $e->getMessage(), 'line' => $e->getLine()],
                500
            );
        }
    }
    /**
     * Privileges Notify Message Endpoint
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function privilegeNotificationMessage(Request $request)
    {
        try {
            $channel  = 'privilegeschannel';
            $response = $this->sendNotification($channel, $request->state);
            return $this->sendResponse($response, 'Privileges Notification sent successfully!');
        } catch (Exception $e) {
            return $this->sendError(
                'An error occurred while sending the notification.',
                ['error' => $e->getMessage(), 'line' => $e->getLine()],
                500
            );
        }
    }
    /**
     * Admin Dashboard count Message Endpoint
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminDashboardCountMessage(Request $request)
    {
        try {
            $channel  = 'admindashboardchannel';
            $response = $this->sendNotification($channel, $request->state);
            return $this->sendResponse($response, 'Admin Dashboard count sent successfully!');
        } catch (Exception $e) {
            return $this->sendError(
                'An error occurred while sending the notification.',
                ['error' => $e->getMessage(), 'line' => $e->getLine()],
                500
            );
        }
    }
    /**
     * Global Dashboard count Message Endpoint
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function globalDashboardCountMessage(Request $request)
    {
        try {
            $channel  = 'globaldashboardchannel';
            $response = $this->sendNotification($channel, $request->state);
            return $this->sendResponse($response, 'Global Dashboard count sent successfully!');
        } catch (Exception $e) {
            return $this->sendError(
                'An error occurred while sending the notification.',
                ['error' => $e->getMessage(), 'line' => $e->getLine()],
                500
            );
        }
    }
    /**
     * Global settings mail config notify message
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mailConfigNotification(Request $request)
    {
        try {
            $channel  = 'mailconfigchannel';
            $response = $this->sendNotification($channel, $request->state);
            return $this->sendResponse($response, 'Mail config notification sent successfully!');
        } catch (Exception $e) {
            return $this->sendError(
                'An error occurred while sending the notification.',
                ['error' => $e->getMessage(), 'line' => $e->getLine()],
                500
            );
        }
    }
    /**
     * Privileges Notify Message Endpoint
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatedRoleNotify(Request $request)
    {
        try {
            $channel  = 'updatedrolechannel';
            $response = $this->sendNotification($channel, $request->state);
            return $this->sendResponse($response, 'Roles Notification sent successfully!');
        } catch (Exception $e) {
            return $this->sendError(
                'An error occurred while sending the notification.',
                ['error' => $e->getMessage(), 'line' => $e->getLine()],
                500
            );
        }
    }
    /**
     * Privileges Notify Message Endpoint
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminuserUpdatedRoleNotify(Request $request)
    {
        try {
            $channel  = 'adminuserupdatedrolechannel';
            $response = $this->sendNotification($channel, $request->state);
            return $this->sendResponse($response, 'Roles Notification sent successfully!');
        } catch (Exception $e) {
            return $this->sendError(
                'An error occurred while sending the notification.',
                ['error' => $e->getMessage(), 'line' => $e->getLine()],
                500
            );
        }
    }
}
