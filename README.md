# NDS HR — Enterprise WordPress Human Resources Management System

[![Build & Package WordPress Plugin](https://github.com/3omrosama/hr_wordpress/actions/workflows/build-package.yml/badge.svg)](https://github.com/3omrosama/hr_wordpress/actions/workflows/build-package.yml)
[![Version](https://img.shields.io/badge/version-2.0.0-teal.svg)](https://github.com/3omrosama/hr_wordpress)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.txt)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B%20%7C%208.2%2B-777bb4.svg)](https://www.php.net)

NDS HR is a modern, standalone, production-ready Human Resources Management System built for WordPress. It includes custom database tables, granular role-based capability routing, an employee directory, self-service portals, independent canonical HR routes, and comprehensive audit logs.

---

## ⚠️ Installing the WordPress Plugin

> **CRITICAL NOTICE FOR WORDPRESS INSTALLATION:**
>
> **DO NOT** use GitHub's green button **`Code → Download ZIP`** to install this plugin on WordPress!
>
> The GitHub repository is the **source code repository** containing build tooling, developer scripts, and frontend preview environments. Downloading the raw repository ZIP will **fail to install** because WordPress requires the plugin root folder (`nds-hr/`) and its entrypoint (`nds-hr.php`) directly inside the archive root.

### How to Install the Official Plugin ZIP

1. **Download the Production Plugin:**
   - **From GitHub Releases (Recommended):** Download `nds-hr-v2.0.0.zip` directly from the [Releases](../../releases) section.
   - **From GitHub Actions Artifacts:** Go to the [Actions tab](../../actions) → latest run of **"Build & Package WordPress Plugin"** → download the artifact. *(Note: GitHub Actions wraps downloaded artifacts in an outer zip file; simply extract the downloaded archive to access the clean `nds-hr-v2.0.0.zip` plugin inside).*
2. In your WordPress administration panel:
   - Navigate to **Plugins → Add New → Upload Plugin**.
   - Choose the `nds-hr-v2.0.0.zip` file (the archive containing `nds-hr/nds-hr.php` at its root).
   - Click **Install Now**, then click **Activate Plugin**.
3. Once activated, NDS HR initializes the custom database schema, registers custom roles, and makes canonical routes available at:
   - **HR Portal:** `your-domain.com/hr/`
   - **Employee Self-Service:** `your-domain.com/employee/`
   - **Unified Login:** `your-domain.com/hr/login/` (or `your-domain.com/login/`)
   - **Initial Setup (First Admin):** `your-domain.com/hr/setup/`

---

## 📁 Repository Structure vs Production Package

### Source Repository (Development)
```
hr_wordpress/
├── .github/workflows/       # GitHub Actions automated CI & packaging pipeline
│   └── build-package.yml
├── nds-hr/                  # Official WordPress Plugin Source Code
│   ├── nds-hr.php           # Main WordPress plugin entrypoint & header
│   ├── includes/            # Core router, auth, database, RBAC, logger
│   ├── admin/               # Administrative dashboard and management
│   ├── employee/            # Employee self-service portal handlers
│   ├── modules/             # Service, repository & data layers
│   ├── templates/           # Server-rendered layouts & views
│   ├── assets/              # CSS, JavaScript & RTL stylesheets
│   ├── languages/           # Translation templates (.pot)
│   ├── readme.txt           # WordPress.org standard metadata
│   └── uninstall.php        # Safe lifecycle cleanup
├── scripts/                 # Production build & packaging verification scripts
│   ├── build-wordpress.mjs  # WordPress plugin integrity verification
│   ├── package-wordpress.mjs# Production ZIP archiver & version extractor
│   ├── validate-package.mjs # Programmatic ZIP structure & security validator
│   └── lint-php.mjs         # Automated PHP syntax checker
├── src/                     # AI Studio / Vite preview simulation application
├── public/                  # Public assets & synchronized package distribution
├── package.json             # NPM dependencies and packaging pipelines
├── tsconfig.json            # TypeScript configuration
└── vite.config.ts           # Vite development server configuration
```

### Production Plugin Artifact (`nds-hr-vX.Y.Z.zip`)
When packaged by the build pipeline, the archive contains **only** the production plugin:
```
nds-hr-v2.0.0.zip
└── nds-hr/
    ├── nds-hr.php           # ZIP_ROOT/nds-hr/nds-hr.php
    ├── includes/
    ├── admin/
    ├── employee/
    ├── modules/
    ├── templates/
    ├── assets/
    ├── languages/
    ├── readme.txt
    └── uninstall.php
```

---

## 🛠️ Developer & Packaging Commands

| Command | Description |
|---|---|
| `npm run build:wordpress` | Verifies the core WordPress plugin file integrity, required headers, and assets. |
| `npm run package:wordpress` | Extracts version, runs the build, packages `nds-hr-vX.Y.Z.zip`, and executes validation. |
| `npm run validate:package` | Programmatically inspects the ZIP archive to verify headers, structure, and zero leakages. |
| `npm run lint:php` | Runs `php -l` syntax validation across all plugin PHP files. |
| `npm run dev` | Starts the local React/Vite interactive HR simulation preview on port 3000. |
| `npm run build` | Compiles the frontend simulation web application. |
| `npm run lint` | Type-checks TypeScript code across the preview application. |

---

## 🔒 Security & Packaging Standards

The packaging pipeline guarantees:
- **Zero Configuration Leakage:** `.env`, `.git`, `.github`, API keys, and local credentials are strictly excluded.
- **No Node/Dev Bloat:** `node_modules`, `src/`, `package.json`, and Vite development artifacts are omitted from the WordPress ZIP.
- **Correct Root Folder:** The archive root is strictly `nds-hr/`, satisfying all WordPress upload requirements.
- **Deterministic Versioning:** Package version is dynamically extracted from `nds-hr/nds-hr.php`.

---

## 📄 License
This project is licensed under the **GPL-2.0+ License** — see [LICENSE](http://www.gnu.org/licenses/gpl-2.0.txt) for details.
