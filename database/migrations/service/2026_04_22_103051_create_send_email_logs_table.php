<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('send_email_logs', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_send_email_logs', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('send_email_logs');
        Schema::dropIfExists('_history_send_email_logs');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {

        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {

            $table->index('email', 'send_email_logs_email_idx');
            $table->index('status', 'send_email_logs_status_idx');
            $table->index('subject_type', 'send_email_logs_subject_type_idx');
            $table->index('subject_id', 'send_email_logs_subject_id_idx');
        }
        // polymorphic relation
        $table->morphs('subject');

        // recipient
        $table->string('email');
        $table->json('data')
            ->nullable()
            ->after('subject_type');

        // mail metadata
        $table->string('mailable');        // App\Mail\GensenFormCreatedMail
        $table->string('view')->nullable();
        $table->string('subject_line')->nullable();

        // lifecycle
        $table->string('status'); // pending | sending | sent | failed

        // monitoring
        $table->unsignedInteger('attempts')->default(0);
        $table->text('error_message')->nullable();

        // provider tracking (VERY IMPORTANT)
        $table->string('provider')->nullable();      // brevo
        $table->string('provider_message_id')->nullable()->index();

        // timing
        $table->timestamp('started_at')->nullable();
        $table->timestamp('finished_at')->nullable();

        $table->bigInteger('created_by')->unsigned()->nullable();
        $table->bigInteger('updated_by')->unsigned()->nullable();
        $table->bigInteger('deleted_by')->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
