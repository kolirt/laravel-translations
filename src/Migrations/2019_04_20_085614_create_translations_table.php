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
        Schema::create('translations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('lang');
            $table->string('key');

            $table->string('string')->nullable();

            $table->text('text')->nullable();
            $table->mediumText('mediumText')->nullable();
            $table->longText('longText')->nullable();

            $table->smallInteger('smallInteger')->nullable();
            $table->tinyInteger('tinyInteger')->nullable();
            $table->integer('integer')->nullable();
            $table->mediumInteger('mediumInteger')->nullable();
            $table->bigInteger('bigInteger')->nullable();

            $table->decimal('decimal')->nullable();
            $table->boolean('boolean')->nullable();

            $table->date('date')->nullable();
            $table->dateTime('dateTime')->nullable();
            $table->timestamp('timestamp')->nullable();

            $table->unsignedBigInteger('translation_id');
            $table->string('translation_type');

            $table->unique(['lang', 'key', 'translation_id', 'translation_type']);
            $table->index(['lang', 'key', 'translation_id', 'translation_type']);
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
