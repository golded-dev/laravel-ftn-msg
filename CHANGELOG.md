# Changelog

Notable changes to `golded-dev/laravel-ftn-msg`.

This project uses semantic versioning.

## 1.1.0 - 2026-04-29

### Added

- Attach parsed FTN control-line metadata to returned `ParsedMessage` objects.
- Attach message provenance with `.MSG` source path and message number.
- Require `golded-dev/laravel-ftn` v1.2.0 in the lockfile.

## 1.0.0 - 2026-04-25

Initial stable release.

### Added

- Add `.MSG` message-base reader for numeric `.msg` and `.MSG` files.
- Add parsing for `.MSG` header names, subject, posted date, raw attributes, and message body.
- Add charset detection through `golded-dev/laravel-ftn`.
- Add `MSGID` extraction with stable synthetic IDs as fallback.
- Add Pest, PHPStan, and Rector quality gates.
- Add public package documentation, security policy, code of conduct, archive hygiene, and CI workflow.
