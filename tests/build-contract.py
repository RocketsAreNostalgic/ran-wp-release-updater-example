#!/usr/bin/env python3
"""Exercise builder input rejection without a Composer install."""

import pathlib
import subprocess
import sys
import tempfile


root = pathlib.Path(__file__).resolve().parent.parent
command = [sys.executable, "scripts/build-plugin-zip.py", "--output-dir", tempfile.mkdtemp(), "--fixture-mode", "--version", "0.2.0/unsafe"]
result = subprocess.run(command, cwd=root, capture_output=True, text=True)
if result.returncode == 0 or "supported three-part SemVer" not in result.stderr:
	raise SystemExit("Builder accepted an unsafe synthetic version.")

print("Build input contract passed.")
