<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ session()->has('dir') ? session()->get('dir') : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseUrl" content="{{ env('APP_URL') }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Dynamic Theme Colors -->
    <script>
        // Set primary color immediately on page load
        const savedPrimaryColor = localStorage.getItem('primaryColor');
        if (savedPrimaryColor) {
            const root = document.documentElement;

            // Convert HEX to RGB for primary-rgb
            const hex = savedPrimaryColor.replace('#', '');
            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);

            // Set CSS variables for primary color
            root.style.setProperty('--bs-primary', savedPrimaryColor);
            root.style.setProperty('--bs-primary-rgb', `${r}, ${g}, ${b}`);
            root.style.setProperty('--bs-primary-bg-subtle', `rgba(${r}, ${g}, ${b}, 0.09)`);
            root.style.setProperty('--bs-primary-border-subtle', `rgba(${r}, ${g}, ${b}, 0.09)`);
            root.style.setProperty('--bs-primary-hover-bg', `rgba(${r}, ${g}, ${b}, 0.75)`);
            root.style.setProperty('--bs-primary-hover-border', `rgba(${r}, ${g}, ${b}, 0.75)`);
            root.style.setProperty('--bs-primary-active-bg', `rgba(${r}, ${g}, ${b}, 0.75)`);
            root.style.setProperty('--bs-primary-active-border', `rgba(${r}, ${g}, ${b}, 0.75)`);
        }
    </script>

    @include('partials._head') <!-- Your other head includes like CSS files -->

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
                        if (j.latest && j.latest.id && j.latest.id !== lastLatestId) {
                            lastLatestId = j.latest.id;
                            playNotify();
                        }
                    }).catch(()=>{});
            }
            const unlock = () => initAudio();
            window.addEventListener('click', unlock);
            window.addEventListener('keydown', unlock);
            setInterval(poll, 5000);
        })();
    </script>
</body>

</html>
