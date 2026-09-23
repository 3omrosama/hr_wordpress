#!/usr/bin/env node

/**
 * NDS HR — PHP Syntax Linter
 *
 * Runs `php -l` across all PHP files in the nds-hr/ directory.
 * If php is not available on the current machine (e.g. lightweight node container),
 * it performs a clean scan and exits gracefully while providing full verification
 * in GitHub Actions CI where PHP is installed.
 */

import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const ROOT_DIR = path.resolve(__dirname, '..');
const PLUGIN_DIR = path.join(ROOT_DIR, 'nds-hr');

console.log('------------------------------------------------------------');
console.log(' NDS HR: Running PHP Syntax Validation');
console.log('------------------------------------------------------------');

// Check if php binary is available
let hasPhp = false;
try {
  const version = execSync('php -v', { encoding: 'utf8', stdio: ['pipe', 'pipe', 'ignore'] });
  console.log(` PHP binary detected: ${version.split('\n')[0]}`);
  hasPhp = true;
} catch (e) {
  console.log(' [NOTICE] PHP CLI not detected in local Node environment.');
  console.log(' (Strict `php -l` syntax validation runs in GitHub Actions CI via setup-php)');
}

// Collect all PHP files
const phpFiles = [];
function findPhp(dir) {
  const list = fs.readdirSync(dir, { withFileTypes: true });
  for (const item of list) {
    const full = path.join(dir, item.name);
    if (item.isDirectory()) {
      findPhp(full);
    } else if (item.isFile() && item.name.endsWith('.php')) {
      phpFiles.push(full);
    }
  }
}
findPhp(PLUGIN_DIR);

console.log(` Found ${phpFiles.length} PHP source files in nds-hr/`);

if (hasPhp) {
  let failCount = 0;
  for (const file of phpFiles) {
    try {
      execSync(`php -l "${file}"`, { stdio: ['pipe', 'pipe', 'pipe'] });
    } catch (err) {
      console.error(` [FAIL] Syntax error in ${path.relative(ROOT_DIR, file)}:`);
      console.error(err.stderr ? err.stderr.toString() : err.message);
      failCount++;
    }
  }

  if (failCount > 0) {
    console.error(` [ERROR] ${failCount} PHP file(s) failed syntax validation!`);
    process.exit(1);
  }

  console.log(` [SUCCESS] All ${phpFiles.length} PHP files passed "php -l" syntax checks with zero errors.`);
} else {
  // Check basic file integrity
  let issues = 0;
  for (const file of phpFiles) {
    const content = fs.readFileSync(file, 'utf8');
    if (!content.trim().startsWith('<?php')) {
      console.error(` [FAIL] File missing <?php opening tag: ${path.relative(ROOT_DIR, file)}`);
      issues++;
    }
  }
  if (issues > 0) {
    console.error(` [ERROR] ${issues} file(s) failed static check.`);
    process.exit(1);
  }
  console.log(` [PASS] Static structural check passed for ${phpFiles.length} PHP files.`);
}
console.log('------------------------------------------------------------');
