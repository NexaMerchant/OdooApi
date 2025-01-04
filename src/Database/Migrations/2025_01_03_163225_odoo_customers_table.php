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
        //
        Schema::create('odoo_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('mobile');
            $table->string('street');
            $table->string('street2');
            $table->string('city');
            $table->string('zip');
            $table->string('state_id');
            $table->string('country_id');
            $table->string('vat');
            $table->string('function');
            $table->string('title');
            $table->string('company_id');
            $table->string('category_id');
            $table->string('user_id');
            $table->string('team_id');
            $table->string('lang');
            $table->string('tz');
            $table->string('active');
            $table->string('customer');
            $table->string('company_type');
            $table->string('is_company');
            $table->string('color');
            $table->string('partner_share');
            $table->string('commercial_partner_id');
            $table->string('type');
            $table->string('signup_token');
            $table->string('signup_type');
            $table->string('signup_expiration');
            $table->string('signup_url');
            $table->string('partner_gid');
            $table->string('calendar_last_notif_ack');
            $table->string('calendar_contact_ack');
            $table->string('calendar_last_notif_seen');
            $table->string('calendar_next_notif_ack');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('odoo_customers');
    }
};
