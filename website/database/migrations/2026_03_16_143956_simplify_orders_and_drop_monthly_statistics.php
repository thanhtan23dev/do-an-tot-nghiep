<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('monthly_statistics');

        // 2. Xóa các cột tiền bạc và trạng thái rườm rà trong bảng Orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'design_status',
                'revenue',
                'material_cost',
                'labor_cost',
                'profit'
            ]);
        });
    }

    public function down()
    {
        // Hàm down để khôi phục (backup) nếu cần thiết
        Schema::table('orders', function (Blueprint $table) {
            $table->string('design_status')->default('waiting');
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('material_cost', 15, 2)->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
        });
    }
};
