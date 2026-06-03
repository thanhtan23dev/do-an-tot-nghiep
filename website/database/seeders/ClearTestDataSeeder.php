<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ClearTestDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Tắt kiểm tra khóa ngoại để tránh lỗi khi xóa
        Schema::disableForeignKeyConstraints();

        DB::table('orders')->truncate();
        DB::table('tasks')->truncate();
        DB::table('task_rejected_histories')->truncate();
        DB::table('customer_groups')->truncate();
        DB::table('formulas')->truncate();
        DB::table('monthly_payrolls')->truncate();
        DB::table('stone_prices')->truncate();

        
        DB::table('personal_access_tokens')->truncate();
        // DB::table('nhan_viens')->truncate();


        // 3. Bật lại kiểm tra khóa ngoại
        Schema::enableForeignKeyConstraints();

        // $this->command->info('Đã dọn sạch dữ liệu test!');
    }
}
