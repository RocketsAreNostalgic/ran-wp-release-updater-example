#!/usr/bin/env python3
"""Build the installable example plugin without changing the checkout."""

from __future__ import annotations

import argparse
import hashlib
import json
import pathlib
import re
import shutil
import subprocess
import tempfile
import zipfile


ROOT = pathlib.Path(__file__).resolve().parent.parent
PLUGIN_SLUG = "ran-wp-release-updater-example"
CONTENTS_FILE = ROOT / "release-contents.txt"
SEMVER = re.compile(r"^(?:v)?[0-9]+\.[0-9]+\.[0-9]+(?:-[0-9A-Za-z.-]+)?$")


def read_version(path: pathlib.Path) -> str:

	for line in path.read_text().splitlines():
		if line.lstrip().startswith("* Version:"):
			return line.split(":", 1)[1].split("//", 1)[0].strip()
	raise RuntimeError("Plugin header has no Version.")


def source_version() -> str:

	return read_version(ROOT / "ran-wp-release-updater-example.php")


def validate_production_versions() -> None:

	version = source_version()
	package_version = json.loads((ROOT / "package.json").read_text())["version"]
	release_version = json.loads((ROOT / ".release-please-manifest.json").read_text())["."]
	if {version, package_version, release_version} != {version}:
		raise RuntimeError("Plugin header, package.json, and Release Please manifest versions must agree.")


def release_contents() -> list[str]:

	entries = [line.strip() for line in CONTENTS_FILE.read_text().splitlines() if line.strip() and not line.startswith("#")]
	if entries != ["ran-wp-release-updater-example.php", "LICENSE"]:
		raise RuntimeError("release-contents.txt must contain only explicit source runtime files.")
	return entries


def copy_runtime(destination: pathlib.Path, contents: list[str]) -> None:

	for relative in contents:
		entry = ROOT / relative.rstrip("/")
		if not entry.exists() or entry.is_symlink():
			raise RuntimeError(f"Invalid release entry: {relative}")
		target = destination / entry.name
		if entry.is_dir():
			for nested in entry.rglob("*"):
				if nested.is_symlink() or not (nested.is_file() or nested.is_dir()):
					raise RuntimeError(f"Invalid release entry: {nested}")
			shutil.copytree(entry, target)
		else:
			shutil.copy2(entry, target)


def replace_version(plugin_file: pathlib.Path, version: str) -> None:

	contents = plugin_file.read_text()
	old_version = read_version(plugin_file)
	plugin_file.write_text(contents.replace(f"Version: {old_version}", f"Version: {version}", 1))


def composer_install(staging_root: pathlib.Path, updater_path: pathlib.Path | None) -> None:

	command = ["composer", "install", "--no-dev", "--prefer-dist", "--no-interaction", "--no-progress", "--optimize-autoloader"]
	if updater_path is not None:
		updater_path = updater_path.resolve()
		updater_runtime = updater_path / "runtime-copy.json"
		if not updater_runtime.is_file():
			raise RuntimeError("Candidate updater must contain runtime-copy.json.")
		updater_version = json.loads(updater_runtime.read_text())["package_version"]
		candidate_export = staging_root.parent / "updater-candidate"
		candidate_export.mkdir()
		for candidate_entry in ("bootstrap.php", "runtime.php", "runtime-copy.json", "composer.json", "LICENSE", "src"):
			source = updater_path / candidate_entry
			if not source.exists() or source.is_symlink():
				raise RuntimeError(f"Candidate updater is missing safe runtime entry: {candidate_entry}")
			if source.is_dir():
				for nested in source.rglob("*"):
					if nested.is_symlink() or not (nested.is_file() or nested.is_dir()):
						raise RuntimeError(f"Candidate updater has unsafe runtime entry: {nested}")
				shutil.copytree(source, candidate_export / candidate_entry)
			else:
				shutil.copy2(source, candidate_export / candidate_entry)
		composer_file = staging_root / "composer.json"
		composer = json.loads(composer_file.read_text())
		composer["require"]["ran/wp-release-updater"] = updater_version
		composer["repositories"] = [{"type": "path", "url": str(candidate_export), "options": {"symlink": False, "versions": {"ran/wp-release-updater": updater_version}}}] + composer.get("repositories", [])
		composer_file.write_text(json.dumps(composer, indent=2) + "\n")
		command[1] = "update"
		command[2:2] = ["ran/wp-release-updater"]
	subprocess.run(command, cwd=staging_root, check=True)
	for forbidden in (".git", ".workspaces", "tests", "scripts", "composer.lock"):
		if (staging_root / "vendor/ran/wp-release-updater" / forbidden).exists():
			raise RuntimeError(f"Updater vendor package unexpectedly contains {forbidden}.")
	if updater_path is not None:
		installed_runtime = json.loads((staging_root / "vendor/ran/wp-release-updater/runtime-copy.json").read_text())
		if installed_runtime["package_revision"] != json.loads((updater_path / "runtime-copy.json").read_text())["package_revision"]:
			raise RuntimeError("Candidate updater runtime revision was not installed.")
		installed_root = staging_root / "vendor/ran/wp-release-updater"
		for expected in sorted(path for path in candidate_export.rglob("*") if path.is_file()):
			relative = expected.relative_to(candidate_export)
			installed = installed_root / relative
			if not installed.is_file() or hashlib.sha256(expected.read_bytes()).digest() != hashlib.sha256(installed.read_bytes()).digest():
				raise RuntimeError(f"Candidate updater byte mismatch: {relative}")


