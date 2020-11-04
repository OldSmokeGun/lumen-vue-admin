<?php

namespace App\Http\Controllers;

use App\Facades\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * 管理员头像上传
     *
     * @param Request $request
     * @return mixed
     */
    public function file(Request $request)
    {
        if (!$request->hasFile('file')) {
            return HttpResponse::failedResponse('上传出错');
        }

        if (!$request->file('file')->isValid()) {
            return HttpResponse::failedResponse('上传的文件无效');
        }

        $dirPrefix = 'upload';
        $extraDir  = $request->input('dir');
        $savePath  = $extraDir ? $dirPrefix . DIRECTORY_SEPARATOR . $extraDir : 'upload/default';

        $path = $request->file->store($savePath);

        if (!$path) {
            return HttpResponse::failedResponse('上传文件失败');
        }

        return HttpResponse::successResponse([
            'file_path' => Storage::url($path)
        ]);
    }
}
