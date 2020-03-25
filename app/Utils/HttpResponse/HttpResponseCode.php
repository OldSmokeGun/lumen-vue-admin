<?php


namespace App\Utils\HttpResponse;


class HttpResponseCode
{
    const SUCCESS_CODE         = "OK";                  // 成功
    const FAILED_CODE          = "ERROR";               // 服务器出错
    const LOGIN_INVALID_CODE   = "LOGIN_VALID";         // 登陆失效
    const PERMISSION_DENY_CODE = "PERMISSION_DENY";     // 权限拒绝
    const ILLEGAL_REQUEST_CODE = "ILLEGAL_REQUEST";     // 非法请求

    const SUCCESS_CODE_MESSAGE         = "";
    const FAILED_CODE_MESSAGE          = "服务器出错";
    const LOGIN_INVALID_CODE_MESSAGE   = "登陆失效";
    const PERMISSION_DENY_CODE_MESSAGE = "权限拒绝";
    const ILLEGAL_REQUEST_CODE_MESSAGE = "非法请求";

}
