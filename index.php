<?php // index.php - MSpot Local Music Streamer ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MSpot • Local Music</title>
    <link rel="icon" href="data:,">
    <script src="assets/ocean.js" defer></script>
    <script src="assets/alpine.min.js" defer></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0f0c29;
            --bg-gradient: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            --surface: rgba(255, 255, 255, 0.05);
            --surface-hover: rgba(255, 255, 255, 0.1);
            --surface2: rgba(255, 255, 255, 0.08);
            --border: rgba(255, 255, 255, 0.1);
            --accent: #ff007f;
            --accent2: #7928ca;
            --accent-gradient: linear-gradient(135deg, #ff007f, #7928ca);
            --text: #ffffff;
            --text-secondary: #e0e0e0;
            --muted: #a0a0b0;
            --sidebar-w: 300px;
            --player-h: 100px;
            --glass-blur: blur(20px);
            --font-family: 'Outfit', 'Inter', system-ui, -apple-system, sans-serif;
        }

        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        html, body {
            height: 100%;
            overflow: hidden;
            background: var(--bg);
            background: var(--bg-gradient);
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            color: var(--text);
            background-size: 200% 200%;
            animation: gradientBG 15s ease infinite;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        #app-container {
            display: grid;
            grid-template-rows: 72px 1fr var(--player-h);
            grid-template-columns: var(--sidebar-w) 1fr;
            grid-template-areas:
                "header header"
                "sidebar main"
                "player player";
            height: 100%;
            height: 100dvh;
            width: 100%;
            overflow: hidden;
            background: radial-gradient(circle at 15% 50%, rgba(121, 40, 202, 0.15), transparent 50%),
                        radial-gradient(circle at 85% 30%, rgba(255, 0, 127, 0.15), transparent 50%);
        }

        /* ── HEADER ───────────────────────────────────── */
        .top-header {
            grid-area: header;
            background: rgba(15, 12, 41, 0.6);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 32px;
            gap: 24px;
            z-index: 100;
        }

        .top-header .logo {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            white-space: nowrap;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .top-header .logo span {
            color: inherit;
        }

        .top-header .search-input {
            flex: 1;
            max-width: 400px;
            background: var(--surface);
            border: 1px solid var(--border);
            outline: none;
            color: var(--text);
            font-family: inherit;
            font-size: 0.9rem;
            padding: 10px 18px;
            border-radius: 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .top-header .search-input:focus {
            background: var(--surface-hover);
            border-color: rgba(255,255,255,0.3);
            box-shadow: 0 4px 20px rgba(255, 0, 127, 0.15);
        }

        .top-header .search-input::placeholder { color: var(--muted); }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 16px;
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 500;
            background: var(--surface);
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid var(--border);
        }

        /* ── SIDEBAR ─────────────────────────────────────────────── */
        .sidebar {
            grid-area: sidebar;
            background: rgba(15, 12, 41, 0.4);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-right: 1px solid var(--border);
        }

        .sidebar-header {
            padding: 24px 24px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header span {
            font-size: 0.75rem !important;
            color: var(--text) !important;
            opacity: 0.7;
        }

        .sidebar-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 0 12px;
        }

        .track-list {
            flex: 1;
            overflow-y: auto;
            list-style: none;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
            padding-bottom: 20px;
        }

        .track-list::-webkit-scrollbar { width: 6px; }
        .track-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 6px; }

        .track-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 12px;
            margin-bottom: 4px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            border: 1px solid transparent;
        }

        .track-item:hover { 
            background: var(--surface-hover); 
            transform: translateX(4px);
            border-color: rgba(255,255,255,0.05);
        }

        .track-item.active {
            background: linear-gradient(90deg, rgba(255, 0, 127, 0.1), rgba(121, 40, 202, 0.1));
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .track-item.active .track-name { 
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 600;
        }

        .track-num {
            width: 24px;
            font-size: 0.8rem;
            color: var(--muted);
            text-align: center;
            flex-shrink: 0;
            font-weight: 500;
        }

        .track-item.active .track-num { color: var(--accent); }

        .track-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        
        .track-item:hover .thumb {
            transform: scale(1.1);
        }

        .track-info { overflow: hidden; min-width: 0; }

        .track-name {
            font-size: 0.9rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-secondary);
            transition: color 0.2s;
        }
        
        .track-item:hover .track-name {
            color: var(--text);
        }

        .track-meta {
            font-size: 0.72rem;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ── MAIN AREA ───────────────────────────────────────────── */
        .main {
            grid-area: main;
            display: grid;
            grid-template-columns: 1fr 320px;
            grid-template-rows: auto 1fr;
            grid-template-areas:
                "topbar topbar"
                "hero   recent";
            overflow: hidden;
            background: transparent;
            position: relative;
        }

        .main-bg {
            position: absolute;
            inset: -40px; /* extended to hide blurry edges */
            background-size: cover;
            background-position: center;
            filter: blur(25px) brightness(0.6) saturate(1.2);
            z-index: 0;
            transition: background-image 0.6s ease;
        }

        .main-bg-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 12, 41, 0.7); /* dark transparent overlay */
            z-index: 1;
        }

        .topbar { 
            grid-area: topbar; 
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 40px;
            z-index: 10;
        }

        .topbar-title {
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: var(--text);
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .main-hero {
            grid-area: hero;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: stretch;
            overflow: hidden;
            position: relative;
            z-index: 10;
            margin: 0 20px 20px 40px;
            border-radius: 24px;
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--border);
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }

        .main-recent {
            grid-area: recent;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            z-index: 10;
            padding: 24px;
            margin: 0 40px 20px 0;
            border-radius: 24px;
            background: rgba(15, 12, 41, 0.4);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
        }

        .recent-header {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0 0 16px 0;
        }

        .recent-list {
            flex: 1;
            overflow-y: auto;
            list-style: none;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
            padding-right: 8px;
        }

        .recent-list::-webkit-scrollbar { width: 4px; }
        .recent-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

        .recent-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            cursor: pointer;
            border-radius: 12px;
            transition: all 0.25s ease;
            margin-bottom: 8px;
            border: 1px solid transparent;
        }

        .recent-item:hover { 
            background: var(--surface-hover);
            border-color: rgba(255,255,255,0.05);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .recent-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--surface2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: var(--muted);
            flex-shrink: 0;
            font-weight: 600;
        }

        .recent-art {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            background: var(--surface);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .recent-name {
            font-size: 0.85rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-secondary);
            transition: color 0.2s;
        }
        
        .recent-item:hover .recent-name {
            color: var(--text);
        }

        .recent-empty {
            padding: 16px;
            color: var(--muted);
            font-size: 0.85rem;
            text-align: center;
        }

        .now-playing-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
            height: 100%;
            flex: 1;
            border-radius: 24px;
        }

        /* Blurry album art bg */
        .hero-blur-bg {
            position: absolute;
            inset: -40px;
            background-size: cover;
            background-position: center;
            filter: blur(50px) brightness(0.4) saturate(1.8);
            transform: scale(1.1);
            transition: background-image 0.8s ease, transform 10s ease;
            z-index: 0;
            opacity: 0.8;
        }
        
        .now-playing-hero:hover .hero-blur-bg {
            transform: scale(1.15);
        }

        /* Content sits above blur */
        .hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            width: 100%;
            padding: 32px;
        }

        .hero-art {
            width: 280px;
            height: 280px;
            border-radius: 16px;
            object-fit: cover;
            box-shadow: 0 30px 60px rgba(0,0,0,0.6), 0 0 40px rgba(255,0,127,0.2);
            margin-bottom: 24px;
            transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .art-wrap.playing .hero-art {
            box-shadow: 0 40px 80px rgba(0,0,0,0.8), 0 0 60px rgba(121,40,202,0.4);
            transform: scale(1.05);
        }

        .hero-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 4px;
            max-width: 500px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
            background: linear-gradient(to bottom, #ffffff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ── MARQUEE UTILITY ─────────────────────────────────────── */
        .marquee {
            overflow: hidden;
            white-space: nowrap;
            width: 100%;
            text-align: inherit;
        }

        .marquee span {
            display: inline-block;
            min-width: 100%;
            text-align: inherit;
        }

        .marquee.playing.has-marquee span {
            animation: marquee-bounce 12s ease-in-out infinite alternate;
        }

        @keyframes marquee-bounce {
            0%, 15% { transform: translateX(0); }
            85%, 100% { transform: translateX(var(--scroll-amount, 0px)); }
        }

        .hero-sub {
            font-size: 1rem;
            color: var(--muted);
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* ── PLAYER BAR ──────────────────────────────────────────── */
        .player-bar {
            grid-area: player;
            background: rgba(15, 12, 41, 0.7);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-top: 1px solid rgba(255,255,255,0.05);
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            align-items: center;
            padding: 0 32px;
            gap: 24px;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.3);
            z-index: 100;
        }

        .player-left {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .mini-art {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
            background: var(--surface2);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .mini-info { min-width: 0; }

        .mini-title {
            font-size: 0.95rem;
            font-weight: 600;
            max-width: 200px;
            color: var(--text);
            margin-bottom: 4px;
        }

        .mini-sub {
            font-size: 0.75rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .player-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .controls {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .ctrl-btn {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 1.2rem;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
            line-height: 1;
        }

        .ctrl-btn:hover { 
            color: var(--text); 
            background: rgba(255,255,255,0.1);
            transform: scale(1.05); 
        }

        .ctrl-btn--active {
            color: var(--accent) !important;
            position: relative;
        }
        
        .ctrl-btn--active::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent);
        }

        .play-btn {
            background: var(--text);
            border: none;
            color: #000;
            cursor: pointer;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 16px rgba(255,255,255,0.2);
        }

        .play-btn:hover { 
            transform: scale(1.1); 
            background: #ffffff;
            box-shadow: 0 6px 20px rgba(255,255,255,0.3);
        }

        .progress-row {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            max-width: 600px;
        }

        .time-label {
            font-size: 0.75rem;
            color: var(--muted);
            width: 40px;
            text-align: center;
            flex-shrink: 0;
            font-variant-numeric: tabular-nums;
        }

        .seek-bar {
            flex: 1;
            -webkit-appearance: none;
            appearance: none;
            height: 6px;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
            outline: none;
            cursor: pointer;
            transition: height 0.15s, background 0.15s;
            overflow: hidden;
        }

        .seek-bar:hover { 
            height: 8px; 
            background: rgba(255,255,255,0.15);
        }

        .seek-bar::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 0px;
            height: 0px;
            box-shadow: -400px 0 0 400px var(--text);
            cursor: pointer;
        }
        
        .seek-bar:hover::-webkit-slider-thumb {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--text);
            box-shadow: -400px 0 0 394px var(--text);
        }

        .seek-bar::-moz-range-thumb {
            width: 0;
            height: 0;
            border: none;
            box-shadow: -400px 0 0 400px var(--text);
        }

        .player-right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        .vol-label {
            font-size: 1rem;
            color: var(--muted);
        }

        .vol-bar {
            -webkit-appearance: none;
            appearance: none;
            height: 6px;
            width: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
            outline: none;
            cursor: pointer;
            overflow: hidden;
        }

        .vol-bar::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 0;
            height: 0;
            box-shadow: -100px 0 0 100px var(--text);
        }
        
        .vol-bar:hover::-webkit-slider-thumb {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--text);
            box-shadow: -100px 0 0 94px var(--text);
        }

        .vol-bar::-moz-range-thumb {
            width: 0;
            height: 0;
            border: none;
            box-shadow: -100px 0 0 100px var(--text);
        }

        /* Equaliser bars animation */
        @keyframes eq1 { 0%,100%{height:4px} 50%{height:16px} }
        @keyframes eq2 { 0%,100%{height:12px} 40%{height:4px} }
        @keyframes eq3 { 0%,100%{height:8px} 60%{height:16px} }

        .eq {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 16px;
        }

        .eq span {
            width: 4px;
            background: var(--accent);
            border-radius: 2px;
            box-shadow: 0 0 6px var(--accent);
        }

        .eq span:nth-child(1) { animation: eq1 0.9s ease-in-out infinite; }
        .eq span:nth-child(2) { animation: eq2 0.7s ease-in-out infinite; }
        .eq span:nth-child(3) { animation: eq3 1.1s ease-in-out infinite; }

        .eq.paused span { animation-play-state: paused; height: 4px; }

        /* ─ Waveform animation at top of hero ─ */
        .audio-wave {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 4px;
            padding: 0 20px;
            z-index: 2;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .audio-wave.active { opacity: 0.8; }

        .audio-wave span {
            flex: 1;
            max-width: 8px;
            border-radius: 4px 4px 0 0;
            background: var(--accent-gradient);
            animation: none;
            height: 6px;
            transition: height 0.2s;
            box-shadow: 0 0 10px rgba(255, 0, 127, 0.4);
        }

        .audio-wave.active span:nth-child(1)  { animation: wv1 0.7s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(2)  { animation: wv2 0.5s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(3)  { animation: wv3 0.9s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(4)  { animation: wv4 0.6s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(5)  { animation: wv5 0.8s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(6)  { animation: wv1 0.55s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(7)  { animation: wv3 0.75s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(8)  { animation: wv2 0.65s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(9)  { animation: wv5 0.85s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(10) { animation: wv4 0.6s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(11) { animation: wv1 0.7s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(12) { animation: wv2 0.5s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(13) { animation: wv3 0.9s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(14) { animation: wv4 0.6s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(15) { animation: wv5 0.8s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(16) { animation: wv1 0.55s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(17) { animation: wv3 0.75s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(18) { animation: wv2 0.65s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(19) { animation: wv5 0.85s ease-in-out infinite alternate; }
        .audio-wave.active span:nth-child(20) { animation: wv4 0.6s ease-in-out infinite alternate; }

        @keyframes wv1 { from{height:6px}  to{height:45px} }
        @keyframes wv2 { from{height:12px}  to{height:35px} }
        @keyframes wv3 { from{height:18px} to{height:55px} }
        @keyframes wv4 { from{height:10px}  to{height:30px} }
        @keyframes wv5 { from{height:15px} to{height:48px} }

        /* Empty state */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            gap: 16px;
            color: var(--muted);
            background: rgba(255,255,255,0.02);
            border-radius: 24px;
        }

        .empty-icon { 
            font-size: 5rem; 
            opacity: 0.5; 
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5));
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        /* ── MOBILE RESPONSIVENESS ────────────────────────────────── */
        .mobile-tabs {
            grid-area: tabs;
            display: none;
            background: rgba(15, 12, 41, 0.9);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255,255,255,0.1);
            justify-content: space-around;
            align-items: center;
            z-index: 100;
        }

        .mobile-tab-btn {
            flex: 1;
            background: none;
            border: none;
            color: var(--muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 8px 0;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            outline: none;
            transition: all 0.2s ease;
        }

        .mobile-tab-btn:active {
            transform: scale(0.95);
        }

        .mobile-tab-btn.active {
            color: #ffffff;
            text-shadow: 0 0 10px rgba(255,0,127,0.5);
        }

        .mobile-tab-btn.active .mobile-tab-icon {
            transform: translateY(-2px);
            color: var(--accent);
        }

        .mobile-tab-icon {
            font-size: 1.2rem;
            transition: all 0.2s ease;
        }

        #app-container:not(.has-player) .player-bar {
            display: none !important;
        }

        /* Fade-in transitions between mobile tabs */
        .sidebar, .main {
            animation: tabFadeIn 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes tabFadeIn {
            from { opacity: 0; transform: translateY(10px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @media (max-width: 768px) {
            #app-container {
                grid-template-columns: minmax(0, 1fr) !important;
                grid-template-rows: 64px 1fr 60px;
                grid-template-areas:
                    "header"
                    "main-view"
                    "tabs";
            }

            #app-container.has-player {
                grid-template-columns: minmax(0, 1fr) !important;
                grid-template-rows: 64px 1fr 70px 60px;
                grid-template-areas:
                    "header"
                    "main-view"
                    "player"
                    "tabs";
            }

            .mobile-tabs {
                display: flex;
            }

            .sidebar, .main {
                grid-area: main-view;
                width: 100%;
                height: 100%;
                display: none !important;
                border-right: none;
                background: transparent;
            }

            #app-container.tab-library .sidebar {
                display: flex !important;
            }

            #app-container.tab-player .main,
            #app-container.tab-recent .main {
                display: grid !important;
            }

            .main {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "topbar"
                    "content";
            }

            .main-hero, .main-recent {
                grid-area: content;
                display: none !important;
                border-left: none;
                margin: 0 16px 16px 16px;
            }

            #app-container.tab-player .main-hero {
                display: flex !important;
            }

            .hero-title {
                max-width: 100%;
                font-size: 1.5rem;
            }

            #app-container.tab-recent .main-recent {
                display: flex !important;
            }

            .top-header {
                padding: 0 16px;
                gap: 12px;
            }

            .top-header .logo {
                font-size: 1.25rem;
            }

            .header-right {
                display: none !important;
            }

            .top-header .search-input {
                max-width: none;
                font-size: 0.85rem;
                padding: 8px 14px;
            }

            #app-container:not(.tab-library) .top-header .search-input {
                display: none !important;
            }

            .player-bar {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) 120px !important;
                align-items: center;
                padding: 0 16px;
                height: 70px;
                background: rgba(15, 12, 41, 0.85);
                border-top: 1px solid rgba(255,255,255,0.1);
                position: relative;
                gap: 12px;
                box-shadow: 0 -5px 20px rgba(0,0,0,0.4);
                border-radius: 20px 20px 0 0;
            }

            .player-left {
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 0;
            }

            .mini-art {
                width: 48px;
                height: 48px;
                border-radius: 8px;
            }

            .mini-info {
                flex: 1;
                min-width: 0;
                overflow: hidden;
            }

            .mini-title {
                font-size: 0.9rem;
                max-width: 100%;
            }

            .player-center {
                display: flex !important;
                justify-content: flex-end;
                align-items: center;
                width: 100%;
                margin: 0;
            }

            .player-center .controls {
                gap: 16px;
                margin-bottom: 0;
            }

            .player-center .controls .ctrl-btn:first-child,
            .player-center .controls .ctrl-btn:last-child,
            .player-right {
                display: none !important;
            }

            .player-center .progress-row {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                padding: 0;
            }

            .player-center .progress-row .time-label {
                display: none !important;
            }

            .player-center .progress-row .seek-bar {
                width: 100%;
                height: 3px;
                border-radius: 0;
                background: rgba(255,255,255,0.1);
                margin: 0;
            }

            .player-center .progress-row .seek-bar::-webkit-slider-thumb,
            .player-center .progress-row .seek-bar::-moz-range-thumb {
                width: 0;
                height: 0;
                border: 0;
            }
        }

        @media (max-width: 480px) {
            .hero-art {
                width: 200px;
                height: 200px;
            }
            .hero-title {
                font-size: 1.3rem;
                max-width: 260px;
            }
            .audio-wave {
                height: 40px;
            }
        }

    </style>
</head>
<body>
<div id="app-container" x-data="mspot()" x-init="boot()"
     :class="{
         'has-player': current,
         'tab-library': currentTab === 'library',
         'tab-player': currentTab === 'player',
         'tab-recent': currentTab === 'recent'
     }">

    <!-- FULL-WIDTH HEADER -->
    <header class="top-header">
        <span class="logo">🎧 M<span style="color:var(--accent)">Spot</span></span>
        <input class="search-input" type="text" placeholder="Search songs…" x-model="query" @input="filter()" />
        <div class="header-right">
            <span x-text="tracks.length + ' tracks'"></span>
        </div>
    </header>

    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <span style="font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)">Library</span>
        </div>



        <div class="sidebar-section">
            <ul class="track-list">
                <template x-for="(t, i) in filtered" :key="t.url">
                    <li class="track-item" :class="{active: current && current.url === t.url}" @click="play(i)">
                        <div class="track-num" x-show="!(current && current.url === t.url && isPlaying)">
                            <span x-text="i + 1"></span>
                        </div>
                        <div class="eq" :class="{paused: !isPlaying}" x-show="current && current.url === t.url && isPlaying">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="track-icon">
                            <img class="thumb" src="assets/album_placeholder.png"
                                 :data-art="t.artUrl"
                                 x-ref="thumb"
                                 alt="" loading="lazy" />
                        </div>
                        <div class="track-info">
                            <div class="track-name" x-text="t.name"></div>
                        </div>
                    </li>
                </template>
                <template x-if="filtered.length === 0">
                    <li style="padding:16px 12px;color:#666;font-size:.82rem">No results…</li>
                </template>
            </ul>
        </div>
    </nav>

    <!-- MAIN -->
    <main class="main">
        <div class="main-bg" :style="current ? 'background-image:url(' + currentArt + ')' : ''"></div>
        <div class="main-bg-overlay"></div>
        
        <div class="topbar">
            <span class="topbar-title">Now Playing</span>
        </div>

        <!-- Hero column -->
        <div class="main-hero" style="position: relative;">
            <canvas id="ocean-canvas" style="width: 100%; height: 100%; display: block; border-radius: 24px;" x-show="current"></canvas>
            
            <!-- No track yet -->
            <template x-if="!current">
                <div class="empty-state" style="position: absolute; inset: 0; background: rgba(15,12,41,0.8); backdrop-filter: blur(20px); z-index: 10; border-radius: 24px;">
                    <div class="empty-icon">🎵</div>
                    <div style="font-size:1rem;font-weight:600">Pick something to play</div>
                    <div style="font-size:.85rem">Select a track from the sidebar</div>
                </div>
            </template>
        </div>

        <!-- Recently Played right panel -->
        <aside class="main-recent">
            <div class="recent-header">Recently Played</div>
            <ul class="recent-list">
                <template x-if="recent.length === 0">
                    <li class="recent-empty">Nothing played yet — pick a track!</li>
                </template>
                <template x-for="(r, i) in recent" :key="r">
                    <li class="recent-item" @click="playByName(r)">
                        <div class="recent-num" x-text="i + 1"></div>
                        <img class="recent-art" src="assets/album_placeholder.png" alt=""
                             x-init="loadRecentArt($el, r)" />
                        <span class="recent-name" x-text="r"></span>
                    </li>
                </template>
            </ul>
        </aside>
    </main>

    <!-- MOBILE NAVIGATION TABS -->
    <nav class="mobile-tabs">
        <button class="mobile-tab-btn" :class="{active: currentTab === 'library'}" @click="currentTab = 'library'">
            <span class="mobile-tab-icon">📚</span>
            <span>Library</span>
        </button>
        <button class="mobile-tab-btn" :class="{active: currentTab === 'player'}" @click="currentTab = 'player'">
            <span class="mobile-tab-icon">🎵</span>
            <span>Player</span>
        </button>
        <button class="mobile-tab-btn" :class="{active: currentTab === 'recent'}" @click="currentTab = 'recent'">
            <span class="mobile-tab-icon">🕐</span>
            <span>Recent</span>
        </button>
    </nav>

    <!-- PLAYER BAR -->
    <footer class="player-bar">
        <!-- Left: mini track info -->
        <div class="player-left">
            <img class="mini-art" :src="currentArt" alt="" x-show="current"
                 @error="currentArt = 'assets/album_placeholder.png'" />
            <div class="mini-info" x-show="current">
                <div class="mini-title marquee" :class="{playing: isPlaying}">
                    <span x-text="current ? current.name : ''"></span>
                </div>
                <div class="mini-sub">Local Library</div>
            </div>
        </div>

        <!-- Center: controls + seek -->
        <div class="player-center">
            <div class="controls">
                <button class="ctrl-btn" @click="toggleShuffle()" title="Shuffle"
                        :class="{'ctrl-btn--active': shuffle}">&#x1F500;</button>
                <button class="ctrl-btn" @click="prev()" title="Previous">&#9198;</button>
                <button class="play-btn" @click="togglePlay()" :title="isPlaying ? 'Pause' : 'Play'">
                    <span x-text="isPlaying ? '⏸' : '▶'"></span>
                </button>
                <button class="ctrl-btn" @click="next()" title="Next">&#9197;</button>
                <button class="ctrl-btn" @click="cycleLoop()" title="Loop mode"
                        :class="{'ctrl-btn--active': loopMode !== 'none'}"
                        :title="loopMode === 'one' ? 'Loop: One' : loopMode === 'all' ? 'Loop: All' : 'Loop: Off'">
                    <span x-text="loopMode === 'one' ? '🔂' : '🔁'"></span>
                </button>
            </div>
            <div class="progress-row">
                <span class="time-label" x-text="elapsed"></span>
                <input type="range" class="seek-bar" min="0" max="100" step="0.1"
                       x-ref="seekBar" @input="seek($event)" />
                <span class="time-label" x-text="duration"></span>
            </div>
        </div>

        <!-- Right: volume -->
        <div class="player-right">
            <span class="vol-label">🔉</span>
            <input type="range" class="vol-bar" min="0" max="1" step="0.01" value="1"
                   @input="setVolume($event)" />
        </div>
    </footer>

    <!-- Hidden audio element -->
    <audio id="audio-el" x-ref="audio" preload="metadata"></audio>
</div>

<script>
function mspot() {
    return {
        tracks: [], filtered: [], query: '',
        current: null, currentArt: 'assets/album_placeholder.png', isPlaying: false,
        loopMode: 'none', // 'none' | 'one' | 'all'
        shuffle: false,
        currentTab: 'library', // active view on mobile: 'library' | 'player' | 'recent'
        recent: JSON.parse(localStorage.getItem('mspot_recent') || '[]'),
        playCounts: {}, // loaded from server (stats.php)
        elapsed: '0:00', duration: '0:00',
        _artObserver: null,

        boot() {
            console.log("MSpot boot() started");
            if (typeof OceanScene !== 'undefined') OceanScene.init();
            const audio = this.$refs.audio;

            // IntersectionObserver — lazy load cover art only when row scrolls into view
            this._artObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    const img = entry.target;
                    const artUrl = img.dataset.art;
                    if (!artUrl || img.dataset.loaded) return;
                    img.dataset.loaded = '1';
                    // Probe: fetch with HEAD first is too slow; just set src and let
                    // the browser deal with 204 (which renders nothing, placeholder stays)
                    const probe = new Image();
                    probe.onload = () => { img.src = artUrl; };
                    probe.onerror = () => { /* keep placeholder */ };
                    probe.src = artUrl;
                    this._artObserver.unobserve(img);
                });
            }, { rootMargin: '100px' }); // pre-load 100px before entering view

            // After Alpine renders the list, attach observer to all thumbnails
            this.$watch('filtered', () => {
                this.$nextTick(() => this._observeAll());
            });

            this.$watch('current', () => this.updateMarquees());
            this.$watch('currentTab', () => this.updateMarquees());
            window.addEventListener('resize', () => this.updateMarquees());

            // Load tracks + play counts in parallel, then sort
            console.log("Fetching tracks from api.php...");
            Promise.all([
                fetch('api.php').then(r => {
                    console.log("api.php response received:", r.status);
                    return r.json();
                }),
                fetch('stats.php').then(r => {
                    console.log("stats.php response received:", r.status);
                    return r.json();
                }).catch((e) => {
                    console.warn("stats.php fetch failed, using default counts", e);
                    return { counts: {} };
                })
            ]).then(([trackData, statsData]) => {
                console.log("Successfully parsed tracks and stats:", trackData, statsData);
                this.tracks = trackData.tracks || [];
                this.playCounts = statsData.counts || {};
                this.sortByPlayCount();
                this.$nextTick(() => {
                    this._observeAll();
                    this.updateMarquees();
                });
            }).catch(err => {
                console.error("Promise.all failed:", err);
            });

            audio.addEventListener('timeupdate', () => {
                if (!audio.duration) return;
                const p = (audio.currentTime / audio.duration) * 100;
                this.$refs.seekBar.value = p;
                this.elapsed = this.fmt(audio.currentTime);
                this.duration = this.fmt(audio.duration);
            });

            audio.addEventListener('ended', () => {
                if (this.loopMode === 'one') {
                    audio.currentTime = 0;
                    audio.play();
                } else {
                    this.next();
                }
            });
            audio.addEventListener('pause', () => this.isPlaying = false);
            audio.addEventListener('play',  () => this.isPlaying = true);
        },

        _observeAll() {
            document.querySelectorAll('.thumb[data-art]').forEach(img => {
                if (!img.dataset.loaded) this._artObserver.observe(img);
            });
        },

        sortByPlayCount() {
            // Spread into a new array — Alpine.js needs a new reference to trigger re-render
            this.tracks = [...this.tracks].sort((a, b) =>
                (this.playCounts[b.url] || 0) - (this.playCounts[a.url] || 0)
            );
            this.filter(); // re-apply search filter with the new order
        },

        filter() {
            const q = this.query.toLowerCase();
            this.filtered = q
                ? this.tracks.filter(t => t.name.toLowerCase().includes(q))
                : [...this.tracks];
        },

        play(idx) {
            const t = this.filtered[idx];
            if (!t) return;
            this.current = t;
            this.currentArt = 'assets/album_placeholder.png';
            if (t.artUrl) {
                const probe = new Image();
                probe.onload = () => { this.currentArt = t.artUrl; };
                probe.onerror = () => {};
                probe.src = t.artUrl;
            }
            const audio = this.$refs.audio;
            audio.src = t.url;
            audio.play();
            // Automatically switch to the Player tab on mobile when a track begins
            this.currentTab = 'player';
            // Increment play count in SQLite on the server
            fetch('stats.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: t.url, name: t.name })
            })
            .then(r => r.json())
            .then(d => {
                if (d.count !== undefined) {
                    this.playCounts[t.url] = d.count;
                    this.sortByPlayCount();
                }
            })
            .catch(() => {});
            this.addRecent(t.name);
        },

        playByName(name) {
            const idx = this.filtered.findIndex(t => t.name === name);
            if (idx >= 0) { this.play(idx); return; }
            const gi = this.tracks.findIndex(t => t.name === name);
            if (gi >= 0) {
                const t = this.tracks[gi];
                this.current = t;
                this.currentArt = 'assets/album_placeholder.png';
                if (t.artUrl) {
                    const probe = new Image();
                    probe.onload = () => { this.currentArt = t.artUrl; };
                    probe.src = t.artUrl;
                }
                this.$refs.audio.src = t.url;
                this.$refs.audio.play();
                this.addRecent(name);
            }
        },

        togglePlay() {
            if (!this.current) return;
            const audio = this.$refs.audio;
            if (this.isPlaying) audio.pause(); else audio.play();
        },

        toggleShuffle() { this.shuffle = !this.shuffle; },

        cycleLoop() {
            const modes = ['none', 'all', 'one'];
            this.loopMode = modes[(modes.indexOf(this.loopMode) + 1) % modes.length];
            this.$refs.audio.loop = (this.loopMode === 'one');
        },

        prev() {
            if (this.shuffle) {
                this.play(Math.floor(Math.random() * this.filtered.length));
                return;
            }
            const i = this.filtered.findIndex(t => this.current && t.url === this.current.url);
            if (i > 0) this.play(i - 1);
            else if (this.filtered.length) this.play(this.filtered.length - 1);
        },

        next() {
            if (!this.filtered.length) return;
            if (this.shuffle) {
                this.play(Math.floor(Math.random() * this.filtered.length));
                return;
            }
            const i = this.filtered.findIndex(t => this.current && t.url === this.current.url);
            if (i >= 0 && i < this.filtered.length - 1) {
                this.play(i + 1);
            } else if (this.loopMode === 'all') {
                this.play(0); // wrap back to start
            } else if (i < this.filtered.length - 1) {
                this.play(i + 1);
            }
            // loopMode 'none' + last track → stop (do nothing)
        },

        seek(e) {
            const audio = this.$refs.audio;
            if (audio.duration) audio.currentTime = (e.target.value / 100) * audio.duration;
        },

        setVolume(e) { this.$refs.audio.volume = e.target.value; },

        addRecent(name) {
            this.recent = this.recent.filter(n => n !== name);
            this.recent.unshift(name);
            if (this.recent.length > 15) this.recent.pop();
            localStorage.setItem('mspot_recent', JSON.stringify(this.recent));
        },

        loadRecentArt(el, name) {
            // Find the track in the full list by name and load its art
            const track = this.tracks.find(t => t.name === name);
            if (!track || !track.artUrl) return;
            const probe = new Image();
            probe.onload = () => { el.src = track.artUrl; };
            probe.src = track.artUrl;
        },

        updateMarquees() {
            const run = () => {
                document.querySelectorAll('.marquee').forEach(el => {
                    const span = el.querySelector('span');
                    if (!span) return;
                    
                    // Temporarily remove classes to measure static layout
                    el.classList.remove('has-marquee');
                    void span.offsetHeight; // force reflow
                    
                    const overflow = span.scrollWidth - el.clientWidth;
                    if (overflow > 0) {
                        el.style.setProperty('--scroll-amount', `-${overflow}px`);
                        el.classList.add('has-marquee');
                    } else {
                        el.style.setProperty('--scroll-amount', '0px');
                    }
                });
            };
            this.$nextTick(run);
            setTimeout(run, 150);
        },

        fmt(s) {
            if (!s || isNaN(s)) return '0:00';
            const m = Math.floor(s / 60), sec = Math.floor(s % 60);
            return `${m}:${sec.toString().padStart(2,'0')}`;
        }
    };
}
</script>
</body>
</html>
