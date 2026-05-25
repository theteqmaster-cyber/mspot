# 🎵 MSpot — Local Music Streamer

![MSpot Cover Picture](assets/readme_cover.png)

MSpot is a lightweight, premium web-based music streaming platform designed to run in local homelabs and personal networks. It serves your local offline music collection recursively, providing a fast, self-hosted streaming experience on any desktop or mobile device.

---

## 🚀 Why MSpot?

Modern music streaming services are locked behind monthly subscriptions, track user habits, and require constant internet connectivity. MSpot is created to reclaim ownership of your digital music library. Simply place your music in your server's folder, spin up MSpot, and stream your high-quality files locally over Wi-Fi with zero subscriptions and complete privacy.

---

## 🎯 Objectives

- **Visual Excellence:** Provide a Spotify-inspired, premium glassmorphism user interface with responsive layout controls, custom color palettes, and micro-animations.
- **Mobile First:** Ensure absolute responsiveness using modern CSS viewport units (`100dvh`) and grid structures to prevent page clipping on smaller screens.
- **High Performance:** Optimize server-side operations (like raw audio streaming and cover art caching) to ensure instantaneous loads and low CPU overhead.
- **Zero Database Hassle:** Keep setup minimal with flat-file JSON caching, eliminating the need for external SQL server configurations.

---

## 🛠 Tech Stack

- **Backend:** PHP (Native ID3 tag extraction, caching, and stream controllers)
- **Frontend:** Alpine.js (State management, event triggers, DOM bindings)
- **Styling:** Custom Vanilla CSS (Responsive CSS Grid layouts, flexbox structures, CSS Variables, and responsive `@media` query overlays)
- **Datastore:** Portable flat-file JSON (`data/stats.json`) with file-locking (`flock`) to handle statistics and play counts safely.

---

## ✨ Key Features

- **Mobile Viewport Optimization:** Dynamic row switching, bottom navigation tabs, and a floating mini-player bar designed to fit mobile browsers perfectly.
- **Adaptive Title Marquee:** An observer watches song title widths dynamically and triggers a smooth CSS keyframe marquee scroll whenever a track name exceeds its container width.
- **Cover Art Caching:** Fast-path caches look up sentinel files (e.g. `.none` folders) first, avoiding CPU-heavy file scans for tracks without embedded artwork.
- **Standard Controls:** Support for Shuffle, Loop modes (Off, Single track, All tracks), seekable progress slider, volume controls, and sidebar searches.
- **Keyboard Shortcuts:** Keyboard hotkeys for quick controls (Space to Play/Pause, Arrow keys for volume, `N` for next, `P` for previous).

---

## 🔮 Future Roadmap

- [ ] **Playlist Management:** Allow users to build, name, and edit custom playlists from their web browser.
- [ ] **Multi-User Profiles:** Introduce lightweight session/token management for individualized stats and play histories.
- [ ] **PWA Support:** Convert MSpot into a Progressive Web App (PWA) to allow installation on home screens and service-worker caching for offline listening.
- [ ] **Audio Visualizer:** Implement a native canvas-based visualizer using the Web Audio API for immersive spectrum bars.
- [ ] **Lossless Formats support:** Enhance mobile transcoding/streaming capabilities for higher-bitrate formats like FLAC and ALAC.

---

## ⚙️ Quick Start

1. Place your audio files inside `/home/mphatic/Music` (supports `.mp3`, `.wav`, `.flac`, `.aac`, `.ogg`).
2. Run the PHP server bound to all interfaces so other Wi-Fi devices can reach it:
   ```bash
   php -S 0.0.0.0:8080
   ```
3. Open `http://<your-server-ip>:8080` on any web browser on your local network.
