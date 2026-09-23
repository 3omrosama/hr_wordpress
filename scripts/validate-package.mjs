#!/usr/bin/env node

/**
 * NDS HR — WordPress Plugin Package Validator
 *
 * Programmatically validates the generated ZIP artifact:
 * - Checks top-level directory is strictly nds-hr/
 * - Checks nds-hr/nds-hr.php exists at root of archive
 * - Checks standard WordPress plugin headers
 * - Checks all critical files and directories exist inside the ZIP
 * - Enforces zero leakage of source files, dev configs, or secrets
 */

import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const ROOT_DIR = path.resolve(__dirname, '..');

// Allow passing custom zip path as argv[2]
let zipPath = process.argv[2];

if (!zipPath) {
  // Find latest nds-hr-v*.zip in dist/ or public/
  const distDir = path.join(ROOT_DIR, 'dist');
  if (fs.existsSync(distDir)) {
    const files = fs.readdirSync(distDir).filter(f => f.startsWith('nds-hr-v') && f.endsWith('.zip'));
    if (files.length > 0) {
      // Sort to get newest
      files.sort().reverse();
      zipPath = path.join(distDir, files[0]);
    }
  }
}

if (!zipPath || !fs.existsSync(zipPath)) {
  console.error(' [ERROR] No ZIP file found to validate! Provide a path or build the package first.');
  process.exit(1);
}

console.log('------------------------------------------------------------');
console.log(' NDS HR: Validating WordPress Plugin Package');
console.log(' Archive target:', zipPath);
console.log(' Archive size:', (fs.statSync(zipPath).size / 1024).toFixed(2), 'KB');
console.log('------------------------------------------------------------');

// List entries from ZIP using unzip or python3 fallback
let entries = [];
try {
  const output = execSync(`unzip -Z -1 "${zipPath}"`, { encoding: 'utf8' });
  entries = output.split('\n').map(s => s.trim()).filter(Boolean);
} catch (e) {
  // Fallback to python3 zipfile
  try {
    const pyCmd = `python3 -c "import zipfile, sys; z = zipfile.ZipFile(sys.argv[1]); [print(n) for n in z.namelist()]" "${zipPath}"`;
    const output = execSync(pyCmd, { encoding: 'utf8' });
    entries = output.split('\n').map(s => s.trim()).filter(Boolean);
  } catch (pyErr) {
    console.error(' [ERROR] Could not read zip entries:', pyErr.message);
    process.exit(1);
  }
}

if (entries.length === 0) {
  console.error(' [FAIL] ZIP archive is empty!');
  process.exit(1);
}

let errors = [];
let warnings = [];

// 1. Validate every entry is inside 'nds-hr/'
const nonPluginEntries = entries.filter(e => !e.startsWith('nds-hr/'));
if (nonPluginEntries.length > 0) {
  errors.push(`Found ${nonPluginEntries.length} entries outside the 'nds-hr/' root: ${nonPluginEntries.slice(0, 5).join(', ')}`);
}

// 2. Validate nds-hr/nds-hr.php exists directly at ZIP_ROOT/nds-hr/nds-hr.php
const hasMainFile = entries.includes('nds-hr/nds-hr.php');
if (!hasMainFile) {
  errors.push('CRITICAL: nds-hr/nds-hr.php is missing from the ZIP root!');
}

// 3. Check for disallowed / leaked development files
const forbiddenPatterns = [
  /^node_modules/,
  /\/node_modules/,
  /^\.git/,
  /\/\.git/,
  /^\.github/,
  /\/\.github/,
  /\.env/,
  /\.DS_Store/,
  /package\.json$/,
  /package-lock\.json$/,
  /bun\.lock$/,
  /tsconfig\.json$/,
  /vite\.config\.ts$/,
  /metadata\.json$/,
  /^src\//,
  /\/src\//,
  /^public\//,
  /\/public\//,
  /\.log$/,
  /\.tmp$/,
];

const forbiddenFound = [];
for (const entry of entries) {
  for (const pattern of forbiddenPatterns) {
    if (pattern.test(entry)) {
      forbiddenFound.push(entry);
      break;
    }
  }
}

if (forbiddenFound.length > 0) {
  errors.push(`Forbidden development/secret files detected in ZIP: ${forbiddenFound.join(', ')}`);
}

