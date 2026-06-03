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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên đơn hàng');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('designer_id')->nullable()->comment('Nhân viên phụ trách thiết kế');

            // 1. Nhóm Tài chính (Đều là cột vật lý)
            $table->decimal('revenue', 15, 2)->default(0)->comment('Tổng tiền thu về');
            $table->decimal('material_cost', 15, 2)->default(0)->comment('Chi phí vật liệu');
            $table->decimal('labor_cost', 15, 2)->default(0)->comment('Chi phí nhân công (Sẽ auto cộng)');
            $table->decimal('profit', 15, 2)->default(0)->comment('Lợi nhuận (Sẽ auto tính)');

            // 2. Nhóm Tiến độ (Đều là cột vật lý)
            $table->integer('total_tasks')->default(0)->comment('Tổng số công việc');
            $table->integer('completed_tasks')->default(0)->comment('Số việc đã làm xong');
            $table->decimal('progress_percent', 5, 2)->default(0)->comment('% Tiến độ (Sẽ auto tính)');

            // 3. Trạng thái và Thông tin khác
            $table->string('status')->default('waiting')->comment('waiting, processing, completed');
            $table->dateTime('deadline')->nullable();
            $table->string('design_image')->nullable();
            $table->timestamps();

            $table->foreign('designer_id')->references('id')->on('nhan_viens')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