def write_archive(staging_root: pathlib.Path, output: pathlib.Path) -> None:

	with zipfile.ZipFile(output, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
		for source in sorted(path for path in staging_root.rglob("*") if path.is_file()):
			if source.is_symlink():
				raise RuntimeError(f"Refusing symlink in release archive: {source}")
			relative = source.relative_to(staging_root).as_posix()
			if relative not in {"ran-wp-release-updater-example.php", "LICENSE"} and not relative.startswith("vendor/ran/wp-release-updater/"):
				continue
			info = zipfile.ZipInfo(f"{PLUGIN_SLUG}/{relative}", date_time=(1980, 1, 1, 0, 0, 0))
			info.compress_type = zipfile.ZIP_DEFLATED
			info.external_attr = 0o100644 << 16
			archive.writestr(info, source.read_bytes(), compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)


def main() -> None:

	parser = argparse.ArgumentParser()
	parser.add_argument("--output-dir", type=pathlib.Path, required=True)
	parser.add_argument("--version")
	parser.add_argument("--updater-path", type=pathlib.Path)
	parser.add_argument("--fixture-mode", action="store_true")
	arguments = parser.parse_args()
	production_version = source_version()
	version = arguments.version or production_version
	if not SEMVER.fullmatch(version):
		raise RuntimeError("Version must be a supported three-part SemVer value.")
	if version != production_version and not (arguments.fixture_mode or arguments.updater_path):
		raise RuntimeError("Synthetic versions require --fixture-mode or --updater-path.")
	if version == production_version:
		validate_production_versions()

	arguments.output_dir.mkdir(parents=True, exist_ok=True)
	archive = arguments.output_dir / f"{PLUGIN_SLUG}-{version}.zip"
	with tempfile.TemporaryDirectory(prefix="ran-wp-release-updater-example-") as temporary:
		temporary_root = pathlib.Path(temporary)
		comparison_archive = temporary_root / "comparison.zip"
		for stage_name, stage_archive in (("first", archive), ("second", comparison_archive)):
			staging_root = temporary_root / stage_name / PLUGIN_SLUG
			staging_root.mkdir(parents=True)
			copy_runtime(staging_root, release_contents())
			shutil.copy2(ROOT / "composer.json", staging_root / "composer.json")
			shutil.copy2(ROOT / "composer.lock", staging_root / "composer.lock")
			replace_version(staging_root / "ran-wp-release-updater-example.php", version)
			composer_install(staging_root, arguments.updater_path)
			write_archive(staging_root, stage_archive)
		if archive.read_bytes() != comparison_archive.read_bytes():
			raise RuntimeError("Independent archive builds are not deterministic.")

	digest = hashlib.sha256(archive.read_bytes()).hexdigest()
	manifest = {"archive": archive.name, "sha256": digest, "size": archive.stat().st_size, "version": version}
	(arguments.output_dir / "manifest.json").write_text(json.dumps(manifest, indent=2, sort_keys=True) + "\n")
	print(json.dumps(manifest, sort_keys=True))


if __name__ == "__main__":
	main()
