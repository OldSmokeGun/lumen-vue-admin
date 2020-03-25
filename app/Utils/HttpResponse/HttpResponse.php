<?php

namespace App\Utils\HttpResponse;

use Laravel\Lumen\Http\ResponseFactory;

class HttpResponse
{
    /**
     * @var ResponseFactory
     */
    private $response;

    /**
     * HttpResponse constructor.
     *
     * @param ResponseFactory $response
     */
    public function __construct(ResponseFactory $response)
    {
        $this->response = $response;
    }

    /**
     * 成功响应
     *
     * @param array $data
     * @param string $message
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse(
        $data = [],
        string $message = HttpResponseCode::SUCCESS_CODE_MESSAGE,
        string $code = HttpResponseCode::SUCCESS_CODE
    )
    {
        return $this->response($code, $message, $data);
    }

    /**
     * 失败响应
     *
     * @param string $message
     * @param string $code
     * @param array $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function failedResponse(
        string $message = HttpResponseCode::FAILED_CODE_MESSAGE,
        string $code = HttpResponseCode::FAILED_CODE,
        $data = []
    )
    {
        return $this->response($code, $message, $data);
    }

    /**
     * http 响应
     *
     * @param string $code
     * @param string $message
     * @param array $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function response(string $code, string $message, $data = [] )
    {
        $response = [
            'code'    => $code,
            'message' => $message,
            'data'    => $data
        ];

        return $this->response->json($response);
    }
}
