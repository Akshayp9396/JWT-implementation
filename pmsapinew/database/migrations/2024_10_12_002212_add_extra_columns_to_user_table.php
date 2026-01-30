<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraColumnsToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
             $table->string('mobile_number')->after("last_name")->nullable();
             $table->tinyInteger('patient_category')->after("mobile_number")->default(1)->comment("1 for ip , 2 for op");
             $table->timestamp('admission_date')->after("patient_category")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('mobile_number');
            $table->dropColumn('patient_category');
            $table->dropColumn('admission_date');
        });
    }
}
