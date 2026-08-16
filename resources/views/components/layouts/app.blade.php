<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Task Management') }}</title>
    <script>
        window.__initialRewardPopups = @json(session('reward_popups', []));
    </script>
    @vite(['resources/js/app.js'])
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f7f9;
            --panel: #ffffff;
            --text: #18212f;
            --muted: #657084;
            --line: #dfe3ea;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #0f8f5f;
            --warning: #b7791f;
            --danger: #c2410c;
        }

        * { box-sizing: border-box; }

        [x-cloak] { display: none !important; }

        body {
            margin: 0;
            background:
                linear-gradient(180deg, #eef6ff 0, #f6f7f9 320px),
                var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 15px;
        }

        a { color: inherit; text-decoration: none; }

        .shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.88);
            border-bottom: 1px solid rgba(191, 219, 254, 0.72);
            backdrop-filter: blur(14px);
        }

        .topbar-inner, .page {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar-inner {
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            color: white;
            background: linear-gradient(135deg, #2563eb, #0f8f5f);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.24);
            font-size: 13px;
            font-weight: 900;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 14px;
        }

        .nav-link,
        .nav-user {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0 10px;
            border-radius: 8px;
        }

        .nav-link {
            color: #334155;
            font-weight: 700;
        }

        .nav-link:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .nav-link.active {
            background: #dbeafe;
            color: #1d4ed8;
            box-shadow: inset 0 0 0 1px #bfdbfe;
        }

        .notification-link {
            position: relative;
            width: 38px;
            justify-content: center;
            padding: 0;
        }

        .notification-icon {
            width: 19px;
            height: 19px;
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #dc2626;
            color: white;
            border: 2px solid white;
            font-size: 11px;
            font-weight: 900;
            line-height: 1;
        }

        .notification-count[hidden] {
            display: none;
        }

        .nav-user {
            color: #0f172a;
            background: #f8fafc;
            border: 1px solid var(--line);
            font-weight: 700;
        }

        .profile-menu {
            position: relative;
        }

        .profile-trigger {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
            padding: 0 8px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: white;
            color: #0f172a;
            cursor: pointer;
        }

        .profile-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: 190px;
            padding: 8px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: white;
            box-shadow: 0 18px 38px rgba(24, 33, 47, 0.14);
            z-index: 60;
        }

        .profile-dropdown a,
        .profile-dropdown button {
            width: 100%;
            min-height: 38px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 10px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #334155;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            text-align: left;
        }

        .profile-dropdown a:hover,
        .profile-dropdown button:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            overflow: hidden;
            background: linear-gradient(135deg, #2563eb, #0f8f5f);
            color: white;
            font-size: 13px;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar.sm {
            width: 30px;
            height: 30px;
            font-size: 12px;
        }

        .avatar.task-avatar {
            width: 42px;
            height: 42px;
            font-size: 15px;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
        }

        .avatar.xl {
            width: 86px;
            height: 86px;
            font-size: 30px;
            box-shadow: 0 16px 36px rgba(37, 99, 235, 0.18);
        }

        .page {
            padding: 28px 0 48px;
            flex: 1;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 16px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
        }

        .span-4 { grid-column: span 4; }
        .span-8 { grid-column: span 8; }
        .span-12 { grid-column: span 12; }

        .centered-panel {
            grid-column: 3 / span 8;
        }

        .title {
            margin: 0;
            font-size: 26px;
            line-height: 1.2;
        }

        .subtitle {
            margin: 6px 0 0;
            color: var(--muted);
        }

        .label {
            display: block;
            margin: 0 0 6px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }

        .input {
            width: 100%;
            height: 42px;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 0 12px;
            color: var(--text);
            background: white;
            outline: none;
            transition: border-color 120ms ease, box-shadow 120ms ease;
        }

        .input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
        }

        .input.error-input {
            border-color: #ef4444;
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap .input {
            padding-right: 46px;
        }

        .password-toggle {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 8px;
            color: #64748b;
            background: transparent;
            cursor: pointer;
        }

        .password-toggle:hover {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
        }

        .field { margin-top: 14px; }

        .field-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 14px;
        }

        .checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 28px;
            color: var(--muted);
            font-size: 14px;
        }

        .checkbox-label input {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
        }

        .range-control {
            display: grid;
            gap: 10px;
        }

        .range-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .range-input {
            width: 100%;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .range-value {
            min-width: 48px;
            min-height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 12px;
            font-weight: 900;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 0 14px;
            border: 0;
            border-radius: 6px;
            background: var(--primary);
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        .button:hover { background: var(--primary-dark); }

        .button.loading {
            pointer-events: none;
            opacity: 0.82;
        }

        .button-spinner {
            width: 15px;
            height: 15px;
            border-radius: 999px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            animation: button-spin 700ms linear infinite;
        }

        @keyframes button-spin {
            to { transform: rotate(360deg); }
        }

        .button.secondary {
            background: #eef2ff;
            color: #1e3a8a;
        }

        .button.secondary:hover {
            background: #e0e7ff;
        }

        .button.danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .button.danger:hover {
            background: #fecaca;
        }

        .button.compact-button {
            min-height: 32px;
            padding: 0 11px;
            font-size: 13px;
        }

        .button.full {
            width: 100%;
        }

        .button.link {
            background: transparent;
            color: var(--primary);
            padding: 0;
        }

        .button.link:hover {
            background: #eff6ff;
        }

        .alert {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 16px;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .error {
            color: #b91c1c;
            margin-top: 6px;
            font-size: 13px;
        }

        .metric {
            font-size: 24px;
            font-weight: 800;
        }

        .muted { color: var(--muted); }

        .dashboard-hero {
            grid-column: span 8;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 26px;
            border: 0;
            color: white;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.84), rgba(30, 64, 175, 0.74)),
                url('https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1400&q=80') center / cover;
            box-shadow: 0 18px 42px rgba(24, 33, 47, 0.16);
        }

        .dashboard-hero .subtitle {
            color: rgba(255, 255, 255, 0.82);
            max-width: 62ch;
        }

        .hero-topline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .hero-pill {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            color: white;
            font-size: 12px;
            font-weight: 800;
            backdrop-filter: blur(8px);
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .hero-actions .button {
            background: white;
            color: #1e3a8a;
        }

        .hero-actions .button.secondary {
            background: rgba(255, 255, 255, 0.18);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.32);
        }

        .join-card {
            grid-column: span 4;
            border-color: #bfdbfe;
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 78%);
        }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .section-title {
            margin: 0;
            font-size: 20px;
            line-height: 1.25;
        }

        .metric-card {
            position: relative;
            overflow: hidden;
            min-height: 132px;
            border: 0;
            box-shadow: 0 12px 28px rgba(24, 33, 47, 0.08);
        }

        .metric-card::after {
            content: "";
            position: absolute;
            right: -24px;
            top: -24px;
            width: 90px;
            height: 90px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.46);
        }

        .metric-card .muted,
        .metric-card .subtitle {
            color: rgba(24, 33, 47, 0.68);
        }

        .metric-card.blue {
            background: linear-gradient(135deg, #dbeafe, #eff6ff 62%, #ffffff);
        }

        .metric-card.green {
            background: linear-gradient(135deg, #dcfce7, #f0fdf4 62%, #ffffff);
        }

        .metric-card.amber {
            background: linear-gradient(135deg, #fef3c7, #fffbeb 62%, #ffffff);
        }

        .metric-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            margin-bottom: 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.78);
            font-size: 16px;
            font-weight: 900;
        }

        .progress-track {
            height: 8px;
            margin-top: 14px;
            border-radius: 999px;
            background: rgba(24, 33, 47, 0.09);
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #2563eb, #0f8f5f);
        }

        .color-panel {
            border: 0;
            box-shadow: 0 12px 28px rgba(24, 33, 47, 0.07);
        }

        .leaderboard-panel {
            background: linear-gradient(180deg, #ffffff, #f8fafc);
        }

        .badges-panel {
            background: linear-gradient(180deg, #fff7ed, #ffffff 70%);
        }

        .achievements-panel {
            background: linear-gradient(180deg, #ecfdf5, #ffffff 70%);
        }

        .goals-panel {
            background: linear-gradient(180deg, #eef2ff, #ffffff 70%);
        }

        .task-panel {
            background: linear-gradient(180deg, #f8fafc, #ffffff);
        }

        .tabs {
            display: inline-flex;
            gap: 6px;
            padding: 5px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fafc;
        }

        .tab-button {
            min-height: 34px;
            padding: 0 12px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #475569;
            font-weight: 800;
            cursor: pointer;
        }

        .tab-button.active {
            background: white;
            color: #1d4ed8;
            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.12);
        }

        .task-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .task-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 14px;
            padding: 16px 18px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background:
                linear-gradient(180deg, #ffffff, #fbfdff);
            box-shadow: 0 10px 24px rgba(24, 33, 47, 0.045);
            transition: border-color 140ms ease, box-shadow 140ms ease, transform 140ms ease;
        }

        .task-card:hover {
            border-color: #bfdbfe;
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.10);
            transform: translateY(-1px);
        }

        .task-card-title {
            display: block;
            font-size: 16px;
            font-weight: 900;
            line-height: 1.35;
        }

        .task-card-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .task-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 14px;
            margin-top: 8px;
            color: var(--muted);
            font-size: 13px;
        }

        .task-card-badges {
            display: flex;
            align-items: flex-start;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .sent-card .task-card-main:hover .task-card-title {
            color: #1d4ed8;
        }

        .sent-actions form {
            margin: 0;
        }

        .activity-panel {
            background: linear-gradient(180deg, #ffffff, #f8fafc);
        }

        .attachment-panel {
            background: linear-gradient(180deg, #ffffff, #f8fafc);
        }

        .attachment-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
        }

        .attachment-thumb {
            position: relative;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fafc;
            cursor: pointer;
        }

        .attachment-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 160ms ease;
        }

        .attachment-thumb:hover img {
            transform: scale(1.04);
        }

        .attachment-thumb span {
            position: absolute;
            left: 8px;
            top: 8px;
            min-width: 26px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.72);
            color: white;
            font-size: 12px;
            font-weight: 900;
        }

        .attachment-files {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .attachment-file {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: white;
        }

        .attachment-file:hover {
            border-color: #bfdbfe;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.08);
        }

        .attachment-file-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 11px;
            font-weight: 900;
        }

        .attachment-file strong,
        .attachment-file small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .attachment-file small {
            margin-top: 3px;
            color: var(--muted);
        }

        .attachment-file em {
            color: #1d4ed8;
            font-style: normal;
            font-weight: 900;
        }

        .file-input {
            height: auto;
            min-height: 42px;
            padding: 9px 12px;
        }

        .image-lightbox {
            position: fixed;
            inset: 0;
            z-index: 90;
            display: grid;
            grid-template-rows: auto minmax(0, 1fr);
            gap: 12px;
            padding: 18px;
            background: rgba(15, 23, 42, 0.44);
            backdrop-filter: blur(10px);
        }

        .image-lightbox-toolbar {
            justify-self: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.72);
            color: white;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.26);
        }

        .image-lightbox-toolbar button,
        .image-lightbox-toolbar a {
            min-height: 34px;
            padding: 0 11px;
            border: 0;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.14);
            color: white;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }

        .image-lightbox-toolbar button:disabled {
            cursor: not-allowed;
            opacity: 0.42;
        }

        .image-lightbox-stage {
            min-height: 0;
            display: grid;
            place-items: center;
            overflow: auto;
        }

        .image-lightbox-stage img {
            max-width: 94vw;
            max-height: 82vh;
            object-fit: contain;
            transform-origin: center center;
            transition: transform 140ms ease;
        }

        .comment-feed {
            max-height: 420px;
            display: grid;
            gap: 12px;
            overflow-y: auto;
            padding: 4px 4px 12px;
            scroll-behavior: smooth;
        }

        .comment-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            max-width: min(720px, 100%);
        }

        .comment-row.mine {
            justify-self: end;
            flex-direction: row-reverse;
        }

        .comment-bubble {
            min-width: 0;
            padding: 11px 12px;
            border: 1px solid #e5eaf2;
            border-radius: 8px;
            background: white;
            box-shadow: 0 8px 18px rgba(24, 33, 47, 0.045);
        }

        .comment-row.mine .comment-bubble {
            border-color: #bfdbfe;
            background: #eff6ff;
        }

        .comment-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 12px;
        }

        .comment-meta strong {
            color: #0f172a;
            font-size: 13px;
        }

        .comment-bubble p {
            margin: 6px 0 0;
            color: #1f2937;
            line-height: 1.55;
            white-space: pre-wrap;
        }

        .comment-form {
            display: grid;
            gap: 10px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--line);
        }

        .comment-form textarea {
            height: auto;
            min-height: 82px;
            padding-top: 10px;
            resize: vertical;
        }

        .comment-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .profile-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            border-color: #bfdbfe;
            background:
                radial-gradient(circle at 8% 18%, rgba(37, 99, 235, 0.16), transparent 30%),
                radial-gradient(circle at 96% 0%, rgba(15, 143, 95, 0.16), transparent 26%),
                linear-gradient(135deg, #ffffff, #eff6ff 48%, #ecfdf5);
            box-shadow: 0 18px 42px rgba(24, 33, 47, 0.08);
        }

        .profile-main,
        .profile-photo-editor {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .profile-main .title {
            font-size: 28px;
        }

        .profile-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .profile-card {
            min-height: 220px;
            background:
                linear-gradient(180deg, rgba(248, 250, 252, 0.88), #ffffff 54%);
            box-shadow: 0 10px 24px rgba(24, 33, 47, 0.045);
        }

        .profile-card .row {
            background: white;
            border-color: #e5eaf2;
        }

        .profile-card .row strong {
            text-align: right;
        }

        .profile-bio {
            margin: 14px 0 0;
            color: #334155;
            line-height: 1.7;
        }

        .profile-edit-panel {
            background:
                linear-gradient(180deg, #ffffff, #f8fafc);
            box-shadow: 0 14px 34px rgba(24, 33, 47, 0.055);
        }

        .profile-form {
            display: grid;
            gap: 18px;
        }

        .profile-company-note {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 12px;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            background: #f0fdf4;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .span-form-2 {
            grid-column: span 2;
        }

        .file-input {
            height: auto;
            padding: 9px 12px;
        }

        .readonly-field {
            min-height: 42px;
            display: flex;
            align-items: center;
            padding: 0 12px;
            border: 1px solid #e5eaf2;
            border-radius: 6px;
            background: #f8fafc;
            color: #475569;
            font-weight: 800;
        }

        .hint {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 12px;
        }

        .timer-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 32px;
            min-width: 82px;
            justify-content: center;
            padding: 0 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, #fff7ed, #fef3c7);
            color: #92400e;
            border: 1px solid #fed7aa;
            font-size: 12px;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            box-shadow: 0 8px 16px rgba(245, 158, 11, 0.14);
        }

        .timer-pill::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.16);
        }

        .timer-pill.due {
            background: #dc2626;
            color: white;
            border-color: #b91c1c;
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.18);
        }

        .timer-pill.urgent {
            background: #111827;
            color: white;
            border-color: #111827;
            letter-spacing: 0.02em;
            box-shadow: 0 12px 24px rgba(17, 24, 39, 0.18);
        }

        .timer-pill.due::before {
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.18);
        }

        .timer-pill.urgent::before {
            background: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18);
        }

        .mini-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        .mini-metric {
            min-height: 94px;
            padding: 14px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: white;
        }

        .mini-metric.pending { border-color: #bfdbfe; background: #eff6ff; }
        .mini-metric.completed { border-color: #bbf7d0; background: #f0fdf4; }
        .mini-metric.overdue { border-color: #fed7aa; background: #fff7ed; }

        .status-dot {
            display: inline-flex;
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--primary);
            margin-right: 8px;
        }

        .status-dot.green { background: var(--success); }
        .status-dot.amber { background: #f59e0b; }
        .status-dot.red { background: var(--danger); }

        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #111827;
            color: white;
            font-size: 13px;
            font-weight: 900;
        }

        .row:hover {
            border-color: #bfdbfe;
            background: white;
            box-shadow: 0 8px 18px rgba(24, 33, 47, 0.06);
        }

        .notification-row {
            position: relative;
        }

        .notification-row.unread {
            border-color: #bfdbfe;
            background: linear-gradient(90deg, #eff6ff, #ffffff 72%);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.08);
        }

        .notification-row.unread::before {
            content: "";
            position: absolute;
            left: 0;
            top: 12px;
            bottom: 12px;
            width: 4px;
            border-radius: 999px;
            background: #2563eb;
        }

        .notification-row.unread strong {
            color: #0f172a;
        }

        .notification-row.read {
            background: #fbfcfe;
            opacity: 0.78;
        }

        .goal-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .goal-card {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 14px;
            background: white;
        }

        .goal-card.badge-goal { border-color: #fed7aa; background: #fff7ed; }
        .goal-card.level-goal { border-color: #bfdbfe; background: #eff6ff; }
        .goal-card.achievement-goal { border-color: #bbf7d0; background: #f0fdf4; }

        .goal-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: white;
            font-weight: 900;
        }

        .goal-card h3 {
            margin: 0;
            font-size: 15px;
            line-height: 1.3;
        }

        .goal-card p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .goal-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .goal-toggle {
            margin-top: 14px;
            border: 1px solid #c7d2fe;
            border-radius: 8px;
            background: white;
            overflow: hidden;
        }

        .goal-toggle summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px;
            cursor: pointer;
            color: #1e3a8a;
            font-weight: 800;
            list-style: none;
        }

        .goal-toggle summary::-webkit-details-marker {
            display: none;
        }

        .goal-toggle summary::after {
            content: "+";
            width: 26px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #eef2ff;
            color: #3730a3;
        }

        .goal-toggle[open] summary::after {
            content: "-";
        }

        .goal-toggle-body {
            padding: 0 14px 14px;
        }

        .auth-wrap {
            min-height: calc(100vh - 140px);
            display: grid;
            place-items: center;
        }

        .auth-shell {
            width: min(920px, 100%);
            display: grid;
            grid-template-columns: minmax(0, 1fr) 390px;
            background: var(--panel);
            border: 1px solid rgba(191, 219, 254, 0.92);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(37, 99, 235, 0.14);
        }

        .auth-intro {
            padding: 34px;
            color: white;
            background:
                linear-gradient(135deg, rgba(37, 99, 235, 0.90), rgba(15, 143, 95, 0.82)),
                url('https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=1000&q=80') center / cover;
            border-right: 1px solid rgba(191, 219, 254, 0.72);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 28px;
        }

        .auth-kicker {
            margin: 0 0 10px;
            color: rgba(255, 255, 255, 0.86);
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .auth-heading {
            margin: 0;
            font-size: 34px;
            line-height: 1.12;
            letter-spacing: 0;
        }

        .auth-copy {
            margin: 12px 0 0;
            max-width: 46ch;
            color: rgba(255, 255, 255, 0.84);
            line-height: 1.65;
        }

        .auth-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .auth-stat {
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.16);
            padding: 12px;
            backdrop-filter: blur(10px);
        }

        .auth-stat strong {
            display: block;
            font-size: 20px;
            line-height: 1;
        }

        .auth-stat span {
            display: block;
            margin-top: 6px;
            color: rgba(255, 255, 255, 0.76);
            font-size: 12px;
        }

        .auth-panel {
            padding: 32px;
            background:
                linear-gradient(180deg, rgba(239, 246, 255, 0.72), rgba(255, 255, 255, 1) 42%),
                white;
        }

        .auth-panel-header {
            margin-bottom: 22px;
        }

        .auth-actions {
            display: grid;
            gap: 10px;
            margin-top: 20px;
        }

        .demo-logins {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
        }

        .demo-logins-title {
            margin: 0 0 10px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .demo-account {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 0;
            border-top: 1px solid #eef1f5;
        }

        .demo-account:first-of-type {
            border-top: 0;
        }

        .demo-account strong {
            display: block;
            font-size: 13px;
        }

        .demo-account span {
            color: var(--muted);
            font-size: 12px;
        }

        .list {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fbfcfe;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 0 9px;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 12px;
            font-weight: 700;
        }

        .badge.success {
            background: #dcfce7;
            color: #166534;
        }

        .badge.warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge.danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge.neutral {
            background: #f1f5f9;
            color: #334155;
        }

        .toast-stack {
            position: fixed;
            right: 16px;
            bottom: 16px;
            display: grid;
            gap: 10px;
            width: min(360px, calc(100vw - 32px));
            z-index: 50;
        }

        .toast {
            position: relative;
            background: #111827;
            color: white;
            border-radius: 8px;
            padding: 12px 42px 12px 12px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.22);
        }

        .toast strong { display: block; }
        .toast p { margin: 4px 0 0; color: #d1d5db; }

        .toast-close {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 26px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 8px;
            color: #cbd5e1;
            background: transparent;
            cursor: pointer;
        }

        .toast-close:hover {
            color: white;
            background: rgba(255, 255, 255, 0.12);
        }

        .reward-backdrop {
            position: fixed;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 18px;
            background: rgba(15, 23, 42, 0.36);
            backdrop-filter: blur(8px);
            z-index: 80;
        }

        .reward-modal {
            position: relative;
            width: min(520px, 100%);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.64);
            border-radius: 8px;
            background:
                linear-gradient(135deg, #ffffff 0%, #eff6ff 48%, #ecfdf5 100%);
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.28);
        }

        .reward-modal.collaborator {
            background:
                linear-gradient(135deg, #ffffff 0%, #f0fdf4 48%, #fff7ed 100%);
        }

        .reward-modal::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, transparent 0 10%, rgba(37, 99, 235, 0.18) 10% 12%, transparent 12% 28%, rgba(15, 143, 95, 0.18) 28% 30%, transparent 30% 48%, rgba(245, 158, 11, 0.22) 48% 50%, transparent 50% 100%);
            opacity: 0.62;
            pointer-events: none;
        }

        .reward-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.72);
            color: #475569;
            cursor: pointer;
            z-index: 2;
        }

        .reward-body {
            position: relative;
            z-index: 1;
            padding: 30px;
        }

        .reward-kicker {
            margin: 0 0 12px;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .reward-title {
            margin: 0;
            color: #0f172a;
            font-size: 28px;
            line-height: 1.15;
        }

        .reward-message {
            margin: 12px 0 0;
            color: #475569;
            line-height: 1.6;
        }

        .reward-points {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin: 20px 0;
            color: #0f172a;
        }

        .reward-points strong {
            font-size: 50px;
            line-height: 1;
        }

        .reward-points span {
            color: #1d4ed8;
            font-weight: 900;
        }

        .reward-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 16px;
        }

        .reward-stat {
            padding: 12px;
            border: 1px solid rgba(191, 219, 254, 0.88);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.72);
        }

        .reward-stat span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .reward-stat strong {
            display: block;
            margin-top: 4px;
            color: #0f172a;
            font-size: 14px;
        }

        .reward-progress {
            height: 8px;
            margin-top: 16px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.10);
            overflow: hidden;
        }

        .reward-progress span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #2563eb, #0f8f5f, #f59e0b);
        }

        .reward-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 22px;
        }

        @media (max-width: 760px) {
            .span-4, .span-8, .dashboard-hero, .join-card { grid-column: span 12; }
            .centered-panel { grid-column: span 12; }
            .topbar-inner {
                height: auto;
                padding: 12px 0;
                align-items: stretch;
                flex-direction: column;
            }
            .brand {
                width: 100%;
                justify-content: center;
            }
            .nav {
                width: 100%;
                flex-wrap: wrap;
                justify-content: center;
                gap: 6px;
            }
            .nav-link {
                min-height: 36px;
            }
            .profile-menu {
                width: 100%;
                display: flex;
                justify-content: center;
            }
            .profile-dropdown {
                left: 50%;
                right: auto;
                transform: translateX(-50%);
            }
            .page {
                width: min(100% - 20px, 1120px);
                padding: 18px 0 34px;
            }
            .grid {
                gap: 12px;
            }
            .panel {
                padding: 14px;
            }
            .section-head {
                align-items: flex-start;
                flex-direction: column;
            }
            .section-head .button,
            .hero-actions .button {
                width: 100%;
            }
            .row {
                align-items: flex-start;
                flex-direction: column;
            }
            .row > div:last-child {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }
            .tabs {
                width: 100%;
            }
            .tab-button {
                flex: 1;
            }
            .task-card {
                grid-template-columns: 1fr;
                padding: 14px;
            }
            .task-card-badges {
                justify-content: flex-start;
            }
            .profile-hero,
            .profile-main,
            .profile-photo-editor {
                align-items: flex-start;
                flex-direction: column;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .span-form-2 {
                grid-column: span 1;
            }
            .auth-wrap { min-height: auto; place-items: stretch; }
            .auth-shell { grid-template-columns: 1fr; }
            .auth-intro { display: none; }
            .auth-panel { padding: 22px; }
            .field-row { align-items: flex-start; flex-direction: column; }
            .dashboard-hero { min-height: 220px; }
            .mini-metrics { grid-template-columns: 1fr; }
            .goal-grid { grid-template-columns: 1fr; }
            .reward-body { padding: 24px; }
            .reward-grid { grid-template-columns: 1fr; }
            .reward-actions {
                align-items: stretch;
                flex-direction: column;
            }
            .reward-actions .button { width: 100%; }
            .attachment-gallery {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .attachment-file {
                grid-template-columns: auto minmax(0, 1fr);
            }
            .attachment-file em {
                grid-column: 2;
            }
            .image-lightbox {
                padding: 10px;
            }
            .image-lightbox-toolbar {
                width: 100%;
                flex-wrap: wrap;
            }
        }
    </style>
</head>
@php
    $activeCompany = auth()->check() ? app(\App\Support\CompanyContext::class)->current(auth()->user()) : null;
    $notificationSetting = auth()->check()
        ? auth()->user()->notificationSettings()->where('company_id', $activeCompany?->id)->first()
        : null;
@endphp
<body
    @auth
        data-user-id="{{ auth()->id() }}"
        data-company-id="{{ $activeCompany?->id }}"
        data-team-ids="{{ auth()->user()->teamMemberships()->pluck('team_id')->join(',') }}"
        data-unread-notifications="{{ auth()->user()->unreadNotifications()->count() }}"
        data-notification-sounds="{{ ($notificationSetting?->sounds_enabled ?? true) ? '1' : '0' }}"
        data-notification-volume="{{ $notificationSetting?->sound_volume ?? 50 }}"
    @endauth
>
<div class="shell">
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="{{ auth()->check() ? route('employee.dashboard') : route('login') }}">
                <span class="brand-mark">IL</span>
                {{ config('app.name', 'Task Management') }}
            </a>

            <nav class="nav">
                @auth
                    <a class="nav-link @if(request()->routeIs('employee.dashboard')) active @endif" href="{{ route('employee.dashboard') }}">Dashboard</a>
                    <a class="nav-link @if(request()->routeIs('tasks.*')) active @endif" href="{{ route('tasks.index') }}">Tasks</a>
                    <a class="nav-link notification-link @if(request()->routeIs('notifications.*')) active @endif" href="{{ route('notifications.index') }}" x-data aria-label="Notifications">
                        <svg class="notification-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M15 17H9m10-1.5c-.9-.9-1.5-1.7-1.5-4.5A5.5 5.5 0 0 0 6.5 11c0 2.8-.6 3.6-1.5 4.5-.4.4-.1 1.1.5 1.1h13c.6 0 .9-.7.5-1.1Z" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 19a2 2 0 0 1-4 0" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                        </svg>
                        <span class="notification-count" x-text="$store.notifications.unread > 99 ? '99+' : $store.notifications.unread" x-bind:hidden="$store.notifications.unread < 1"></span>
                    </a>
                    @if (auth()->user()->canAccessPanel(filament()->getPanel('admin')))
                        <a class="nav-link" href="{{ url('/admin') }}">Admin</a>
                    @endif
                    <div class="profile-menu" x-data="{ open: false }" x-on:click.outside="open = false">
                        <button class="profile-trigger" type="button" x-on:click="open = ! open" aria-label="Open profile menu">
                            <span class="avatar sm">
                                @if (auth()->user()->profile?->photoUrl())
                                    <img src="{{ auth()->user()->profile->photoUrl() }}" alt="{{ auth()->user()->name }}">
                                @else
                                    <span>{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                                @endif
                            </span>
                            <span>{{ auth()->user()->name }}</span>
                        </button>

                        <div class="profile-dropdown" x-show="open" x-cloak>
                            <a href="{{ route('profile.show') }}">My Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="page">
        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        {{ $slot }}
    </main>
</div>

@auth
    <div class="toast-stack" x-data>
        <template x-for="item in $store.notifications.items" :key="item.id">
            <a class="toast" :href="item.action_url || '#'">
                <button class="toast-close" type="button" aria-label="Close notification" x-on:click.prevent.stop="$store.notifications.dismiss(item.id)">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong x-text="item.title"></strong>
                <p x-text="item.body"></p>
            </a>
        </template>
    </div>

    <div class="reward-backdrop" x-data x-show="$store.rewards.current" x-cloak>
        <div
            class="reward-modal"
            x-bind:class="{ collaborator: $store.rewards.current?.audience === 'collaborator' }"
            x-on:click.outside="$store.rewards.dismiss()"
        >
            <button class="reward-close" type="button" aria-label="Close reward popup" x-on:click="$store.rewards.dismiss()">
                <span aria-hidden="true">&times;</span>
            </button>

            <div class="reward-body">
                <p class="reward-kicker" x-text="$store.rewards.current?.title"></p>
                <h2 class="reward-title" x-text="$store.rewards.current?.headline"></h2>
                <p class="reward-message" x-text="$store.rewards.current?.message"></p>

                <div class="reward-points">
                    <strong x-text="'+' + ($store.rewards.current?.points || 0)"></strong>
                    <span>points</span>
                </div>

                <div class="reward-grid">
                    <div class="reward-stat">
                        <span>XP Added</span>
                        <strong x-text="'+' + ($store.rewards.current?.xp_added || 0) + ' XP'"></strong>
                    </div>
                    <div class="reward-stat">
                        <span>Current Level</span>
                        <strong x-text="$store.rewards.current?.current_level"></strong>
                    </div>
                    <div class="reward-stat">
                        <span>Next Level</span>
                        <strong x-text="$store.rewards.current?.next_level"></strong>
                    </div>
                </div>

                <div class="reward-progress" aria-hidden="true">
                    <span x-bind:style="'width: ' + ($store.rewards.current?.progress || 0) + '%'"></span>
                </div>

                <div class="reward-actions">
                    <span class="muted" x-text="($store.rewards.current?.xp_to_next || 0) > 0 ? ($store.rewards.current.xp_to_next + ' XP to the next level. Keep working.') : 'You are at the top level. Keep leading the rhythm.'"></span>
                    <button class="button" type="button" x-on:click="$store.rewards.confirm()">Okay</button>
                </div>
            </div>
        </div>
    </div>
@endauth
</body>
</html>
