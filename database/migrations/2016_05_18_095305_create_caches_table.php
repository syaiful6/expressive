<?php

use App\Database\BaseMigration;
use Illuminate\Database\Schema\Blueprint;

class CreateCachesTable extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = $this->getSchemaBuilder();
        $schema->create('cache', function (Blueprint $table) {
            $table->string('key')->unique();
            $table->text('value');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->getSchemaBuilder()->drop('cache');
    }
}
