<?php

use App\Database\BaseMigration;
use Illuminate\Database\Schema\Blueprint;

class CreatePasswordResets extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = $this->getSchemaBuilder();
        $schema->create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->getSchemaBuilder()->drop('password_resets');
    }
}
