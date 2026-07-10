-- =============================================================================
-- frobster.com (EN database only)
-- Fixes typos in existing EN rows — no DE rows added.
-- =============================================================================
SET NAMES utf8mb4;

-- Fix "Hallo [[ provider_name ]]" → "Hello [[ provider_name ]]" in EN rows
UPDATE mail_template_content_mappings
SET template_detail = REPLACE(template_detail, '<p>Hallo [[ provider_name ]],</p>', '<p>Hello [[ provider_name ]],</p>'),
    updated_at = NOW()
WHERE language = 'en' AND template_detail LIKE '%<p>Hallo [[ provider_name ]],</p>%';

-- Fix contact@frobster.com → info@frobster.com in all EN rows
UPDATE mail_template_content_mappings
SET template_detail = REPLACE(template_detail, 'contact@frobster.com', 'info@frobster.com'),
    updated_at = NOW()
WHERE language = 'en' AND template_detail LIKE '%contact@frobster.com%';

-- Fix "FROBSTERR Team" / "FROBSTER Team" → "Frobster-Team" in EN rows
UPDATE mail_template_content_mappings
SET template_detail = REPLACE(REPLACE(template_detail, 'FROBSTERR Team', 'Frobster-Team'), 'FROBSTER Team', 'Frobster-Team'),
    updated_at = NOW()
WHERE language = 'en' AND (template_detail LIKE '%FROBSTERR Team%' OR template_detail LIKE '%FROBSTER Team%');

-- Fix {{ $var }} Blade syntax → [[ var ]] in job_requested EN rows
UPDATE mail_template_content_mappings SET
    template_detail = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        template_detail,
        '{{ $admin_name }}',             '[[ admin_name ]]'),
        '{{ $provider_name }}',          '[[ provider_name ]]'),
        '{{ $company_name }}',           '[[ company_name ]]'),
        '{{ $job_id }}',                 '[[ job_id ]]'),
        '{{ $job_request_name }}',       '[[ job_request_name ]]'),
        '{{ $customer_name }}',          '[[ customer_name ]]'),
        '{{ $job_request_start_date }}', '[[ job_request_start_date ]]'),
        '{{ $job_request_end_date }}',   '[[ job_request_end_date ]]'),
        '{{ $job_request_city }}',       '[[ job_request_city ]]'),
        '{{ $job_country }}',            '[[ job_country ]]'),
        '{{ $job_request_amount }}',     '[[ job_request_amount ]]'),
        '{{ $job_request_created_at }}', '[[ job_request_created_at ]]'),
    updated_at = NOW()
WHERE language = 'en' AND template_detail LIKE '%{{ $%';
