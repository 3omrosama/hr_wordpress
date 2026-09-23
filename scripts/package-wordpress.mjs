#!/usr/bin/env node

/**
 * NDS HR — WordPress Production Plugin Packaging Pipeline
 *
 * Produces the official distributable WordPress Plugin ZIP:
 *   nds-hr-vX.Y.Z.zip
 *
 * Internal ZIP structure:
 *   nds-hr-vX.Y.Z.zip
 *   └── nds-hr/
 *       ├── nds-hr.php
 *       ├── uninstall.php
 *       ├── readme.txt
 *       ├── includes/
 *       ├── admin/
 *       ├── employee/
 *       ├── modules/
 *       ├── templates/
 *       ├── assets/
 *       └── languages/
 */

import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const ROOT_DIR = path.resolve(__dirname, '..');
const PLUGIN_SRC_DIR = path.join(ROOT_DIR, 'nds-hr');
const DIST_DIR = path.join(ROOT_DIR, 'dist');
const PUBLIC_DIR = path.join(ROOT_DIR, 'public');

console.log('============================================================');
console.log(' NDS HR: Starting Production Plugin Packaging Pipeline');
console.log('============================================================');

// 1. Extract canonical version from nds-hr/nds-hr.php
const mainPluginFile = path.join(PLUGIN_SRC_DIR, 'nds-hr.php');
if (!fs.existsSync(mainPluginFile)) {
  console.error(' [ERROR] Main plugin file nds-hr.php not found!');
  process.exit(1);
}

const mainContent = fs.readFileSync(mainPluginFile, 'utf8');

// Match Version from header: * Version: X.Y.Z
const headerMatch = mainContent.match(/Version:\s*([0-9]+\.[0-9]+(?:\.[0-9]+)?(?:-[a-zA-Z0-9.]+)?)/i);
const constMatch = mainContent.match(/define\(\s*['"]NDS_HR_VERSION['"]\s*,\s*['"]([^'"]+)['"]\s*\)/);

const version = headerMatch ? headerMatch[1].trim() : (constMatch ? constMatch[1].trim() : '1.0.0');

console.log(` Canonical Plugin Version: v${version}`);
console.log(` Source Plugin Directory:  ${PLUGIN_SRC_DIR}`);

// 2. Run the protected WordPress build step first
console.log('\n--- Step 1: Running WordPress Build Verification ---');
try {
  execSync('node scripts/build-wordpress.mjs', { stdio: 'inherit', cwd: ROOT_DIR });
} catch (err) {
  console.error(' [FAIL] Build verification step failed.');
  process.exit(1);
}

// 3. Ensure dist and public directories exist
if (!fs.existsSync(DIST_DIR)) {
  fs.mkdirSync(DIST_DIR, { recursive: true });
}
if (!fs.existsSync(PUBLIC_DIR)) {
  fs.mkdirSync(PUBLIC_DIR, { recursive: true });
}

// 4. Define target archive filenames
const versionedZipName = `nds-hr-v${version}.zip`;
const versionedZipPath = path.join(DIST_DIR, versionedZipName);
const genericDistZipPath = path.join(DIST_DIR, 'nds-hr.zip');
const publicZipPath = path.join(PUBLIC_DIR, 'nds-hr.zip');
const publicDownloadsDir = path.join(PUBLIC_DIR, 'downloads');
if (!fs.existsSync(publicDownloadsDir)) {
  fs.mkdirSync(publicDownloadsDir, { recursive: true });
}
const publicDownloadsZipPath = path.join(publicDownloadsDir, 'nds-hr.zip');

console.log('\n--- Step 2: Creating Production ZIP Archive ---');
console.log(` Target output: ${versionedZipPath}`);

// Clean existing zip files
[versionedZipPath, genericDistZipPath, publicZipPath, publicDownloadsZipPath].forEach(f => {
  if (fs.existsSync(f)) {
    try { fs.unlinkSync(f); } catch (e) {}
  }
});

// Run Python archiver to create clean, standard PKZIP archive with explicit directory records (0755) and file records (0644)
try {
  execSync(`python3 "${path.join(ROOT_DIR, 'scripts', 'make-zip.py')}" "${PLUGIN_SRC_DIR}" "${versionedZipPath}"`, {
    stdio: 'inherit',
    cwd: ROOT_DIR,
  });
} catch (err) {
  console.error(' [ERROR] Python packaging script failed:', err.message);
  process.exit(1);
}

const totalBytes = fs.statSync(versionedZipPath).size;
const totalKB = (totalBytes / 1024).toFixed(2);
console.log(` Archive created successfully: ${totalKB} KB (${totalBytes} bytes)`);

// Duplicate to generic name for preview/dev and public directory
fs.copyFileSync(versionedZipPath, genericDistZipPath);
fs.copyFileSync(versionedZipPath, publicZipPath);
fs.copyFileSync(versionedZipPath, publicDownloadsZipPath);
console.log(` Synchronized UI download asset to: ${publicZipPath}`);

// 5. Run automated programmatic package validation
console.log('\n--- Step 3: Programmatic Package Validation ---');
try {
  execSync(`node scripts/validate-package.mjs "${versionedZipPath}"`, { stdio: 'inherit', cwd: ROOT_DIR });
} catch (err) {
  console.error(' [FAIL] Package validation failed.');
  process.exit(1);
}

console.log('\n============================================================');
console.log(` [SUCCESS] Production WordPress Plugin Package Built:`);
console.log(` Artifact: dist/${versionedZipName}`);
console.log(` Internal Root: nds-hr/`);
console.log(` Installable via: WordPress > Plugins > Add New > Upload Plugin`);
console.log('============================================================\n');
