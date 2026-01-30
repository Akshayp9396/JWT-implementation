<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraColumnUserinfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('userinfo', function (Blueprint $table) {
            
             $table->tinyInteger('patient_category')->after("mobilephone")->default(1)->comment("1 for ip , 2 for op");
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
        Schema::table('userinfo', function (Blueprint $table) {
            
            $table->dropColumn('patient_category');
            $table->dropColumn('admission_date');
        });
    }
}
