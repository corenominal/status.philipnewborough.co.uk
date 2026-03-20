# status.philipnewborough.co.uk

A self-hosted personal microblogging platform built with [CodeIgniter 4](https://codeigniter.com/). It displays a chronological timeline of short status updates, optionally syncs them to a Mastodon instance, and exposes an RSS feed. An admin dashboard and REST API allow full content management from a browser or external tooling.

---

## Features

- **Status timeline** — Paginated, infinitely-scrolling feed of status updates with Markdown-rendered content and optional media attachments.
- **Search** — Plain-text search across status content directly on the timeline.
- **Media support** — Upload images (JPEG, PNG, GIF, WebP) and video (MP4). Images are auto-resized when wider than 1920 px and corrected for EXIF orientation.
- **Mastodon sync** — Optionally cross-post new or edited statuses to a Mastodon instance via the Mastodon v1/v2 API using a bearer token.
- **RSS feed** — `GET /feed/rss` exposes the latest 20 statuses as a standard RSS 2.0 feed.
- **Admin dashboard** — Overview of headline stats (total statuses, media counts, Mastodon sync status, reply counts) plus a 12-month activity chart and recent activity list.
- **REST API** — Admin-protected JSON API for creating, reading, updating, and deleting statuses and media.
- **Bulk import** — Spark CLI command to import statuses and media from a JSON export file.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | CodeIgniter 4 |
| Frontend | Bootstrap 5, vanilla JavaScript |
| Database | MySQL / MariaDB (via CodeIgniter Query Builder) |
| PHP standard | PSR-12 |
| JS standard | Airbnb Style Guide |
| CSS convention | BEM + Bootstrap utilities |

---

## Routes

| Method | Path | Description |
|---|---|---|
| `GET` | `/` | Main status timeline |
| `GET` | `/timeline/load` | AJAX endpoint for loading more statuses |
| `GET` | `/feed/rss` | RSS 2.0 feed (latest 20 statuses) |
| `GET` | `/logout` | Destroy session and redirect to auth service |
| `GET` | `/unauthorised` | 403 page |
| `GET` | `/admin` | Admin dashboard (requires admin session) |
| `POST` | `/api/statuses` | Create a status |
| `GET` | `/api/statuses/:id` | Fetch a single status |
| `PATCH` | `/api/statuses/:id` | Update a status |
| `DELETE` | `/api/statuses/:id` | Delete a status |
| `POST` | `/api/media` | Upload a media file |
| `DELETE` | `/api/media/:id` | Delete a media file |
| `GET` | `/api/test/ping` | Health check |

---

## Data Models

### Statuses (`statuses` table, soft-delete enabled)

| Field | Description |
|---|---|
| `uuid` | Unique identifier (used for deduplication during import) |
| `content` | Plain-text content |
| `content_html` | Markdown-processed HTML content |
| `media_ids` | JSON array of linked media IDs |
| `mastodon_id` | Remote Mastodon status ID (if synced) |
| `mastodon_url` | Remote Mastodon status URL |
| `in_reply_to_id` | Set when the status is a reply |

### Media (`media` table)

| Field | Description |
|---|---|
| `uuid` | Unique identifier; used as the filename on disk |
| `file_name` | Stored filename (e.g. `{uuid}.jpg`) under `public/media/` |
| `description` | Alt text / caption |
| `file_ext` / `mime_type` | File type metadata |
| `width` / `height` / `filesize` | Dimensions and size |

---

## Mastodon Integration

Configure `app/Config/Mastodon.php` (or via `.env`) with:

```
mastodon.url          = https://your.instance
mastodon.access_token = your_bearer_token
mastodon.account      = @you@your.instance
mastodon.profile      = https://your.instance/@you
```

When posting a status via the API with `post_to_mastodon=1`, the `MastodonPoster` library uploads any attached media to the Mastodon instance first, then creates the status. The returned `mastodon_id` and `mastodon_url` are stored so future edits and deletes are kept in sync.

---

## Bulk Import

Import statuses from a JSON file:

```bash
php spark status:import imports/statuses.json
```

Options:

| Flag | Description |
|---|---|
| `--limit N` | Only import the first N statuses |
| `--skip-download` | Store metadata only; do not download media files |
| `--dry-run` | Validate the file without writing anything |
| `--replace` | Overwrite existing statuses matched by UUID |

The expected JSON structure is:

```json
{
  "statuses": [
    {
      "guid": "...",
      "content": "...",
      "content_html": "...",
      "images": [
        { "url": "...", "mime_type": "image/jpeg", "description": "..." }
      ]
    }
  ]
}
```

Downloaded media is stored in `public/media/` using the media record's UUID as the filename.

---

## Configuration

Key configuration files under `app/Config/`:

| File | Purpose |
|---|---|
| `App.php` | Base URL, default locale |
| `Database.php` | Database connection settings |
| `Mastodon.php` | Mastodon instance credentials |
| `User.php` | Site owner username and home directory |
| `Routes.php` | All application routes |

Sensitive values (database credentials, Mastodon token, etc.) should be set in `.env` rather than committed to version control.

---

## Installation

```bash
composer install
npm install
cp env .env          # then edit .env for your environment
php spark migrate
```
