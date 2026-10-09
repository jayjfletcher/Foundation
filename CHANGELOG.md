# Changelog

## [Unreleased](https://github.com/Refactor-Circus/Keystone/commits/main)

### Breaking

- Renamed Foundation to Keystone and moved to the Refactor Circus organisation: the package is now `refactor-circus/keystone` with the PHP namespace `RefactorCircus\Keystone` (it was `jayi/foundation` and `JayI\Foundation`), and its provider is `KeystoneServiceProvider`. Update `composer.json` requirements and `use` statements. Old class names are not kept as aliases, so stored values written under them - polymorphic `*_type` columns, audit subjects, Pennant feature names - need updating to the new names.

### Added

- The shared runtime for the Refactor Circus suite: the `Action` base, the action and model event contracts, `DispatchesModelEvents`, the package registry, base service providers, HTTP and MCP request bases, the MCP tool and server bases, the Cortex integration, `PackageException`, the `Policy` base, and `Surface`.
- Audit seams for refactor-circus/keen: `AuditTrail` (bound to `NullAuditTrail` until Keen is installed), `Auditable`, `AuditHooks`, and a history endpoint and MCP tool for every package.

### Changed

- Requires PHP 8.5 or later.
