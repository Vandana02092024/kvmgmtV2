<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('claim_form_links', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            // Legacy production tables have varying signed ID types. Keep
            // indexed references without changing their existing schemas.
            $table->unsignedInteger('location_id')->index();
            $table->unsignedInteger('invited_by')->index();
            $table->string('email', 50);
            $table->char('token_hash', 64)->unique();
            $table->dateTime('expires_at')->index();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->integer('reimbursement_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_form_links');
    }
};
