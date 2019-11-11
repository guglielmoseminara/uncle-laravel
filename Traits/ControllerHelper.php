<?php

namespace UncleProject\UncleLaravel\Traits;

use Dingo\Api\Routing\Helpers;
use Response;

trait ControllerHelper {

    use Helpers;

    private function buildResponse($data, $meta) {
        $responseData = [];
        if (!method_exists($data, 'toArray') && !isset($data['data']) || (method_exists($data, 'toArray') && !isset($data->toArray()['data']))) {
            $responseData['data'] = $data;
            $responseData['meta'] = $meta;
        } else {
            $responseData = $data;
        }
        return $responseData;
    }

    /**
     * Return a valid 200 Success json response.
     *
     * @param string $message  The response message.
     * @param array  $data     The passed data.
     * @param string $redirect The redirection url.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function validSuccessJsonResponse($message = 'Success', $data = [], $redirect = null, $meta = [])
    {
        $responseData = $this->buildResponse($data, $meta);
        return $this->response()->array(
            [
                'code' => '200',
                'message' => $message,
                'results' => $responseData,
                'errors' => [],
                'redirect' => $redirect,
            ]
        );
    }

    public function validSuccessImageResponse($file, $type) {
        $response = Response::make($file);
        $response->header('Content-Type', $type);
        return $response;
    }




}
