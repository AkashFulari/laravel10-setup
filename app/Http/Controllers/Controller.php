<?php

namespace App\Http\Controllers;

use App\Exceptions\ActivationException;
use App\Exceptions\InvalidAccessException;
use App\Exceptions\NoActiveSubscription;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function sendSuccess($message, $data = [], $statusCode = 200, $headers = [])
    {
        return $this->sendResponse($message, true, $data, $statusCode, $headers);
    }

    protected function sendError($message, $data = [], $statusCode = 400, $headers = [])
    {
        return $this->sendResponse($message, false, $data, $statusCode, $headers);
    }

    protected function sendErrorException(Exception $ex, $data = [], $statusCode = 400, $headers = [])
    {
        $message = $ex->getMessage();
        if ($ex instanceof InvalidAccessException) {
            $data = array_merge(['is_inactive' => false, $data]);
            $statusCode = 401;
        } else if ($ex instanceof NoActiveSubscription) {
            $statusCode = 409;
            $data['subscription_needs'] = $ex->subscriptionCode;
        }
        $data = array_filter($data);
        return $this->sendResponse($message, false, $data, $statusCode, $headers);
    }

    protected function sendResponse($message, $status = false, $data = null, $statusCode = 200, $headers = [])
    {
        $resp = array("status" => $status, "message" => $message);
        if ($data != null) {
            if (is_array($data))
                $resp = array_merge($resp, $data);
            else if ($data instanceof \Illuminate\Support\Collection)
                $resp['list'] = $data;
            else if (is_object($data))
                $resp['info'] = $data;
            else if (is_string($data))
                $resp["content"] = $data;
        }
        return response()->json($resp, $statusCode, $headers);
    }
}
