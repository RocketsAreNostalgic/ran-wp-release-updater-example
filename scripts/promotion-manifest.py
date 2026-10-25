#!/usr/bin/env python3
"""Write the exact Profile B promotion record for one built release asset."""

from __future__ import annotations

import argparse
import hashlib
import json
import pathlib
import subprocess


REPOSITORY = "RocketsAreNostalgic/ran-wp-release-updater-example"


def main() -> None:

	parser = argparse.ArgumentParser()
	parser.add_argument("--archive", type=pathlib.Path, required=True)
	parser.add_argument("--manifest", type=pathlib.Path, required=True)
	parser.add_argument("--output", type=pathlib.Path, required=True)
	arguments = parser.parse_args()

	build = json.loads(arguments.manifest.read_text())
	if build["archive"] != arguments.archive.name:
		raise RuntimeError("Build manifest archive does not match the selected asset.")
	digest = hashlib.sha256(arguments.archive.read_bytes()).hexdigest()
	if build["sha256"] != digest:
		raise RuntimeError("Build manifest digest does not match the selected asset.")
	commit = subprocess.check_output(("git", "rev-parse", "HEAD"), text=True).strip()
	if len(commit) != 40:
		raise RuntimeError("Promotion source is not an exact Git commit.")

	promotion = {
		"schema": "ran-profile-b-promotion",
		"schema_version": 1,
		"repository": REPOSITORY,
		"quality_commit": commit,
		"source_commit": commit,
		"tag": "v" + build["version"],
		"assets": [{"name": arguments.archive.name, "sha256": digest}],
	}
	arguments.output.write_text(json.dumps(promotion, indent=2) + "\n")


if __name__ == "__main__":
	main()
