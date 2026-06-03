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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('name');
            $table->unsignedBigInteger('worker_id')->nullable()->comment('Người thi công HIỆN TẠI');
            $table->text('description')->nullable();
            $table->decimal('reward', 15, 2)->default(0)->comment('Thù lao HIỆN TẠI');
            $table->string('status')->default('waiting')->comment('waiting, processing, pending_review, rejected, completed');
            $table->dateTime('deadline')->nullable();
            $table->text('reject_reason')->nullable()->comment('Lý do không đạt (dành cho lần nghiệm thu gần nhất)');
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('worker_id')->references('id')->on('nhan_viens')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
