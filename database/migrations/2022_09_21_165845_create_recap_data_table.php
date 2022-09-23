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
        Schema::create('recap_data', function (Blueprint $table) {
            $table->id();
            $table->string('created_by');
            $table->enum('claim_type', ['WELLNESS', 'HEALTH', 'TAX', 'DEDUCTION']);
            $table->string('claim_name');
            $table->string('claim_description');
            $table->decimal('nominal', 10, 0);
            $table->tinyInteger('period_month');
            $table->year('period_year');
            $table->bigInteger('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('user_employees');
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
        Schema::dropIfExists('recap_data');
    }
};
