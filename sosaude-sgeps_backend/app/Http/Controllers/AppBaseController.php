<?php

namespace App\Http\Controllers;

use InfyOm\Generator\Utils\ResponseUtil;
use Response;
use Helper;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 *   @SWG\Info(
 *     title="Laravel Generator APIs",
 *     version="1.0.0",
 *   )
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    /* public function sendResponse($result, $message)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result));
    } */
    public function sendResponse($result, $message, $code = 200)
    {
        return Response::json(Helper::makeResponse($message, $result), $code);
    }

    /* public function sendError($error, $code = 404)
    {
        return Response::json(ResponseUtil::makeError($error), $code);
    } */
    public function sendError($error, $code = 404)
    {
        return Response::json(Helper::makeError($error), $code);
    }

    public function sendErrorValidation($error, $code = 422)
    {
        return Response::json(Helper::makeErrorValidation($error), $code);
    }

    /* public function sendSuccess($message)
    {
        return Response::json([
            'success' => true,
            'message' => $message
        ], 200);
    } */

    public function sendSuccess($message, $code = 200)
    {
        return Response::json(Helper::makeSuccess($message), $code);
    }
}
