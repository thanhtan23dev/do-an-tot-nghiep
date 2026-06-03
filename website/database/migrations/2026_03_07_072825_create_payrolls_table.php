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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nhan_vien_id')->constrained('nhan_viens')->onDelete('cascade');

            $table->integer('month');
            $table->integer('year');

            $table->integer('total_jobs')->default(0);
            $table->decimal('raw_salary', 15, 2)->default(0);
            $table->decimal('total_advance', 15, 2)->default(0);

            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('fine', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2)->default(0);

            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
};
