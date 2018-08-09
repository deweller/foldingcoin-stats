<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Tokenly\LaravelApiProvider\Filter\RequestFilter;

class ApiController extends Controller
{
    protected function throwJsonResponseException($error_message, $status = 400)
    {
        $response = new JsonResponse([
            'success' => false,
            'message' => $error_message,
        ], $status);

        throw new HttpResponseException($response);
    }

    public function transformResourcesForOutput($resources, $context = null, $wrapper_function = null)
    {
        $out = [];
        foreach ($resources as $resource) {
            $out[] = $resource->serializeForAPI($context);
        }

        if ($wrapper_function !== null) {
            $out = $wrapper_function($out);
        }

        return $this->buildJSONResponse($out);
    }

    public function buidPagedResourcesForOutput($resources, RequestFilter $filter, $context=null, $wrapper_function=null) {
        $serialized_resources = [];
        foreach ($resources as $resource) {
            $serialized_resources[] = $resource->serializeForAPI($context);
        }

        if ($wrapper_function !== null) {
            $serialized_resources = $wrapper_function($out);
        }

        return $this->buildJSONResponse($this->buidPagedItemList($serialized_resources, $filter->used_page_offset, $filter->used_limit, $filter->getCountForPagination()));
    }

    public function buildJSONResponse($data, $http_code = 200)
    {
        return new JsonResponse($data, $http_code);
    }

    public function buidPagedItemList($items, $page_offset, $per_page, $total_item_count)
    {
        return [
            'page' => $page_offset,
            'perPage' => $per_page,
            'pageCount' => ceil($total_item_count / $per_page),
            'count' => $total_item_count,
            'items' => $items,
        ];
    }

}
