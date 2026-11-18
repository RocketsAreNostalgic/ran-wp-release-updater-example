# RAN WP Release Updater Example

This is a minimal plugin integration for
[`ran/wp-release-updater`](https://github.com/RocketsAreNostalgic/ran-wp-release-updater).
It demonstrates a passive, early registration of a GitHub-backed native
WordPress update target; it does not add a settings screen, custom installer,
or release publisher.

It requires WordPress 7.0 or later and PHP 8.4–8.5. The main plugin file loads
and registers the updater before `plugins_loaded`; registration is passive.

## Bundled updater

The lock binds `ran/wp-release-updater` to `1.0.0-beta.21` (runtime protocol
11); the Composer constraint is `~1.0.0-beta.21`. Its native status includes
`last_native_outcome`, and binding writes use schema 2. Older copies cannot read
those rows; incompatible runtime protocols fail closed in either load order. Coordinate compatible bundled copies before
installation; changing load order does not resolve a protocol conflict.

See [testing and qualification](docs/testing.md) for what the local gates and
archive build prove, and the installed checks still required.

## Adapt it

Change the plugin header and registration together:

- use your canonical `https://github.com/owner/repository` as both Plugin URI
  and Update URI;
- use GitHub's numeric repository ID, not the `owner/repository` name;
- keep `channel: 'stable'` for stable tags, and choose `manual`, `automatic`,
  `forced-off`, or `disabled` deliberately for `update_policy`.

`automatic` only permits automatic installation after the updater's normal
immutable/provenanced-release verification succeeds. It never means “install
anything newer”. Keep credentials request-local and outside the distributed ZIP.

## Development

```sh
composer install
pnpm install --frozen-lockfile
composer check
pnpm check
python3 scripts/build-plugin-zip.py --output-dir build
```

The builder emits a deterministic one-root ZIP and `build/manifest.json` with
its SHA-256. The ZIP includes the no-dev updater dependency under `vendor`, so
production WordPress sites do not need Composer. Release Please owns version
updates. No release, publication, deployment, or WordPress.org action is
performed by the builder.
