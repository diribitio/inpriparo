<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->string('image')->nullable()->default(null);
            $table->string('title')->unique();
            $table->foreignId('leader_id')->constrained('users');
            $table->mediumText('description');
            $table->integer('cost');
            $table->integer('min_grade');
            $table->integer('max_grade');
            $table->integer('min_participants');
            $table->integer('max_participants');
            $table->boolean('authorized')->default(false);
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
        Schema::dropIfExists('projects');
    }
}
