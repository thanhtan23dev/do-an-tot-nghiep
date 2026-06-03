<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nhan_viens', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('phone', 20)->unique();

            $table->string('password');

            $table->string('address')->nullable();

            $table->tinyInteger('role')->comment('1: Thiết kế, 2: Nhân công');

            $table->boolean('is_active')->default(true)->comment('Trạng thái làm việc');

            $table->rememberToken();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nhan_viens');
    }
};
