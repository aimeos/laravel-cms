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
        Schema::connection(config('cms.db', 'sqlite'))->create('cms_versions', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('tenant_id');
            $table->string('versionable_id', 36);
            $table->string('versionable_type', 50);
            $table->boolean('published');
            $table->json('data');
            $table->string('editor');
            $table->timestamps();

            $table->primary('id');
            $table->index(['versionable_id', 'versionable_type', 'published', 'tenant_id'], 'idx_versions_id_type_published_tenantid');
            $table->index(['updated_at', 'published']);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('cms.db', 'sqlite'))->dropIfExists('cms_versions');
    }
};
