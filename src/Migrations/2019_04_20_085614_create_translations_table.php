<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function(Blueprint $table){
            $table->bigIncrements('id');

            $table->string('lang')->index();
            $table->string('key')->index();
            $table->longText('value');

            $table->unsignedBigInteger('translation_id')->index();
            $table->string('translation_type')->index();

            $table->unique(['lang', 'key', 'translation_id', 'translation_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translations');
    }
}
