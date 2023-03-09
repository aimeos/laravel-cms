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
        Schema::connection(config('cms.db', 'sqlite'))->create('cms_contents', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('tenant_id');
            $table->string('lang', 5);
            $table->json('data');
            $table->string('editor');
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
            $table->index(['lang', 'tenant_id']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('cms.db', 'sqlite'))->dropIfExists('cms_contents');
    }
};
