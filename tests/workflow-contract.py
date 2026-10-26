#!/usr/bin/env python3
"""Pin the repository-specific Quality and Profile B caller contracts."""

import pathlib


root = pathlib.Path(__file__).resolve().parent.parent
quality = (root / ".github/workflows/quality.yml").read_text()
release = (root / ".github/workflows/release-please.yml").read_text()
config = (root / "release-please-config.json").read_text()

for expected in (
    "name: Quality",
    "pull_request:",
    "workflow_dispatch:",
    "contents: read",
    "persist-credentials: false",
    "python3 scripts/build-plugin-zip.py --output-dir build",
    "ran-profile-b-promotion.json",
    "ran-wp-release-updater-example-promotion-${{ github.run_id }}-${{ github.run_attempt }}",
    "wp plugin install \"$archive\"",
):
    if expected not in quality:
        raise SystemExit(f"Quality workflow lacks: {expected}")

for expected in (
    "RocketsAreNostalgic/.github/.github/workflows/release-profile-b.yml@593768db30a0101e940e85b9a084b2c773322785",
    "release-please--branches--main--components--ran-wp-release-updater-example",
    '"draft": true',
    '"force-tag-creation": true',
):
    if expected not in release + config:
        raise SystemExit(f"Release contract lacks: {expected}")

print("Workflow contract passed.")
