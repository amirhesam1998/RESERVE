# Reserv — Salon Frontend (Vanilla JS + Konva)

Frontend for the Salon project: layout editor (floors, sections, seats) built with vanilla JavaScript, HTML/CSS, and [Konva.js](https://konvajs.org/).

> This folder lives inside the main project repo. You do **not** need to clone this separately — see the [root README](../README.md) for cloning the whole project.

## Requirements

- Node.js 18+
- npm
- The Laravel backend running locally (see the [root README](../README.md))

## 1. Install dependencies

From inside this folder:

```bash
cd Reserv
npm install
```

## 2. Environment setup

```bash
cp .env.example .env   # if present, otherwise create manually
```

```dotenv
VITE_API_BASE_URL=http://localhost:8000
```

> Must match the backend's `APP_URL`. Change it if your backend runs on a different port.

## 3. Run the dev server

```bash
npm run dev
```

Runs on `http://localhost:5174` by default. If Vite picks a different port, make sure that port is allowed in the backend's `config/cors.php`.

## 4. Backend must be running first

This app makes authenticated, cookie-based requests (`credentials: 'include'`) to the backend. Before using it:

1. Start the Laravel backend (see [root README](../README.md)).
2. Confirm `FRONTEND_URL` in the backend's `.env` matches this dev server's URL.
3. Log in through the app — the backend sets `access_token`/`refresh_token` cookies used for all later requests.

## Project structure

```
Reserv/
├── src/
│   ├── css/
│   └── js/
│       ├── halleditor.js       # layout editor logic
│       ├── halleditor4.js      # layout editor (v4 / active version)
│       └── salonLayout.js      # salon layout rendering/handling
├── public/
├── main.js                     # entry point
├── index.html
└── .env
```

## Common issues

| Symptom | Likely cause |
|---|---|
| `Failed to fetch` / CORS error | Backend isn't running, or its `config/cors.php` doesn't include this app's origin |
| Requests silently redirect / 302 in Network tab | Not logged in, or `access_token`/`refresh_token` cookies expired |
| Categories dropdown empty | Backend `/api/categories/tree` not reachable — check `VITE_API_BASE_URL` |
| Konva canvas blank / doesn't render | Check browser console for JS errors; verify layout data returned from the backend is valid |

## Tech stack

- Vanilla JavaScript (ES modules)
- HTML / CSS
- [Konva.js](https://konvajs.org/) — canvas rendering for the layout editor
- Vite — dev server & bundler
