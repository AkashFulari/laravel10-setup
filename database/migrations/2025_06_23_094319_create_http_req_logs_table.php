<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('http_req_logs', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->text("url")->nullable()->default(null);
            $table->string("by")->nullable()->default(null);
            $table->string("method")->nullable()->default(null);
            $table->string("ip")->nullable()->default(null);
            $table->longText("payload")->nullable()->default(null);
            $table->longText("response_content")->nullable()->default(null);
            $table->text("agent")->nullable()->default(null);
            $table->string("referer")->nullable()->default(null);
            $table->enum("status", ['Success', 'Fail'])->default('Fail');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('http_req_logs');
    }
};
