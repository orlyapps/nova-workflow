<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WorkflowMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_log', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('subject_id')->unsigned()->nullable();
            $table->string('subject_type')->nullable();
            $table->index(['subject_id', 'subject_type']);

            $table->unsignedBigInteger('causer_id')->unsigned()->nullable();
            $table->string('causer_type')->nullable();
            $table->index(['causer_id', 'causer_type']);
            $table->text('comment')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('transition')->nullable();
            $table->dateTime('due_at')->nullable();
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
        Schema::dropIfExists('workflow_log');
    }
}
