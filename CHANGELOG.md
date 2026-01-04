# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2026-01-04
### Added
- **Programmatic Message API:** New `MessageService` for sending messages programmatically
  - `sendMessage()` - Send a message to an existing inbox
  - `sendMessageToUsers()` - Send message and create inbox if needed
  - `getOrCreateInbox()` - Get or create an inbox for users
  - `getMessages()` - Retrieve messages from an inbox
  - `getLatestMessage()` - Get the most recent message
  - `getUserInboxes()` - Get all inboxes for a user
  - `getUnreadCount()` - Get unread message count
  - `markAsRead()` - Mark messages as read
- Perfect for integrating with AI bots, webhooks, or automated systems

### Changed
- **BREAKING:** Updated to support Filament v4.0
- Updated `composer.json` to require `filament/filament: ^4.0` and `filament/spatie-laravel-media-library-plugin: ^4.0`
- Added support for Laravel 12
- Migrated Forms components to new Schema namespace (`Filament\Schema\*`)
- Updated `Messages` component to use `HasSchemas` interface and `InteractsWithSchemas` trait
- Updated `Inbox` component to use `HasSchemas` interface and `InteractsWithSchemas` trait
- Updated form method signature to use `Schema` instead of `Form`

### Note
- This version is **not backwards compatible** with Filament v3. For Filament v3 support, use version 1.x.

---

## [1.0.1] - 2025-03-15
### Fixed
- **Inbox:** Inbox resources.
- **Messages:** Message resources.

---

## [1.0.0] - 2025-03-08
### Added
- Initial release of **Filament Messages**
- Features include:
  - User-to-User & Group Chats
  - Unread Message Badges
  - File Attachments
  - Configurable Refresh Interval
  - Timezone Support