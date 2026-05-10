# Changelog

All notable changes to this project will be documented in this file.

## [1.4.1] - 2024-05-10
### Added
- Added an option to remove Google Tag Manager and Analytics scripts from the frontend.
- Added an option to explicitly allow Google Bots for SEO indexing.
- Added an option to block common license check servers (e.g., Envato, Elementor, WooCommerce).
### Changed
- Improved the `filter_http_requests` method to return `WP_Error` for blocked requests instead of `true`, complying with WordPress standards.
- Automatically block update servers if "Disable Updates" is active.

## [1.4.0] - 2024-05-10
### Added
- Added an option to disable Google Fonts (`fonts.googleapis.com` and `fonts.gstatic.com`).

## [1.3.0]
### Added
- Block external HTTP requests with customizable settings.
- Editable domain lists for whitelist and blacklist.
- Toggle options for allowing Google Domains.
- Options to disable Automatic Updates, XML-RPC, and Emojis.
- Admin settings page.
- Admin banners for branding.
