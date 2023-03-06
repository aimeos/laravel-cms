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
        Schema::connection(config('cms.db', 'sqlite'))->create('cms_files', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('tenant_id');
            $table->string('mime', 100);
            $table->string('tag', 30);
            $table->string('name');
            $table->string('path');
            $table->json('previews');
            $table->string('editor');
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('cms.db', 'sqlite'))->dropIfExists('cms_files');
    }
};
