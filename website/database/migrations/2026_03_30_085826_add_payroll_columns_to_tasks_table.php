<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Bảng Đơn giá đá
        Schema::create('stone_prices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price_per', 15, 2);
            $table->timestamps();
        });

        // 2. Bảng Công thức tính (Formulas)
        Schema::create('formulas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('expression'); // Biểu thức: (D * R * C) / 1000000000
            $table->string('unit')->nullable();
            $table->timestamps();
        });

        // 3. Thêm cột vào bảng tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('length')->nullable()->comment('Chiều dài (mm)');
            $table->integer('width')->nullable()->comment('Chiều rộng (mm)');
            $table->integer('height')->nullable()->comment('Chiều cao (mm)');

            // THÊM 2 CỘT QUẢN LÝ SỐ LƯỢNG
            $table->integer('item_count')->default(1)->comment('Số lượng (Tấm/Viên...)');
            $table->integer('completed_count')->default(0)->comment('Số lượng đã hoàn thành');

            $table->decimal('quantity', 10, 5)->nullable()->comment('Tổng khối lượng');
            $table->string('unit')->nullable()->comment('Đơn vị tính');

            $table->unsignedBigInteger('formula_id')->nullable()->comment('Khóa ngoại Công thức');
            $table->unsignedBigInteger('stone_price_id')->nullable()->comment('Khóa ngoại Đơn giá');
            $table->text('task_files')->nullable()->comment('Bản vẽ chi tiết (Nhiều file)');
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'length',
                'width',
                'height',
                'item_count',
                'completed_count', // Drop 2 cột mới
                'quantity',
                'unit',
                'formula_id',
                'stone_price_id',
                'task_files'
            ]);
        });
        Schema::dropIfExists('formulas');
        Schema::dropIfExists('stone_prices');
    }
};
