<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ session()->has('dir') ? session()->get('dir') : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseUrl" content="{{ env('APP_URL') }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Dynamic Theme Colors - Red-Blue Gradient -->
    <script>
        // Red-Blue Gradient Primary Color - set globally
        const root = document.documentElement;
        
        // Red color (from gradient)
        const redHex = '#FF0000';
        const redR = 255;
        const redG = 0;
        const redB = 0;
        
        // Blue color (from gradient)
        const blueHex = '#5F60B9';
        const blueR = 95;
        const blueG = 96;
        const blueB = 185;
        
        // Set gradient as primary
        root.style.setProperty('--bs-primary-gradient', 'linear-gradient(135deg, #FF0000 0%, #5F60B9 100%)');
        root.style.setProperty('--bs-primary', blueHex); // Fallback for non-gradient support
        root.style.setProperty('--bs-primary-rgb', `${blueR}, ${blueG}, ${blueB}`);
        root.style.setProperty('--bs-primary-bg-subtle', `linear-gradient(135deg, rgba(255, 0, 0, 0.09) 0%, rgba(95, 96, 185, 0.09) 100%)`);
        root.style.setProperty('--bs-primary-border-subtle', `linear-gradient(135deg, rgba(255, 0, 0, 0.09) 0%, rgba(95, 96, 185, 0.09) 100%)`);
        root.style.setProperty('--bs-primary-hover-bg', `linear-gradient(135deg, rgba(255, 0, 0, 0.75) 0%, rgba(95, 96, 185, 0.75) 100%)`);
        root.style.setProperty('--bs-primary-hover-border', `linear-gradient(135deg, rgba(255, 0, 0, 0.75) 0%, rgba(95, 96, 185, 0.75) 100%)`);
        root.style.setProperty('--bs-primary-active-bg', `linear-gradient(135deg, rgba(255, 0, 0, 0.75) 0%, rgba(95, 96, 185, 0.75) 100%)`);
        root.style.setProperty('--bs-primary-active-border', `linear-gradient(135deg, rgba(255, 0, 0, 0.75) 0%, rgba(95, 96, 185, 0.75) 100%)`);
    </script>

    @include('partials._head') <!-- Your other head includes like CSS files -->
    
    <style>
        /* Global Red-Blue Gradient Styles - Applied Throughout Project */
        :root {
            --red-blue-gradient: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%);
            --red-blue-gradient-hover: linear-gradient(135deg, #cc0000 0%, #4a4d94 100%);
            --red-blue-gradient-light: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%);
        }

        /* Apply gradient to all primary buttons */
        .btn-primary,
        button.btn-primary,
        input[type="submit"].btn-primary,
        a.btn-primary {
            background: var(--red-blue-gradient) !important;
            border: none !important;
            color: #fff !important;
        }

        .btn-primary:hover,
        button.btn-primary:hover,
        input[type="submit"].btn-primary:hover,
        a.btn-primary:hover {
            background: var(--red-blue-gradient-hover) !important;
            color: #fff !important;
        }

        /* Apply gradient to primary links */
        a.text-primary,
        .text-primary {
            background: var(--red-blue-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Apply gradient to primary backgrounds */
        .bg-primary {
            background: var(--red-blue-gradient) !important;
        }

        /* Apply gradient to primary borders */
        .border-primary {
            border-color: transparent !important;
            background: var(--red-blue-gradient) !important;
            background-clip: padding-box, border-box;
            background-origin: padding-box, border-box;
            border: 2px solid transparent;
        }

        /* Apply gradient to cards and components with primary color */
        .card-primary,
        .badge-primary,
        .alert-primary {
            background: var(--red-blue-gradient) !important;
            color: #fff !important;
        }

        /* Apply gradient to navigation items */
        .nav-link.active,
        .nav-pills .nav-link.active {
            background: var(--red-blue-gradient) !important;
            color: #fff !important;
        }

        /* Apply gradient to progress bars */
        .progress-bar.bg-primary {
            background: var(--red-blue-gradient) !important;
        }

        /* Apply gradient to pagination */
        .page-item.active .page-link {
            background: var(--red-blue-gradient) !important;
            border-color: transparent !important;
        }

        /* Apply gradient to form controls */
        .form-control:focus,
        .form-select:focus {
            border-color: #5F60B9 !important;
            box-shadow: 0 0 0 0.25rem rgba(95, 96, 185, 0.25) !important;
        }

        /* Apply gradient to custom elements using var(--bs-primary) */
        [style*="--bs-primary"],
        [style*="background-color: var(--bs-primary)"] {
            background: var(--red-blue-gradient) !important;
        }
    </style>

</head>

<body class="" id="app">
    @include('partials._body') <!-- Your body content -->
    <audio id="chatRingtone" preload="auto" src="{{ env('CHAT_RINGTONE_URL', asset('audio/chat.mp3')) }}"></audio>
    <script>
        (function(){
            const pingUrl = '{{ route('chat.unread.ping') }}';
            const Ctx = window.AudioContext || window.webkitAudioContext;
            let audioCtx = null;
            const audioEl = document.getElementById('chatRingtone');
            let lastLatestId = 0;
            let enabled = true;
            function initAudio(){ if (!audioCtx && Ctx) audioCtx = new Ctx(); }
            function playTone(){
                try{
                    if (!audioCtx) return;
                    if (audioCtx.state === 'suspended') audioCtx.resume();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.connect(gain); gain.connect(audioCtx.destination);
                    const now = audioCtx.currentTime;
                    osc.frequency.setValueAtTime(880, now);
                    gain.gain.setValueAtTime(0.0001, now);
                    gain.gain.exponentialRampToValueAtTime(0.10, now + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.12);
                    osc.frequency.setValueAtTime(1320, now + 0.14);
                    gain.gain.exponentialRampToValueAtTime(0.10, now + 0.16);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.26);
                    osc.start(now); osc.stop(now + 0.28);
                }catch(e){}
            }
            function playAudioEl(){
                if (!audioEl) return false;
                try {
                    audioEl.currentTime = 0;
                    const p = audioEl.play();
                    if (p && typeof p.catch === 'function') p.catch(()=>{});
                    return true;
                } catch(e) { return false; }
            }
            function playNotify(){
                if (!playAudioEl()) { playTone(); }
            }
            window.__playChatNotify = playNotify;
            function poll(){
                if (!enabled) return;
                fetch(pingUrl, { credentials: 'same-origin' })
                    .then(r => r.ok ? r.json() : null)
                    .then(j => {
                        if (!j || !j.status) return;
                        try {
                            var b = document.getElementById('chatBadge');
                            var p = document.getElementById('chatPulse');
                            if (b) {
                                if (j.count && j.count > 0) { b.style.display = ''; b.textContent = j.count; }
                                else { b.style.display = 'none'; b.textContent = '0'; }
                            }
                            if (p) {
                                if (j.count && j.count > 0) { p.style.display = ''; }
                                else { p.style.display = 'none'; }
                            }
                        } catch(e){}
                        if (j.latest && j.latest.id && j.latest.id !== lastLatestId) {
                            lastLatestId = j.latest.id;
                            playNotify();
                            if (window.Swal && j.latest.sender_name) {
                                const text = `${j.latest.sender_name}: ${j.latest.snippet || ''}`;
                                Swal.fire({ toast:true, position:'bottom-end', timer:3500, showConfirmButton:false, icon:'info', title: text });
                            }
                        }
                    }).catch(()=>{});
            }
            // Admin flagged ping
            function pollFlagged(){
                var badge = document.getElementById('flaggedBadge');
                var badgeSun = document.getElementById('flaggedBadgeSun');
                if (!badge) return;
                fetch('{{ route('chat.flagged.ping') }}', { credentials: 'same-origin' })
                    .then(r => r.ok ? r.json() : null)
                    .then(j => {
                        if (!j || !j.status) return;
                        var show = (j.count && j.count > 0);
                        if (show) { badge.style.display = ''; badge.textContent = j.count; }
                        else { badge.style.display = 'none'; badge.textContent = '0'; }
                        if (badgeSun) {
                            if (show) { badgeSun.style.display = ''; badgeSun.textContent = j.count; }
                            else { badgeSun.style.display = 'none'; badgeSun.textContent = '0'; }
                        }
                        if (j.latest && j.latest.id && window.Swal) {
                            const text = `Flagged message by ${j.latest.sender_name} (${(j.latest.types||[]).join(', ')})`;
                            Swal.fire({ toast:true, position:'bottom-end', timer:3500, showConfirmButton:false, icon:'warning', title: text });
                        }
                    }).catch(()=>{});
            }
            const unlock = () => initAudio();
            window.addEventListener('click', unlock);
            window.addEventListener('keydown', unlock);
            setInterval(poll, 5000);
            setInterval(pollFlagged, 7000);
        })();
    </script>
    <style>
        .chat-pulse-dot {
            width: 8px;
            height: 8px;
            background: #dc3545;
            border-radius: 50%;
            box-shadow: 0 0 0 rgba(220,53,69, 0.7);
            animation: chatPulse 1.5s infinite;
        }
        @keyframes chatPulse {
            0% { box-shadow: 0 0 0 0 rgba(220,53,69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(220,53,69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220,53,69, 0); }
        }
    </style>
    <script>
        // Minimal Echo factory for pages that need realtime and don't have a global bootstrap
        window.EchoFactory = function() {
            try {
                if (!window.Pusher) { return null; }
                // Lazy include pusher-js from CDN if not present
            } catch(e) {}
            try {
                // Build a minimal Echo-like wrapper using Pusher directly (to avoid extra bundles)
                const key = '{{ env('PUSHER_APP_KEY') }}';
                const cluster = '{{ env('PUSHER_APP_CLUSTER') }}';
                if (!key || !cluster) { return null; }
                const pusher = new Pusher(key, { cluster: cluster, forceTLS: true, authEndpoint: '{{ url('/broadcasting/auth') }}', auth: { headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } } });
                return {
                    private: function(channel){
                        const ch = pusher.subscribe('private-' + channel);
                        return {
                            listen: function(event, cb){ ch.bind(event.startsWith('.') ? event.substring(1) : event, cb); return this; }
                        };
                    }
                };
            } catch(e) { return null; }
        };
    </script>
</body>

</html>
