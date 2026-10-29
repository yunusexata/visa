<?php

use App\Enums\Gensen\GensenAttachmenStatus;
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
        Schema::create('visa_form_attachments', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_visa_form_attachments', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('visa_form_attachments');
        Schema::dropIfExists('_history_visa_form_attachments');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
        }

        $table->unsignedBigInteger('visa_form_id');
        $table->string('type');
        $table->string('original_name');       // KK_andi.jpg
        $table->text('stored_name');         // uuid filename
        $table->text('description')->nullable()->default(null);

        $table->string('disk');                // local / s3
        $table->text('path');                // storage path
        $table->text('note')->nullable();                // Attachment Note

        $table->string('extension');           // jpg
        $table->string('mime_type');           // image/jpeg
        $table->string('file_size');           // bytes

        $table->text('checksum');            // sha256 hash (anti duplicate)
        $table->string('status')->nullable();                // Attachment Note
        $table->boolean('convert_image')->default(false)->nullable();                // Attachment Note

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
