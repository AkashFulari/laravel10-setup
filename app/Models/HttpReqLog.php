<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HttpReqLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "url",
        "by",
        "method",
        "ip",
        "payload",
        "response_content",
        "agent",
        "referer",
        "status",
    ];

    protected $hidden = [
        "deleted_at",
        "created_at",
        "updated_at",
    ];

    public static function GetAll()
    {
        return self::orderBy("created_at", "desc")->get();
    }
}
