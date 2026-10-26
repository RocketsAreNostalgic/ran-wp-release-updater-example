# Testing and qualification

`composer check` proves the small source contract: syntax, coding standards,
PHPStan level 8 coverage, and the bootstrap contract. `pnpm check` proves the
repository formatting contract. Neither proves a published release, a WordPress
installation, or browser acceptance.

`python3 scripts/build-plugin-zip.py --output-dir build` deterministically
packages the currently checked-out source plus Composer's no-dev dependencies.
The accompanying manifest proves the local archive bytes only.

For the updater's release and native lifecycle requirements, see its
[integration guide](https://github.com/RocketsAreNostalgic/ran-wp-release-updater/blob/main/docs/integration.md).
Use the builder command above for local archive qualification; it is not proof
of a live update.

The builder can stage a candidate updater using `--updater-path` for isolated
qualification. That overrides only the temporary staging Composer repository;
it never changes the committed production lock. A live end-to-end native update
still requires owner-created immutable GitHub releases with one uploaded ZIP
asset and separate WordPress installation evidence.
