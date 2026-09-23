#!/usr/bin/env node

/**
 * NDS HR — WordPress Plugin Package Validator
 *
 * Programmatically validates the generated ZIP artifact against exact WordPress Core
 * specifications:
 * 1. Checks top-level directory is strictly nds-hr/
 * 2. Checks nds-hr/nds-hr.php exists at root of archive
 * 3. Simulates WordPress Plugin_Upgrader::check_package() extraction & discovery
 * 4. Simulates WordPress get_plugin_data() header extraction
 * 5. Enforces zero leakage of source files, dev configs, or secrets
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

// List entries from ZIP using python3 zipfile
let entries = [];
try {
  const pyCmd = `python3 -c "import zipfile, sys; z = zipfile.ZipFile(sys.argv[1]); [print(n) for n in z.namelist()]" "${zipPath}"`;
  const output = execSync(pyCmd, { encoding: 'utf8' });
  entries = output.split('\n').map(s => s.trim()).filter(Boolean);
} catch (pyErr) {
  console.error(' [ERROR] Could not read zip entries:', pyErr.message);
  process.exit(1);
}

if (entries.length === 0) {
  console.error(' [FAIL] ZIP archive is empty!');
  process.exit(1);
}

let errors = [];

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
  'templates/admin/dashboard.php',
  'templates/admin/employees-list.php',
  'templates/employee/portal-layout.php',
  'templates/login/login.php',
  'templates/login/setup.php',
  'templates/login/reset-password.php',
  'assets/css/admin.css',
  'assets/css/employee-portal.css',
  'assets/js/admin.js',
  'assets/js/employee-portal.js',
  'languages/nds-hr.pot',
];

for (const req of requiredPluginFiles) {
  const fullReq = req.startsWith('nds-hr/') ? req : `nds-hr/${req}`;
  if (!entries.includes(fullReq)) {
    errors.push(`Missing required file in package: ${fullReq}`);
  }
}

// 5. Test WordPress Core Plugin_Upgrader simulation directly on this ZIP
const pySimulation = `
import zipfile, tempfile, os, shutil, re, sys

zip_path = sys.argv[1]
temp_dir = tempfile.mkdtemp()
try:
    with zipfile.ZipFile(zip_path, 'r') as z:
        z.extractall(temp_dir)

    root_entries = os.listdir(temp_dir)
    working_dir = ''
    if len(root_entries) == 1 and os.path.isdir(os.path.join(temp_dir, root_entries[0])):
        working_dir = root_entries[0]

    scan_dir = os.path.join(temp_dir, working_dir) if working_dir else temp_dir
    scan_files = os.listdir(scan_dir)
    php_files = [f for f in scan_files if f.endswith('.php') and os.path.isfile(os.path.join(scan_dir, f))]

    if not php_files:
        print('WP_CORE_FAIL: No PHP files found in plugin root folder')
        sys.exit(1)

    plugin_info = None
    for pf in php_files:
        with open(os.path.join(scan_dir, pf), 'r', encoding='utf-8', errors='ignore') as f:
            header_str = f.read(8192)
        m = re.search(r'^[ \\t\\/*#@]*Plugin Name:(.*)$', header_str, re.MULTILINE | re.IGNORECASE)
        if m:
            v_match = re.search(r'^[ \\t\\/*#@]*Version:(.*)$', header_str, re.MULTILINE | re.IGNORECASE)
            version = v_match.group(1).strip() if v_match else 'Unknown'
            plugin_info = (pf, m.group(1).strip(), version)
            break

    if not plugin_info:
        print('WP_CORE_FAIL: No file contains a valid Plugin Name header')
        sys.exit(1)

    print(f'WP_CORE_SUCCESS: Found plugin file \"{plugin_info[0]}\" with Plugin Name \"{plugin_info[1]}\" (Version {plugin_info[2]})')
finally:
    shutil.rmtree(temp_dir)
`;

try {
  const result = execSync(`python3 -c "${pySimulation.replace(/"/g, '\\"')}" "${zipPath}"`, { encoding: 'utf8' });
  console.log(` [PASS] WordPress Core Simulation: ${result.trim()}`);
} catch (simErr) {
  errors.push(`WordPress Core Upgrader Simulation Failed: ${simErr.stdout || simErr.message}`);
}

console.log(` Package File Count: ${entries.length} entries`);
console.log(` Root Directory: nds-hr/`);

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
console.log(' - Compatible with WordPress -> Plugins -> Add New -> Upload Plugin');
console.log('------------------------------------------------------------');
