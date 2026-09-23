#!/usr/bin/env python3
"""
NDS HR — Production ZIP Archiver for WordPress Plugin
"""

import zipfile
import os
import sys
import time

def package_plugin(src_dir, output_zip):
    os.makedirs(os.path.dirname(output_zip), exist_ok=True)
    forbidden_suffixes = ('.log', '.tmp', '.bak', '.swp', '.DS_Store', 'Thumbs.db')

    with zipfile.ZipFile(output_zip, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as z:
        # 1. Add top-level nds-hr/ directory entry explicitly
        d_info = zipfile.ZipInfo('nds-hr/')
        d_info.external_attr = 0o40755 << 16  # drwxr-xr-x
        d_info.date_time = time.localtime(time.time())[:6]
        z.writestr(d_info, '')

        # 2. Walk directory deterministically
        for root, dirs, files in os.walk(src_dir):
            dirs.sort()
            files.sort()

            rel_root = os.path.relpath(root, src_dir).replace('\\', '/')
            if rel_root == '.':
                zip_base = 'nds-hr'
            else:
                zip_base = f'nds-hr/{rel_root}'

            # Add directory entries
            for d in dirs:
                if d.startswith('.') or d == 'node_modules':
                    continue
                dir_zip_path = f'{zip_base}/{d}/'
                info = zipfile.ZipInfo(dir_zip_path)
                info.external_attr = 0o40755 << 16
                info.date_time = time.localtime(time.time())[:6]
                z.writestr(info, '')

            # Add file entries
            for f in files:
                if f.startswith('.'):
                    continue
                if any(f.endswith(sfx) for sfx in forbidden_suffixes):
                    continue

                file_full = os.path.join(root, f)
                file_zip_path = f'{zip_base}/{f}'

                with open(file_full, 'rb') as fh:
                    data = fh.read()

                info = zipfile.ZipInfo(file_zip_path)
                info.external_attr = 0o100644 << 16  # -rw-r--r--
                info.date_time = time.localtime(os.path.getmtime(file_full))[:6]
                z.writestr(info, data, compress_type=zipfile.ZIP_DEFLATED)

if __name__ == '__main__':
    if len(sys.argv) < 3:
        print('Usage: make-zip.py <src_dir> <output_zip>')
        sys.exit(1)
    package_plugin(sys.argv[1], sys.argv[2])
