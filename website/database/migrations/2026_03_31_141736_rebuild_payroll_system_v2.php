<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        if (Schema::hasColumn('tasks', 'payroll_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                @$table->dropForeign(['payroll_id']);
                $table->dropColumn('payroll_id');
            });
        }

        if (Schema::hasColumn('orders', 'payroll_id')) {
            Schema::table('orders', function (Blueprint $table) {
                @$table->dropForeign('orders_payroll_id_foreign');
                $table->dropColumn('payroll_id');
            });
        }


        Schema::dropIfExists('advances');
        Schema::dropIfExists('payrolls');


        Schema::table('nhan_viens', function (Blueprint $table) {
            $table->decimal('total_balance', 15, 2)->default(0)
                ->comment('Tổng số dư hiện tại (Tiền làm ra - Tiền đã rút/trừ)');
        });


        Schema::create('monthly_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nhan_vien_id')->constrained('nhan_viens')->onDelete('cascade');
            $table->integer('month')->comment('Tháng');
            $table->integer('year')->comment('Năm');

            $table->decimal('earned_amount', 15, 2)->default(0)
                ->comment('Giá trị đã được nghiệm thu trong tháng này');

            $table->decimal('withdrawn_amount', 15, 2)->default(0)
                ->comment('Giá trị ứng / Rút về');

            $table->decimal('deduction_amount', 15, 2)->default(0)
                ->comment('Số tiền trừ (Vật tư, chi phí...)');

            $table->text('deduction_note')->nullable()
                ->comment('Ghi chú lý do trừ');

            $table->enum('status', ['pending', 'paid'])->default('pending')
                ->comment('pending: Chưa thanh toán, paid: Đã thanh toán');

            $table->timestamps();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('monthly_payroll_id')->nullable()
                ->constrained('monthly_payrolls')->nullOnDelete()
                ->comment('Công việc này được tổng hợp vào phiếu chốt sổ nào');
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['monthly_payroll_id']);
            $table->dropColumn('monthly_payroll_id');
            $table->unsignedBigInteger('payroll_id')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_id')->nullable();
        });

        Schema::dropIfExists('monthly_payrolls');

        Schema::table('nhan_viens', function (Blueprint $table) {
            $table->dropColumn('total_balance');
        });
    }
};
