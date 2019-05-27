# StarShip
**Project Status:** very early development, not yet usable

StarShip aims to be a fully functional federating social media server, with a focus on customization, extensibility and a clean separation of back- and front-end. Written primarily in PHP, it also aims to be lightweight and avoid much of the server and client overhead inherent in "modern" web apps.

## Project Ideology

* Prioritize a reliable, consistent, and functional "generic" core framework.
* Build in a wide range of server-level customization options.
* Maintain well-documented code, to facilitate extensibility.
* Add features in a modular, customizable manner.
* Maintain compatability with existing ActivityPub implementations to the extent possible without compromising StarShip's own features.
* Make all individual and administrative content moderation features as transparent and clearly communicated as possible - including *critical* warnings such as when a post will transmitted to a server that may not honor access control settings such as account blocks.

## Installation/Setup
*To Do: Write an installation script. And an update script that will work to update from any starting version to any later version.*

Requirements:
* PHP 7.0+ for your webserver
* MySQL or MariaDB
* Web server (current configuration samples assume nginx)

1. Clone/pull/whatever the master branch of the repo
2. Fill in the database account/password you want to use into config/db.ini
3. Fill in the same account/password into init.sql
4. Run init.sql in MySQL/MariaDB
5. Realize that none of the current code actually has been set up to use MySQL for anything yet and wonder wtf kind of a half-baked set of instructions this is, anyway
6. Copy or refer to the sample nginx configuration to set up the necessary rewrites
7. Set up your SSL certificates
8. Reload nginx

## Goal Posts
*these will be expanded on or modified as necessary

### Version 0.0.1
* Initial ActivityPub functionality
* * Create and Delete Note activities
* * Create Follow activities
* * Accept or Reject received Create Follow activities
* * Access Actor Inbox contents
* Initial Account functionality
* * Log in
* * View a "timeline" of objects received to your Inbox

### Version 0.1.0
* Core ActivityPub functionality
* * Create new Actor profile
* * Update Actor profile
* * Processing Create and Delete activities from remote servers
* Core Account functionality
* * Create new accounts
* * Authenticate existing accounts
* Core micro-blogging web client
* Core API
* * Ideally, the API should be *structurally* consistent with existing ActivityPub server APIs, for easier cross-platform client app development

### Version 1.0.0
* Full ActivityPub functionality
* Full Account functionality
* * Create/authenticate existing accounts
* * Update account information
* * Account deletion
* * Account-level received-content filters
* Core Moderation functionality
* * Temporary/Permanent local account suspensions
* * Temporary/Permanent remote account blocks
* * Temporary/Permanent servers blocks
* * Moderator-level post deletion (that *prevents* re-fetching that post)
* * Moderator-added content warnings
* * Account-level blocks of accounts or servers
* * A local reporting system
* Full micro-blogging web client
* Full blogging web client
* Full API
