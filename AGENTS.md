# RAN WP Release Updater Example

This public developer demo follows the RAN `wordpress-plugin` quality profile.

- Require PHP `>=8.4 <8.6` and WordPress `>=7.0`.
- Keep the plugin intentionally small: one root entrypoint, no controllers,
  settings, assets, or updater wrapper.
- Register `ran/wp-release-updater` at top level before `plugins_loaded`.
  Registration is passive; never add an installer, HTTP request, or manual offer.
- `composer check` and `pnpm check` are the local quality gates.
- Release Please solely owns the plugin-header/package/README version, tag,
  changelog, and GitHub Release. A release is not WordPress.org publication or
  deployment.
- The ZIP builder is deterministic and only packages its explicit runtime
  allowlist plus Composer's no-dev vendor tree. Do not publish from a script.

Keep all committed material suitable for public disclosure. Credentials must
never enter source, tests, archives, URLs, diagnostics, or manifests.
