<?php

use App\Database\BaseMigration;
use Illuminate\Database\Schema\Blueprint;

class CreateFailedJobsTable extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = $this->getSchemaBuilder();
        $schema->create('failed_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->getSchemaBuilder()->drop('failed_jobs');
    }
}
