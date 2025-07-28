<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Api\Customer\Modules\CompanyLogin\Models\Note;

class MyNotesService
{
    /**
     * Store My notes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function storeNote(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'notes' => 'required',
                'title' => 'required',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $insert = Note::on($dbName)->insert([
                'note' => $request->notes,
                'title' => $request->title,
                'cloud_sso_user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if (!$insert) {
                return [
                    'status'     => false,
                    'message'    => 'Failed to add note',
                    'errors'     => ['error' => 'Internal Server Error'],
                    'statusCode' => 500,
                ];
            }
            return [
                'status'     => true,
                'message'    => 'Notes Added Successfully',
                'data'       => null,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
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
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $note = Note::on($dbName)->where([['cloud_sso_user_id', $userId], ['id', $id]])->first();
            return [
                'status'     => true,
                'message'    => 'Notes',
                'data'       => $note,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'An error occurred',
                'errors' => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * get All notes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getNotes(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $note = Note::on($dbName)->where([['cloud_sso_user_id', $userId]])->get();
            return [
                'status'     => true,
                'message'    => 'Notes',
                'data'       => $note,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'An error occurred',
                'errors' => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
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
        try {
            $validator = Validator::make($request->all(), [
                'notes' => 'required',
                'id' => 'required',
                'title' => 'required',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $update = Note::on($dbName)->where([['id', $request->id]])->update([
                'note' => $request->notes,
                'title' => $request->title,
                'cloud_sso_user_id' => $userId,
                'favorite' => $request->favorite ?? 0,
                'updated_at' => now(),
            ]);
            if (!$update) {
                return [
                    'status'     => false,
                    'message'    => 'Update Failed',
                    'errors'     => ['error' => 'Invalid ID: Notes not found'],
                    'statusCode' => 404,
                ];
            }
            return [
                'status'     => true,
                'message'    => 'Notes updated Successfully',
                'data'       => null,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Delete notes.
     *
     * @param   notes  $id 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteNotes($id, Request $request)
    {

        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');

            $deleteNote =  Note::on($dbName)
                ->where([['cloud_sso_user_id', $userId], ['id', $id]])
                ->delete();
            if (!$deleteNote) {
                return [
                    'status'     => false,
                    'message'    => 'Delete Notes Failed',
                    'errors'     => 'Invalid ID: Notes not found',
                    'statusCode' => 404,
                ];
            }
            return [
                'status'     => true,
                'message'    => 'Notes deleted successfully!',
                'data'       => null,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
}