// 4. Verify required core plugin components inside the ZIP
const requiredPluginFiles = [
  'nds-hr/nds-hr.php',
  'nds-hr/uninstall.php',
  'nds-hr/readme.txt',
  'nds-hr/includes/class-plugin.php',
  'nds-hr/includes/class-router.php',
  'nds-hr/includes/class-auth.php',
  'nds-hr/includes/class-database.php',
  'nds-hr/includes/class-roles.php',
  'nds-hr/includes/class-permissions.php',
  'nds-hr/includes/class-security.php',
  'nds-hr/includes/class-session.php',
  'nds-hr/includes/class-audit-logger.php',
  'nds-hr/includes/class-i18n.php',
  'nds-hr/admin/class-admin.php',
  'nds-hr/admin/dashboard/class-admin-dashboard.php',
  'nds-hr/admin/employees/class-admin-employees.php',
  'nds-hr/admin/settings/class-admin-roles.php',
  'nds-hr/employee/class-employee-portal.php',
  'nds-hr/employee/dashboard/class-employee-dashboard.php',
  'nds-hr/employee/profile/class-employee-profile.php',
  'nds-hr/modules/employees/class-employees.php',
  'nds-hr/modules/employees/class-employee-service.php',
  'nds-hr/modules/employees/class-employee-repository.php',
  'nds-hr/templates/admin/admin-layout.php',
  'nds-hr/templates/admin/dashboard.php',
  'nds-hr/templates/admin/employees-list.php',
  'nds-hr/templates/admin/employee-form.php',
  'nds-hr/templates/admin/employee-view.php',
  'nds-hr/templates/admin/roles-permissions.php',
  'nds-hr/templates/admin/audit-logs.php',
  'nds-hr/templates/employee/portal-layout.php',
  'nds-hr/templates/employee/dashboard.php',
  'nds-hr/templates/employee/profile.php',
  'nds-hr/templates/employee/login-required.php',
  'nds-hr/templates/employee/unlinked-account.php',
  'nds-hr/templates/login/login.php',
  'nds-hr/templates/login/setup.php',
  'nds-hr/templates/login/reset-password.php',
  'nds-hr/assets/css/admin.css',
  'nds-hr/assets/css/admin-rtl.css',
  'nds-hr/assets/css/employee-portal.css',
  'nds-hr/assets/css/employee-portal-rtl.css',
  'nds-hr/assets/js/admin.js',
  'nds-hr/assets/js/employee-portal.js',
  'nds-hr/languages/nds-hr.pot',
];

for (const req of requiredPluginFiles) {
  if (!entries.includes(req)) {
    errors.push(`Missing required file in package: ${req}`);
  }
}

// 5. Read main file contents from ZIP and verify header
let mainFileContent = '';
try {
  mainFileContent = execSync(`unzip -p "${zipPath}" "nds-hr/nds-hr.php"`, { encoding: 'utf8' });
} catch (e) {
  try {
    const pyCmd = `python3 -c "import zipfile, sys; z = zipfile.ZipFile(sys.argv[1]); print(z.read('nds-hr/nds-hr.php').decode('utf-8', errors='ignore'))" "${zipPath}"`;
    mainFileContent = execSync(pyCmd, { encoding: 'utf8' });
  } catch (pyErr) {
    errors.push('Failed to extract nds-hr/nds-hr.php from ZIP for header validation.');
  }
}

if (mainFileContent) {
  const hasPluginName = /Plugin Name:\s*NDS HR/i.test(mainFileContent);
  const hasVersion = /Version:\s*([0-9.]+)/i.test(mainFileContent);
  const hasTextDomain = /Text Domain:\s*nds-hr/i.test(mainFileContent);

  if (!hasPluginName) errors.push('Missing "Plugin Name: NDS HR" in zipped nds-hr.php');
  if (!hasVersion) errors.push('Missing "Version: X.Y.Z" in zipped nds-hr.php');
  if (!hasTextDomain) errors.push('Missing "Text Domain: nds-hr" in zipped nds-hr.php');
}

// Output Report
console.log(` Package File Count: ${entries.length} files`);
console.log(` Root Directory: nds-hr/ (Direct WordPress Plugin folder format)`);

if (warnings.length > 0) {
  console.log('\n Warnings:');
  warnings.forEach(w => console.warn(` [WARN] ${w}`));
}

if (errors.length > 0) {
  console.error('\n Validation FAILED with errors:');
  errors.forEach(err => console.error(` [FAIL] ${err}`));
  process.exit(1);
}

console.log('\n [SUCCESS] ZIP archive passed all WordPress plugin packaging criteria!');
console.log(' - Validated structure: nds-hr/ at root level');
console.log(' - Validated entrypoint: nds-hr/nds-hr.php present');
console.log(' - Validated WordPress plugin headers intact');
console.log(' - Validated 0 development/secret leaks');
console.log(' - Ready for upload in WordPress > Plugins > Add New > Upload Plugin');
console.log('------------------------------------------------------------');
