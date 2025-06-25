<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginationRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            "sort_by" => "nullable|string",
            'sort_order' => 'nullable|in:asc,desc',
            "page_no" => "nullable|integer|min:1",
            'limit' => 'nullable|integer',
            "date" => "nullable|date",
            "start_date" => "nullable|date",
            "end_date" => "nullable|date",
            "search" => "nullable|string",
        ];
    }
}
