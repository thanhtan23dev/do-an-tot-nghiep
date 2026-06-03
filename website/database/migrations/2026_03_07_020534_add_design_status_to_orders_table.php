<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('design_status')
                ->default('waiting')
                ->after('designer_id')
                ->comment('waiting: Chờ nhận, processing: Đang vẽ, rejected: Từ chối, completed: Hoàn thành');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('design_status');
        });
    }
};
