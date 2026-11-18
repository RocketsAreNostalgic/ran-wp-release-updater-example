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

## Protocol 11 adoption qualification

The lock binds release updater `1.0.0-beta.21`, runtime protocol 11. A normal
build uses that committed lock, not `--updater-path`. Inspect the ZIP's
`vendor/ran/wp-release-updater/runtime-copy.json` for the package version and
protocol, and compare the lock's source and dist references to the published
tag. The builder installs from the lock with `--no-dev` twice and compares the
resulting ZIPs byte for byte.

Quality CI also installs and activates the exact archive on WordPress 7.0.
That standalone smoke check does not qualify coexistence with another plugin or
a native self-update.

Issue [#7](https://github.com/RocketsAreNostalgic/ran-wp-release-updater-example/issues/7)
remains open for publication and installed qualification. The RAN Booster Core
release carrying protocol 11 has not yet been published. After it is available,
check the released example and Core together on an installed site in both load
orders, with release updating active, and prove the example's own native
self-update. These acceptance points remain unproven by the dependency change,
local gates, archive inspection, and standalone CI activation.
