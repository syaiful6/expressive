<?php

use App\Database\BaseMigration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = $this->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('is_active')->default(true);
            $table->boolean('is_sitter')->default(false);
            $table->boolean('is_staff')->default(false);
            $table->boolean('is_superuser')->default(false);
            $table->rememberToken();
            $table->dateTime('date_joined')->nullable();
            $table->dateTime('last_login')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->getSchemaBuilder()->drop('users');
    }
}
