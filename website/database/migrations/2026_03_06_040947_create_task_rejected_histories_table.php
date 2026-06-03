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
        Schema::create('task_rejected_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');

            $table->unsignedBigInteger('failed_worker_id')->nullable()->comment('Nhân công đã làm hỏng/không đạt');
            $table->decimal('old_reward', 15, 2)->default(0)->comment('Mức thù lao lúc đó');
            $table->text('reason')->nullable()->comment('Lý do không đạt (nếu quản lý có nhập)');

            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_rejected_histories');
    }
};
