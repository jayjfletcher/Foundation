# Changelog

## [Unreleased](https://github.com/jayjfletcher/Foundation/commits/main)

### Added

- The shared runtime for the jayi suite: the `Action` base, the action and model event contracts, `DispatchesModelEvents`, the package registry, base service providers, HTTP and MCP request bases, the MCP tool and server bases, the Cortex integration, `PackageException`, the `Policy` base, and `Surface`.
- Audit seams for jayi/keen: `AuditTrail` (bound to `NullAuditTrail` until Keen is installed), `Auditable`, `AuditHooks`, and a history endpoint and MCP tool for every package.
