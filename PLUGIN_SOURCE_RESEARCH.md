# Plugin source research

- Modrinth provides a public API at `https://api.modrinth.com`; most reads do not require authentication. Its project-version response includes supported game versions, loaders, direct file URLs, filenames, sizes, primary-file flags, and SHA-1/SHA-512 hashes. Requests must use a unique User-Agent and should respect rate-limit headers.
- Spigot does not expose a modern public official marketplace API in the reviewed material. Spiget presents itself as an API for Spigot resources, plugins, and authors. The implementation should treat Spigot downloads as a separate adapter, validate the returned URL/content, and not assume every resource is freely downloadable.
- BuiltByBit v1 endpoints require a private API token for resource listing and retrieval. The documentation exposes public/owned resource routes but does not imply anonymous downloads. Premium resources require the user's own entitlement/session; the panel must not bypass payment or scrape private download links.
- Safe installation policy: only accept HTTPS downloads, enforce a `.jar` filename and size limit, download to a temporary file, verify the upstream hash when available, scan archive entries for unsafe path traversal, then atomically move into the selected server's `plugins` directory. The action must be authorized for the server owner/admin and logged.
- Architecture implication: Modrinth can be implemented first with public search/version APIs. Spiget can be added with resource IDs and its documented download endpoint/metadata. BuiltByBit should be optional and require an administrator-configured API token or a user-authorized flow; otherwise show an external link rather than attempting automatic installation.

## Sources

- https://docs.modrinth.com/api/
- https://docs.modrinth.com/api/operations/getprojectversions/
- https://spiget.org/
- https://www.spigotmc.org/threads/spigot-resources-api.68705/
- https://builtbybit.com/wiki/v1-endpoints/
- https://builtbybit.com/wiki/users-overview
