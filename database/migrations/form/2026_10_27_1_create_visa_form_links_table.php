<?php

use App\Models\Form\VisaFormLink;
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
        Schema::create('visa_form_links', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_visa_form_links', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('visa_form_links');
        Schema::dropIfExists('_history_visa_form_links');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();

            // unique access token (used in URL)
            $table->uuid('token');
        } else {
            $table->index('token', 'visa_form_links_token_idx');
            $table->index('pic_code', 'visa_form_links_pic_code_idx');
            $table->index('created_by', 'visa_form_links_created_by_idx');
            $table->index('expired_at', 'visa_form_links_expired_at_idx');
            $table->index('status', 'visa_form_links_status_idx');

            // unique access token (used in URL)
            $table->uuid('token')->unique();
        }

        $table->string('pic_code')->nullable();

        // optional password protection
        $table->string('password')->nullable();

        // usage control
        $table->string('name')->nullable();
        $table->unsignedInteger('max_usage')->default(1);
        $table->unsignedInteger('used_count')->default(0);

        // expiration
        $table->timestamp('expired_at')->nullable();

        // lifecycle state
        $table->string('status')->default(VisaFormLink::STATUS_ACTIVE);

        $table->bigInteger('created_by')->unsigned()->nullable();
        $table->bigInteger('updated_by')->unsigned()->nullable();
        $table->bigInteger('deleted_by')->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
