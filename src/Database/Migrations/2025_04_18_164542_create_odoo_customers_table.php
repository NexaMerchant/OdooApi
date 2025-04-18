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
        if (!Schema::hasTable('ba_odoo_orders')) {
            Schema::create('odoo_customers', function (Blueprint $table) {
                // 主键
                $table->bigIncrements('id')->comment('客户ID');

                // 基本信息
                $table->string('name', 191)->comment('客户名称');
                $table->string('email', 191)->comment('电子邮箱');
                $table->string('phone', 191)->comment('电话');
                $table->string('mobile', 191)->comment('手机');

                // 地址信息
                $table->string('street', 191)->comment('街道地址1');
                $table->string('street2', 191)->comment('街道地址2');
                $table->string('city', 191)->comment('城市');
                $table->string('zip', 191)->comment('邮编');
                $table->string('state_id', 191)->comment('州/省ID');
                $table->string('country_id', 191)->comment('国家ID');

                // 商业信息
                $table->string('vat', 191)->comment('增值税号');
                $table->string('function', 191)->comment('职位/职能');
                $table->string('title', 191)->comment('称呼/头衔');

                // 关联信息
                $table->string('company_id', 191)->comment('公司ID');
                $table->string('category_id', 191)->comment('分类ID');
                $table->string('user_id', 191)->comment('用户ID');
                $table->string('team_id', 191)->comment('团队ID');

                // 偏好设置
                $table->string('lang', 191)->comment('语言');
                $table->string('tz', 191)->comment('时区');

                // 状态标志
                $table->string('active', 191)->comment('是否激活');
                $table->string('customer', 191)->comment('是否客户');
                $table->string('company_type', 191)->comment('公司类型');
                $table->string('is_company', 191)->comment('是否公司');
                $table->string('color', 191)->comment('颜色标记');
                $table->string('partner_share', 191)->comment('合作伙伴共享');

                // 合作伙伴信息
                $table->string('commercial_partner_id', 191)->comment('商业伙伴ID');
                $table->string('type', 191)->comment('类型');

                // 注册相关
                $table->string('signup_token', 191)->comment('注册令牌');
                $table->string('signup_type', 191)->comment('注册类型');
                $table->string('signup_expiration', 191)->comment('注册过期时间');
                $table->string('signup_url', 191)->comment('注册URL');

                // 其他标识
                $table->string('partner_gid', 191)->comment('合作伙伴全局ID');

                // 日历通知
                $table->string('calendar_last_notif_ack', 191)->comment('最后通知确认');
                $table->string('calendar_contact_ack', 191)->comment('联系人确认');
                $table->string('calendar_last_notif_seen', 191)->comment('最后通知查看');
                $table->string('calendar_next_notif_ack', 191)->comment('下次通知确认');

                // 时间戳
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odoo_customers');
    }
};
