#!/usr/bin/env node

/**
 * NDS HR — WordPress Plugin Production Build Step
 *
 * Validates the core plugin tree, dependencies, asset integrity, and required
 * runtime files before packaging.
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const ROOT_DIR = path.resolve(__dirname, '..');
const PLUGIN_DIR = path.join(ROOT_DIR, 'nds-hr');

console.log('------------------------------------------------------------');
console.log(' NDS HR: Building WordPress Plugin');
console.log(' Plugin source:', PLUGIN_DIR);
console.log('------------------------------------------------------------');

// 1. Verify plugin root exists
if (!fs.existsSync(PLUGIN_DIR)) {
  console.error(' [ERROR] Plugin directory not found at:', PLUGIN_DIR);
  process.exit(1);
}

// 2. Verify main plugin entrypoint
const mainFile = path.join(PLUGIN_DIR, 'nds-hr.php');
if (!fs.existsSync(mainFile)) {
  console.error(' [ERROR] Main plugin entrypoint nds-hr.php is missing!');
  process.exit(1);
}

const mainContent = fs.readFileSync(mainFile, 'utf8');

// Check WordPress plugin header markers
const requiredHeaders = [
  'Plugin Name:',
  'Description:',
  'Version:',
  'Author:',
  'Text Domain:',
];

for (const header of requiredHeaders) {
  if (!mainContent.includes(header)) {
    console.error(` [ERROR] nds-hr.php is missing required WordPress header: "${header}"`);
    process.exit(1);
  }
}

// 3. Verify critical directories and files
const criticalFiles = [
  'includes/class-plugin.php',
  'includes/class-router.php',
  'includes/class-auth.php',
  'includes/class-database.php',
  'includes/class-roles.php',
  'includes/class-permissions.php',
  'includes/class-security.php',
  'includes/class-session.php',
  'includes/class-audit-logger.php',
  'includes/class-i18n.php',
  'admin/class-admin.php',
  'admin/dashboard/class-admin-dashboard.php',
  'admin/employees/class-admin-employees.php',
  'admin/settings/class-admin-roles.php',
  'employee/class-employee-portal.php',
  'employee/dashboard/class-employee-dashboard.php',
  'employee/profile/class-employee-profile.php',
  'modules/employees/class-employees.php',
  'modules/employees/class-employee-service.php',
  'modules/employees/class-employee-repository.php',
  'templates/admin/admin-layout.php',
  'templates/admin/dashboard.php',
  'templates/admin/employees-list.php',
  'templates/employee/portal-layout.php',
  'templates/login/login.php',
  'templates/login/setup.php',
  'templates/login/reset-password.php',
  'assets/css/admin.css',
  'assets/css/admin-rtl.css',
  'assets/css/employee-portal.css',
  'assets/css/employee-portal-rtl.css',
  'assets/js/admin.js',
  'assets/js/employee-portal.js',
  'languages/nds-hr.pot',
  'readme.txt',
  'uninstall.php',
];

let missingCount = 0;
for (const relPath of criticalFiles) {
  const fullPath = path.join(PLUGIN_DIR, relPath);
  if (!fs.existsSync(fullPath)) {
    console.error(` [ERROR] Missing critical plugin file: ${relPath}`);
    missingCount++;
  }
}

if (missingCount > 0) {
  console.error(` [FAIL] Build failed: ${missingCount} critical file(s) missing.`);
  process.exit(1);
}

// 4. Validate PHP tags and file integrity (no UTF-8 BOM, valid opening tags)
const allPhpFiles = [];
function collectPhpFiles(dir) {
  const items = fs.readdirSync(dir, { withFileTypes: true });
  for (const item of items) {
    const full = path.join(dir, item.name);
    if (item.isDirectory()) {
      collectPhpFiles(full);
    } else if (item.isFile() && item.name.endsWith('.php')) {
      allPhpFiles.push(full);
    }
  }
}
collectPhpFiles(PLUGIN_DIR);

let syntaxIssues = 0;
for (const file of allPhpFiles) {
  const content = fs.readFileSync(file, 'utf8');
  if (content.charCodeAt(0) === 0xFEFF) {
    console.warn(` [WARN] UTF-8 BOM detected in ${path.relative(ROOT_DIR, file)}, removing BOM.`);
    fs.writeFileSync(file, content.slice(1), 'utf8');
  }
  if (!content.trim().startsWith('<?php')) {
    console.error(` [ERROR] File does not begin with <?php: ${path.relative(ROOT_DIR, file)}`);
    syntaxIssues++;
  }
}

if (syntaxIssues > 0) {
  console.error(` [FAIL] ${syntaxIssues} PHP file(s) failed integrity check.`);
  process.exit(1);
}

console.log(` [PASS] Verified ${allPhpFiles.length} PHP source files.`);
console.log(` [PASS] All critical plugin assets and templates are in place.`);
console.log(' WordPress plugin build check completed successfully.');
console.log('------------------------------------------------------------');
