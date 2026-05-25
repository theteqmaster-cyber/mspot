<?php // index.php - MSpot Local Music Streamer ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MSpot • Local Music</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.13.5/dist/cdn.min.js" defer></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0d0d0d;
            --surface:   #181818;
            --surface2:  #222222;
            --border:    #2a2a2a;
            --accent:    #1ed760;
            --accent2:   #1ab44f;
            --text:      #ffffff;
            --muted:     #a7a7a7;
            --sidebar-w: 280px;
            --player-h:  90px;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            background: var(--bg);
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
        }

        #app-container {
            display: grid;
            grid-template-rows: 56px 1fr var(--player-h);
            grid-template-columns: var(--sidebar-w) 1fr;
            grid-template-areas:
                "header  header"
                "sidebar main"
                "player  player";
            height: 100%;
            height: 100dvh;
            width: 100%;
            overflow: hidden;
        }

        /* ── FULL-WIDTH HEADER ───────────────────────────────────── */
        .top-header {
            grid-area: header;
            background: rgba(13,13,13,0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .top-header .logo {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            white-space: nowrap;
        }

        .top-header .search-input {
            flex: 1;
            max-width: 340px;
            background: var(--surface2);
            border: 1px solid var(--border);
            outline: none;
            color: var(--text);
            font-family: inherit;
            font-size: 0.83rem;
            padding: 7px 14px;
            border-radius: 20px;
            transition: border-color 0.2s, background 0.2s;
        }

        .top-header .search-input:focus {
            background: #2a2a2a;
            border-color: #555;
        }

        .top-header .search-input::placeholder { color: var(--muted); }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--muted);
            font-size: 0.8rem;
        }

        /* ── SIDEBAR ─────────────────────────────────────────────── */
        .sidebar {
            grid-area: sidebar;
            background: #000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-right: 1px solid var(--border);
        }

        .sidebar-header {
            padding: 14px 20px 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .logo span { color: var(--accent); }

        .sidebar-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 0 8px;
        }

        .section-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 16px 12px 8px;
        }

        .search-wrap {
            padding: 0 12px 12px;
        }

        .search-input {
            width: 100%;
            background: var(--surface2);
            border: none;
            outline: none;
            color: var(--text);
            font-family: inherit;
            font-size: 0.82rem;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .search-input::placeholder { color: var(--muted); }
        .search-input:focus { background: #333; }

        .track-list {
            flex: 1;
            overflow-y: auto;
            list-style: none;
            scrollbar-width: thin;
            scrollbar-color: #333 transparent;
        }

        .track-list::-webkit-scrollbar { width: 4px; }
        .track-list::-webkit-scrollbar-thumb { background: #444; border-radius: 4px; }

        .track-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.15s;
            user-select: none;
        }

        .track-item:hover { background: var(--surface2); }

        .track-item.active {
            background: var(--surface2);
        }

        .track-item.active .track-name { color: var(--accent); }

        .track-num {
            width: 18px;
            font-size: 0.75rem;
            color: var(--muted);
            text-align: center;
            flex-shrink: 0;
        }

        .track-item.active .track-num { color: var(--accent); }

        .track-icon {
            width: 32px;
            height: 32px;
            background: var(--surface);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
            transition: opacity 0.3s ease;
        }

        .track-item.active .track-icon { background: #1ed76022; }

        .track-info { overflow: hidden; min-width: 0; }

        .track-name {
            font-size: 0.82rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .track-meta {
            font-size: 0.72rem;
            color: var(--muted);
            margin-top: 2px;
        }

        .sidebar-divider {
            height: 1px;
            background: var(--border);
            margin: 8px 12px;
        }

        /* ── MAIN AREA ───────────────────────────────────────────── */
        .main {
            grid-area: main;
            display: grid;
            grid-template-columns: 1fr 280px;
            grid-template-rows: auto 1fr;
            grid-template-areas:
                "topbar topbar"
                "hero   recent";
            overflow: hidden;
            background: linear-gradient(180deg, #1a1a2e 0%, var(--bg) 300px);
        }

        .topbar { grid-area: topbar; }

        .main-hero {
            grid-area: hero;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: stretch;
            overflow: hidden;
        }

        .main-recent {
            grid-area: recent;
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 16px 0 0;
        }

        .recent-header {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0 16px 10px;
        }

        .recent-list {
            flex: 1;
            overflow-y: auto;
            list-style: none;
            scrollbar-width: thin;
            scrollbar-color: #333 transparent;
        }

        .recent-list::-webkit-scrollbar { width: 4px; }
        .recent-list::-webkit-scrollbar-thumb { background: #444; border-radius: 4px; }

        .recent-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 6px;
            transition: background 0.15s;
        }

        .recent-item:hover { background: var(--surface2); }

        .recent-num {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--surface2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            color: var(--muted);
            flex-shrink: 0;
        }

        .recent-art {
            width: 36px;
            height: 36px;
            border-radius: 4px;
            object-fit: cover;
            flex-shrink: 0;
            background: var(--surface);
        }

        .recent-name {
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text);
        }

        .recent-empty {
            padding: 16px;
            color: var(--muted);
            font-size: 0.8rem;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 28px;
            gap: 16px;
        }

        .topbar-title {
            font-size: 1.05rem;
            font-weight: 600;
            opacity: 0.85;
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
        }

        /* Blurry album art bg */
        .hero-blur-bg {
            position: absolute;
            inset: -40px;
            background-size: cover;
            background-position: center;
            filter: blur(40px) brightness(0.35) saturate(1.6);
            transform: scale(1.1);
            transition: background-image 0.6s ease;
            z-index: 0;
        }

        /* Content sits above blur */
        .hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0;
            width: 100%;
            padding: 24px;
        }

        .hero-art {
            width: 220px;
            height: 220px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 20px 60px rgba(0,0,0,0.7);
            margin-bottom: 28px;
            transition: transform 0.3s;
        }

        .hero-art:hover { transform: scale(1.02); }

        .hero-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 6px;
            max-width: 420px;
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
            font-size: 0.9rem;
            color: var(--muted);
        }

        .no-track-hint {
            font-size: 1rem;
            color: var(--muted);
            margin-top: 12px;
        }

        /* ── PLAYER BAR ──────────────────────────────────────────── */
        .player-bar {
            grid-area: player;
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
        }

        .player-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .mini-art {
            width: 52px;
            height: 52px;
            border-radius: 6px;
            object-fit: cover;
            flex-shrink: 0;
            background: var(--surface2);
        }

        .mini-info { min-width: 0; }

        .mini-title {
            font-size: 0.82rem;
            font-weight: 600;
            max-width: 180px;
        }

        .mini-sub {
            font-size: 0.72rem;
            color: var(--muted);
            margin-top: 2px;
        }

        .player-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .controls {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .ctrl-btn {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 4px;
            border-radius: 50%;
            transition: color 0.15s, transform 0.1s;
            line-height: 1;
        }

        .ctrl-btn:hover { color: var(--text); transform: scale(1.1); }

        .ctrl-btn--active {
            color: var(--accent) !important;
            position: relative;
        }
        /* Dot indicator under active mode button */
        .ctrl-btn--active::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--accent);
        }

        .play-btn {
            background: var(--text);
            border: none;
            color: #000;
            cursor: pointer;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            transition: transform 0.15s, background 0.15s;
        }

        .play-btn:hover { transform: scale(1.07); background: #e8e8e8; }

        .progress-row {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .time-label {
            font-size: 0.68rem;
            color: var(--muted);
            width: 36px;
            text-align: center;
            flex-shrink: 0;
        }

        .seek-bar {
            flex: 1;
            -webkit-appearance: none;
            appearance: none;
            height: 4px;
            background: #555;
            border-radius: 4px;
            outline: none;
            cursor: pointer;
            transition: height 0.1s;
        }

        .seek-bar:hover { height: 6px; }

        .seek-bar::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--text);
            cursor: pointer;
        }

        .seek-bar::-moz-range-thumb {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--text);
            cursor: pointer;
            border: none;
        }

        .player-right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .vol-label {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .vol-bar {
            -webkit-appearance: none;
            appearance: none;
            height: 4px;
            width: 90px;
            background: #555;
            border-radius: 4px;
            outline: none;
            cursor: pointer;
        }

        .vol-bar::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--text);
        }

        .vol-bar::-moz-range-thumb {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--text);
            border: none;
        }

        /* Equaliser bars animation for active track */
        @keyframes eq1 { 0%,100%{height:4px} 50%{height:14px} }
        @keyframes eq2 { 0%,100%{height:10px} 40%{height:4px} }
        @keyframes eq3 { 0%,100%{height:7px} 60%{height:14px} }

        .eq {
            display: flex;
            align-items: flex-end;
            gap: 2px;
            height: 14px;
        }

        .eq span {
            width: 3px;
            background: var(--accent);
            border-radius: 2px;
        }

        .eq span:nth-child(1) { animation: eq1 0.9s ease-in-out infinite; }
        .eq span:nth-child(2) { animation: eq2 0.7s ease-in-out infinite; }
        .eq span:nth-child(3) { animation: eq3 1.1s ease-in-out infinite; }

        .eq.paused span { animation-play-state: paused; }

        /* ─ Waveform animation at top of hero ─ */
        .audio-wave {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 48px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 3px;
            padding: 0 20px;
            z-index: 2;
            opacity: 0;
            transition: opacity 0.4s;
        }

        .audio-wave.active { opacity: 1; }

        .audio-wave span {
            flex: 1;
            max-width: 6px;
            border-radius: 3px 3px 0 0;
            background: linear-gradient(to top, var(--accent), #1ed76055);
            animation: none;
            height: 4px;
            transition: height 0.15s;
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

        @keyframes wv1 { from{height:4px}  to{height:34px} }
        @keyframes wv2 { from{height:8px}  to{height:28px} }
        @keyframes wv3 { from{height:12px} to{height:44px} }
        @keyframes wv4 { from{height:6px}  to{height:22px} }
        @keyframes wv5 { from{height:10px} to{height:38px} }

        /* art-wrap: no border, just shadow lift on playing */
        .art-wrap { position: relative; display: inline-block; }
        .art-wrap.playing .hero-art {
            box-shadow: 0 28px 80px rgba(0,0,0,0.85);
            transform: scale(1.03);
        }

        /* Empty state */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            gap: 12px;
            color: var(--muted);
        }

        .empty-icon { font-size: 4rem; opacity: 0.3; }

        /* Scrollbar for recent */
        /* Scrollbar for recent */
        .recent-section {
            max-height: 160px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #333 transparent;
        }

        /* ── MOBILE RESPONSIVENESS ────────────────────────────────── */
        .mobile-tabs {
            grid-area: tabs;
            display: none;
            background: var(--surface);
            border-top: 1px solid var(--border);
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
            padding: 6px 0;
            font-size: 0.65rem;
            font-weight: 600;
            cursor: pointer;
            outline: none;
            transition: color 0.15s;
        }

        .mobile-tab-btn.active {
            color: var(--accent);
        }

        .mobile-tab-icon {
            font-size: 1.1rem;
        }

        #app-container:not(.has-player) .player-bar {
            display: none !important;
        }

        /* Fade-in transitions between mobile tabs */
        .sidebar, .main {
            animation: tabFadeIn 0.2s ease-out;
        }

        @keyframes tabFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            #app-container {
                grid-template-columns: minmax(0, 1fr) !important;
                grid-template-rows: 56px 1fr 52px;
                grid-template-areas:
                    "header"
                    "main-view"
                    "tabs";
            }

            #app-container.has-player {
                grid-template-columns: minmax(0, 1fr) !important;
                grid-template-rows: 56px 1fr 64px 52px;
                grid-template-areas:
                    "header"
                    "main-view"
                    "player"
                    "tabs";
            }

            .mobile-tabs {
                display: flex;
            }

            /* Route layout elements to standard main-view cell */
            .sidebar, .main {
                grid-area: main-view;
                width: 100%;
                height: 100%;
                display: none !important;
                border-right: none;
            }

            #app-container.tab-library .sidebar {
                display: flex !important;
            }

            #app-container.tab-player .main,
            #app-container.tab-recent .main {
                display: grid !important;
            }

            /* Restructure main section content display */
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
            }

            #app-container.tab-player .main-hero {
                display: flex !important;
            }

            .hero-title {
                max-width: 100%;
            }

            #app-container.tab-recent .main-recent {
                display: flex !important;
                padding-top: 8px;
            }

            /* Header styles for mobile screen constraint */
            .top-header {
                padding: 0 16px;
                gap: 12px;
            }

            .top-header .logo {
                font-size: 1.15rem;
            }

            .header-right {
                display: none !important;
            }

            .top-header .search-input {
                max-width: none;
            }

            #app-container:not(.tab-library) .top-header .search-input {
                display: none !important;
            }

            /* Premium bottom floating player design */
            .player-bar {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) 120px !important;
                align-items: center;
                padding: 0 16px;
                height: 64px;
                background: #141414;
                border-top: 1px solid var(--border);
                position: relative;
                gap: 12px;
            }

            .player-left {
                display: flex;
                align-items: center;
                gap: 10px;
                min-width: 0;
            }

            .mini-art {
                width: 44px;
                height: 44px;
                border-radius: 4px;
                object-fit: cover;
                flex-shrink: 0;
            }

            .mini-info {
                flex: 1;
                min-width: 0;
                overflow: hidden;
            }

            .mini-title {
                font-size: 0.8rem;
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
                gap: 14px;
                margin-bottom: 0;
            }

            /* Hide shuffle, loop and volume controls in tight mobile space */
            .player-center .controls .ctrl-btn:first-child,
            .player-center .controls .ctrl-btn:last-child,
            .player-right {
                display: none !important;
            }

            /* Top progress bar line */
            .player-center .progress-row {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
                padding: 0;
            }

            .player-center .progress-row .time-label {
                display: none !important;
            }

            .player-center .progress-row .seek-bar {
                width: 100%;
                height: 2px;
                border-radius: 0;
                background: #333;
                margin: 0;
            }

            .player-center .progress-row .seek-bar::-webkit-slider-thumb {
                width: 0;
                height: 0;
                border: 0;
            }
            .player-center .progress-row .seek-bar::-moz-range-thumb {
                width: 0;
                height: 0;
                border: 0;
            }
        }

        @media (max-width: 480px) {
            .hero-art {
                width: 170px;
                height: 170px;
            }
            .hero-title {
                font-size: 1.15rem;
                max-width: 260px;
            }
            .audio-wave {
                height: 36px;
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
        <div class="topbar">
            <span class="topbar-title">Now Playing</span>
        </div>

        <!-- Hero column -->
        <div class="main-hero">
            <!-- No track yet -->
            <template x-if="!current">
                <div class="empty-state">
                    <div class="empty-icon">🎵</div>
                    <div style="font-size:1rem;font-weight:600">Pick something to play</div>
                    <div style="font-size:.85rem">Select a track from the sidebar</div>
                </div>
            </template>

            <!-- Hero when track is selected -->
            <template x-if="current">
                <div class="now-playing-hero">
                    <!-- Blurry bg layer -->
                    <div class="hero-blur-bg" :style="'background-image:url(' + currentArt + ')'" ></div>

                    <!-- Waveform animation strip -->
                    <div class="audio-wave" :class="{active: isPlaying}">
                        <span></span><span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>

                    <!-- Main content above blur -->
                    <div class="hero-content">
                        <div class="art-wrap" :class="{playing: isPlaying}">
                            <img class="hero-art" :src="currentArt" alt="Album Art"
                                 @error="currentArt = 'assets/album_placeholder.png'" />
                        </div>
                        <div class="hero-title marquee" :class="{playing: isPlaying}">
                            <span x-text="current.name"></span>
                        </div>
                        <div class="hero-sub">Local Library</div>
                    </div>
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
    <audio x-ref="audio" preload="metadata"></audio>
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
            Promise.all([
                fetch('api.php').then(r => r.json()),
                fetch('stats.php').then(r => r.json()).catch(() => ({ counts: {} }))
            ]).then(([trackData, statsData]) => {
                this.tracks = trackData.tracks || [];
                this.playCounts = statsData.counts || {};
                this.sortByPlayCount();
                this.$nextTick(() => {
                    this._observeAll();
                    this.updateMarquees();
                });
            }).catch(() => {});

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
