<?php

namespace App\Services;

class PiiDetector
{
    /**
     * Detect PII / contact-sharing attempts in a chat message.
     * Returns [bool $found, string[] $types]
     */
    public static function detect(?string $text): array
    {
        $text = (string) ($text ?? '');
        if ($text === '') {
            return [false, []];
        }

        // ── Step 1: Unicode normalisation ────────────────────────────────────
        // Converts fullwidth digits ０１２３ → 012, superscripts, etc.
        if (class_exists('Normalizer')) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_NFKC) ?: $text;
        }

        // Fullwidth digits (manual fallback if Normalizer not available)
        $fullwidth = ['０'=>'0','１'=>'1','２'=>'2','３'=>'3','４'=>'4','５'=>'5','６'=>'6','７'=>'7','８'=>'8','９'=>'9'];
        $text = strtr($text, $fullwidth);

        // Circled/enclosed digits  ①②③ → 123
        $circled = ['⓪'=>'0','①'=>'1','②'=>'2','③'=>'3','④'=>'4','⑤'=>'5','⑥'=>'6','⑦'=>'7','⑧'=>'8','⑨'=>'9',
                    '⑩'=>'10','⑳'=>'20','❶'=>'1','❷'=>'2','❸'=>'3','❹'=>'4','❺'=>'5','❻'=>'6','❼'=>'7','❽'=>'8','❾'=>'9'];
        $text = strtr($text, $circled);

        // Emoji keycap digits  0️⃣1️⃣2️⃣ → 012
        $emojiDigits = [
            "0\u{FE0F}\u{20E3}"=>'0',"1\u{FE0F}\u{20E3}"=>'1',"2\u{FE0F}\u{20E3}"=>'2',
            "3\u{FE0F}\u{20E3}"=>'3',"4\u{FE0F}\u{20E3}"=>'4',"5\u{FE0F}\u{20E3}"=>'5',
            "6\u{FE0F}\u{20E3}"=>'6',"7\u{FE0F}\u{20E3}"=>'7',"8\u{FE0F}\u{20E3}"=>'8',
            "9\u{FE0F}\u{20E3}"=>'9',
            "\u{0030}\u{20E3}"=>'0',"\u{0031}\u{20E3}"=>'1',"\u{0032}\u{20E3}"=>'2',
            "\u{0033}\u{20E3}"=>'3',"\u{0034}\u{20E3}"=>'4',"\u{0035}\u{20E3}"=>'5',
            "\u{0036}\u{20E3}"=>'6',"\u{0037}\u{20E3}"=>'7',"\u{0038}\u{20E3}"=>'8',
            "\u{0039}\u{20E3}"=>'9',
        ];
        $text = strtr($text, $emojiDigits);

        // Strip zero-width characters used to break keyword detection
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00AD}]/u', '', $text);

        // Strip emoji separators between digits (e.g. "030🔴122🔴3333")
        $text = preg_replace('/(\d)\s*[^\w\s,.\-+()@]\s*(?=\d)/u', '$1', $text);

        $hay   = mb_strtolower($text);
        $types = [];

        // ── Step 2: Normalise text obfuscation ───────────────────────────────
        $norm = $hay;
        $norm = preg_replace('/\b(at the rate|at)\b/', '@', $norm);
        $norm = str_ireplace(['[at]','(at)','{at}',' @ '], '@', $norm);
        $norm = preg_replace('/\b(dot|punkt|punkte)\b/', '.', $norm);
        $norm = str_ireplace(['[dot]','(dot)','{dot}'], '.', $norm);
        $norm = preg_replace('/\s*(@|\.)\s*/', '$1', $norm);

        // l33t-speak: @ → a, 3 → e, 1 → i/l, 0 → o, 5 → s etc. for provider names
        $norm = preg_replace('/g[\s\*\-_]*m[\s\*\-_]*a[\s\*\-_]*i[\s\*\-_]*l/', 'gmail', $norm);
        $norm = preg_replace('/y[\s\*\-_]*a[\s\*\-_]*h[\s\*\-_]*o[\s\*\-_]*o/', 'yahoo', $norm);
        $norm = preg_replace('/h[\s\*\-_]*o[\s\*\-_]*t[\s\*\-_]*m[\s\*\-_]*a[\s\*\-_]*i[\s\*\-_]*l/', 'hotmail', $norm);
        $norm = preg_replace('/out[\s\*\-_]*look/', 'outlook', $norm);
        $norm = preg_replace('/proton[\s\*\-_]*mail/', 'protonmail', $norm);
        $norm = preg_replace('/i[\s\*\-_]*cloud/', 'icloud', $norm);
        $norm = preg_replace('/web[\s\*\-_]*\.?[\s\*\-_]*de/', 'web.de', $norm);
        $norm = preg_replace('/t[\s\*\-_]*-?[\s\*\-_]*online/', 't-online', $norm);

        // ── Step 3: Email ────────────────────────────────────────────────────
        $emailRe = '/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i';
        if (preg_match($emailRe, $hay) || preg_match($emailRe, $norm)) {
            $types[] = 'email';
        }

        // Email provider keywords — even without @ sign
        $emailProviders = [
            'gmail','yahoo','hotmail','outlook','icloud','protonmail',
            'ymail','gmx','aol','mail.com','yandex','zoho',
            // German providers
            'web.de','gmx.de','gmx.net','t-online','freenet','posteo','mailbox.org',
        ];
        foreach ($emailProviders as $p) {
            if (strpos($hay, $p) !== false) { $types[] = 'email'; break; }
        }

        // Phonetic/letter-by-letter email spelling: "j - o - h - n at g mail dot com"
        // Detect alphabet soup: 3+ single letters separated by spaces/dashes then @
        if (preg_match('/\b[a-z]\b[\s\-\.]+\b[a-z]\b[\s\-\.]+\b[a-z]\b/i', $hay) && preg_match('/@|at\b|dot\b/i', $hay)) {
            $types[] = 'email';
        }

        // ── Step 4: Phone numbers ────────────────────────────────────────────
        // Standard numeric with 7+ digits
        if (preg_match('/(?:(?:\+|00)?\d{1,3}[\s.\-]?)?(?:\(?\d{2,4}\)?[\s.\-]?)?\d{3,4}[\s.\-]?\d{3,4}/', $hay)) {
            if (preg_match_all('/\d/', $hay, $dm) && count($dm[0]) >= 7) {
                $types[] = 'phone';
            }
        }

        // Single-space-separated digits: "0 3 0 1 2 3 4 5 6 7"
        if (!in_array('phone', $types) && preg_match('/(?<!\d)\d(\s\d){6,}(?!\d)/', $hay)) {
            $types[] = 'phone';
        }

        // Creative separators: digits separated by *, -, /, |, or letters used as fillers
        if (!in_array('phone', $types)) {
            $stripped = preg_replace('/[^0-9]/', '', $hay);
            // Only flag if there are 7+ digit runs and text contains common phone separators
            if (strlen($stripped) >= 7 && preg_match('/\d[\s\*\|\/\\\\\-]{1,3}\d[\s\*\|\/\\\\\-]{1,3}\d/', $hay)) {
                $types[] = 'phone';
            }
        }

        // English spelled-out digits: "zero three one …" / "double five …"
        if (!in_array('phone', $types)) {
            $wordMap = ['zero'=>'0','oh'=>'0','o'=>'0','one'=>'1','two'=>'2','three'=>'3',
                        'four'=>'4','five'=>'5','six'=>'6','seven'=>'7','eight'=>'8','nine'=>'9'];
            $tokens     = preg_split('/[^a-z0-9+]+/', $hay, -1, PREG_SPLIT_NO_EMPTY);
            $digitCount = 0; $repeat = 1;
            foreach ($tokens as $tok) {
                if ($tok === 'double') { $repeat = 2; continue; }
                if ($tok === 'triple') { $repeat = 3; continue; }
                $add = '';
                if (isset($wordMap[$tok])) { $add = str_repeat($wordMap[$tok], $repeat); }
                elseif (preg_match('/^\+?\d+$/', $tok)) { $add = str_repeat(preg_replace('/\D/', '', $tok), $repeat); }
                if ($add !== '') { $digitCount += strlen($add); if ($digitCount >= 7) { $types[] = 'phone'; break; } }
                $repeat = 1;
            }
        }

        // German spelled-out digits: "null drei null eins …"
        if (!in_array('phone', $types)) {
            $deMap = ['null'=>'0','nul'=>'0','eins'=>'1','ein'=>'1','zwei'=>'2','drei'=>'3',
                      'vier'=>'4','fuenf'=>'5','fünf'=>'5','sechs'=>'6','sieben'=>'7',
                      'acht'=>'8','neun'=>'9','zehn'=>'10','zwanzig'=>'20',
                      'dreissig'=>'30','dreißig'=>'30','vierzig'=>'40','fünfzig'=>'50',
                      'fuenfzig'=>'50','sechzig'=>'60','siebzig'=>'70','achtzig'=>'80','neunzig'=>'90'];
            $tokens     = preg_split('/[\s,\-\/]+/', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
            $digitCount = 0;
            foreach ($tokens as $tok) {
                if (isset($deMap[$tok])) { $digitCount += strlen($deMap[$tok]); if ($digitCount >= 7) { $types[] = 'phone'; break; } }
                elseif (preg_match('/^\d+$/', $tok)) { $digitCount += strlen($tok); if ($digitCount >= 7) { $types[] = 'phone'; break; } }
                else { $digitCount = 0; } // reset on non-number word
            }
        }

        // Mixed English+German number words in same message
        if (!in_array('phone', $types)) {
            $mixMap = ['zero'=>'0','null'=>'0','nul'=>'0','oh'=>'0',
                       'one'=>'1','eins'=>'1','ein'=>'1',
                       'two'=>'2','zwei'=>'2','three'=>'3','drei'=>'3',
                       'four'=>'4','vier'=>'4','five'=>'5','fuenf'=>'5','fünf'=>'5',
                       'six'=>'6','sechs'=>'6','seven'=>'7','sieben'=>'7',
                       'eight'=>'8','acht'=>'8','nine'=>'9','neun'=>'9'];
            $tokens     = preg_split('/[^a-z0-9+äöüß]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
            $digitCount = 0;
            foreach ($tokens as $tok) {
                if (isset($mixMap[$tok])) { $digitCount++; if ($digitCount >= 7) { $types[] = 'phone'; break; } }
                elseif (preg_match('/^\d+$/', $tok)) { $digitCount += strlen($tok); if ($digitCount >= 7) { $types[] = 'phone'; break; } }
                else { $digitCount = 0; }
            }
        }

        // ── Step 5: Messaging platforms ──────────────────────────────────────
        // WhatsApp
        if (preg_match('/\bwhatsapp\b|wa\.me|api\.whatsapp/i', $hay) ||
            preg_match('/\b(my|on|via|über)\s*(WA|w\.a\.)\b/i', $hay) ||
            preg_match('/\bWA\s*(number|num|nr|no|#|:)/i', $hay)) {
            $types[] = 'whatsapp';
        }

        // Telegram
        if (preg_match('/\btelegram\b|t\.me\//i', $hay) ||
            preg_match('/\b(my|on|via)\s*TG\b/i', $hay) ||
            preg_match('/\bTG\s*(id|username|:)/i', $hay)) {
            $types[] = 'telegram';
        }

        // Skype
        if (preg_match('/\bskype\b/i', $hay)) { $types[] = 'skype'; }

        // Signal
        if (preg_match('/\bsignal\s*(app|me|id|number|chat|\.org)|get\s+signal|use\s+signal/i', $hay) ||
            strpos($hay, 'signal.org') !== false) {
            $types[] = 'signal';
        }

        // Viber
        if (preg_match('/\bviber\b/i', $hay)) { $types[] = 'viber'; }

        // WeChat
        if (preg_match('/\bwechat\b|微信/iu', $hay)) { $types[] = 'wechat'; }

        // Line app
        if (preg_match('/\bline\s*(app|id|chat|me)\b|my\s+line\s+(is|:)/i', $hay) ||
            strpos($hay, 'line.me') !== false) {
            $types[] = 'line';
        }

        // Discord (including tag format username#1234)
        if (preg_match('/\bdiscord\b/i', $hay) || preg_match('/\b\w{2,32}#\d{4}\b/', $hay)) {
            $types[] = 'discord';
        }

        // Microsoft Teams
        if (preg_match('/\b(ms\s+teams|microsoft\s+teams|teams\s+id|teams\s+chat)\b/i', $hay)) {
            $types[] = 'teams';
        }

        // Zoom
        if (preg_match('/\bzoom\s*(id|link|meeting|call|\.us)\b|join\s+(my\s+)?zoom\b/i', $hay) ||
            strpos($hay, 'zoom.us') !== false) {
            $types[] = 'zoom';
        }

        // Snapchat
        if (preg_match('/\bsnapchat\b|my\s+snap\b|\bsnap\s*(id|me|chat)\b/i', $hay)) {
            $types[] = 'snapchat';
        }

        // Instagram
        if (preg_match('/\binstagram\b|my\s+insta\b|\big\s*:/i', $hay) ||
            strpos($hay, 'instagram.com') !== false) {
            $types[] = 'instagram';
        }

        // Twitter / X
        if (preg_match('/\btwitter\b|dm\s+me\s+on\s+x\b/i', $hay) ||
            strpos($hay, 'twitter.com') !== false || strpos($hay, 'x.com/') !== false) {
            $types[] = 'twitter';
        }

        // LinkedIn
        if (preg_match('/\blinkedin\b/i', $hay) || strpos($hay, 'linkedin.com') !== false) {
            $types[] = 'linkedin';
        }

        // XING — German professional network (very relevant for DE market)
        if (preg_match('/\bxing\b/i', $hay) || strpos($hay, 'xing.com') !== false) {
            $types[] = 'xing';
        }

        // Facebook / Messenger
        if (preg_match('/\bfacebook\b|\bmessenger\b/i', $hay) ||
            strpos($hay, 'facebook.com') !== false || strpos($hay, 'fb.com') !== false ||
            strpos($hay, 'm.me/') !== false || strpos($hay, 'messenger.com') !== false) {
            $types[] = 'facebook';
        }

        // Freelancer platforms (off-platform payment/contact avoidance)
        if (preg_match('/\b(fiverr|upwork|freelancer\.com|toptal|guru\.com|peopleperhour)\b/i', $hay)) {
            $types[] = 'freelancer_platform';
        }

        // Payment apps (often used to arrange off-platform payments)
        if (preg_match('/\b(paypal\.me|revolut\.me|venmo|cash\s*app|\$[a-z0-9_]+)\b|paypal\s*me/i', $hay)) {
            $types[] = 'payment_app';
        }

        // Jitsi / Whereby / other video call links
        if (preg_match('/\bjitsi\b|meet\.jit\.si|whereby\.com|meet\.google\.com/i', $hay)) {
            $types[] = 'video_call';
        }

        // ── Step 6: Any external URL ──────────────────────────────────────────
        if (preg_match('#https?://#i', $hay)) { $types[] = 'url'; }

        // URL shorteners (even without http)
        $shorteners = ['bit.ly','tinyurl.com','t.co','ow.ly','goo.gl','is.gd','buff.ly','rb.gy','short.link'];
        foreach ($shorteners as $s) {
            if (strpos($hay, $s) !== false) { $types[] = 'url'; break; }
        }

        // Calendly / scheduling
        if (strpos($hay, 'calendly.com') !== false || strpos($hay, 'cal.com') !== false ||
            strpos($hay, 'doodle.com') !== false) {
            $types[] = 'url';
        }

        // File-sharing / cloud (can contain PII in files)
        if (preg_match('/\b(wetransfer|we\s+transfer|dropbox\.com|drive\.google|onedrive)\b/i', $hay)) {
            $types[] = 'url';
        }

        // ── Step 7: @username social handles ─────────────────────────────────
        if (!in_array('email', $types) && preg_match('/@[a-z0-9_\.]{3,}/i', $hay)) {
            $types[] = 'social_handle';
        }

        // ── Step 8: Contact-intent phrases ───────────────────────────────────
        $intentPhrases = [
            // English
            'call me','reach me','contact me','find me on','message me on','dm me',
            'text me','add me on','ping me','hit me up','connect on','reach out on',
            'look me up','search for me','send me your','my number is','my email is',
            'my handle is','my username is','drop me a','give me a call','give me a ring',
            'let\'s take this offline','let\'s continue outside','outside this platform',
            'off platform','off the platform',
            // German
            'ruf mich an','schreib mir','kontaktiere mich','füge mich hinzu',
            'finde mich auf','erreich mich','meine nummer','meine email','meine e-mail',
            'ich bin auf','schick mir','lass uns woanders','außerhalb der plattform',
        ];
        foreach ($intentPhrases as $phrase) {
            if (strpos($hay, $phrase) !== false) { $types[] = 'contact_intent'; break; }
        }

        $types = array_values(array_unique($types));
        return [!empty($types), $types];
    }
}
