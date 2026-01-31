<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update "New Post Job Request" email content for admin and provider
     * so providers get a meaningful message with job details and link.
     */
    public function up(): void
    {
        $templateId = DB::table('mail_templates')->where('type', 'job_requested')->value('id');
        if (!$templateId) {
            return;
        }

        $adminSubject = 'New job request on [[ company_name ]]: [[ job_name ]]';
        $adminBody = '<p>Hello [[ admin_name ]],</p>
                                  <p>A new job request has been posted on [[ company_name ]].</p>
                                  <p><strong>Job #[[ job_id ]]</strong> – [[ job_name ]]</p>
                                  <p><strong>Posted by:</strong> [[ customer_name ]]</p>
                                  <p><strong>Description:</strong><br />[[ job_description_short ]]</p>
                                  <p><strong>Location:</strong> [[ job_address ]]</p>
                                  <p>You can review and manage this request in your admin panel.</p>
                                  <p>Best regards,<br />[[ company_name ]]</p>';

        DB::table('mail_template_content_mappings')
            ->where('template_id', $templateId)
            ->where('user_type', 'admin')
            ->update([
                'subject' => $adminSubject,
                'template_detail' => $adminBody,
                'updated_at' => now(),
            ]);

        $providerSubject = 'New job on [[ company_name ]]: [[ job_name ]] – submit your bid';
        $providerBody = '<p>Hello [[ provider_name ]],</p>
                                      <p>A new job request has been posted on [[ company_name ]] that may match your services. The customer is looking for an Employer to help with the following:</p>
                                      <p><strong>Job #[[ job_id ]] – [[ job_name ]]</strong></p>
                                      <p><strong>Posted by:</strong> [[ customer_name ]]</p>
                                      <p><strong>Description:</strong><br />[[ job_description_short ]]</p>
                                      <p><strong>Location:</strong> [[ job_address ]]</p>
                                      <p>If this job fits your skills, log in to [[ company_name ]] (or visit the app), view the full details, and submit your bid. The sooner you respond, the better your chances.</p>
                                      <p><strong><a href="[[ link ]]" style="color:#2563eb;">View job and submit your bid</a></strong></p>
                                      <p>If the link does not work, copy this URL into your browser: [[ link ]]</p>
                                      <p>Best regards,<br />[[ company_name ]]</p>';

        DB::table('mail_template_content_mappings')
            ->where('template_id', $templateId)
            ->where('user_type', 'provider')
            ->update([
                'subject' => $providerSubject,
                'template_detail' => $providerBody,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Revert to previous simple content (optional)
        $templateId = DB::table('mail_templates')->where('type', 'job_requested')->value('id');
        if (!$templateId) {
            return;
        }

        DB::table('mail_template_content_mappings')
            ->where('template_id', $templateId)
            ->where('user_type', 'admin')
            ->update([
                'subject' => 'New Custom Job Request',
                'template_detail' => '<p>Hello [[ admin_name ]],</p>
                                  <p>#[[ job_id ]] - [[ customer_name ]] has requested a new job request [[ job_name ]].</p>
                                  <p>&nbsp;</p>
                                  <p>Best regards,<br />[[ company_name ]]</p>',
                'updated_at' => now(),
            ]);

        DB::table('mail_template_content_mappings')
            ->where('template_id', $templateId)
            ->where('user_type', 'provider')
            ->update([
                'subject' => 'New Custom Job Request',
                'template_detail' => '<p>Hello [[ provider_name ]],</p>
                                      <p>#[[ job_id ]] - [[ customer_name ]] has requested a new job request [[ job_name ]].</p>
                                      <p>&nbsp;</p>
                                      <p>Best regards,<br />[[ company_name ]]</p>',
                'updated_at' => now(),
            ]);
    }
};
