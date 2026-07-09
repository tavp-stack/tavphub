# tavphub

Framework **admin panel** untuk TAVP. Bikin back-office (dashboard, CRUD)
jadi cepat, nggak perlu bikin dari scratch.

## Features

- **CRUD resources** — Auto-generated index, create, edit, delete views
- **Table builder** — Columns, sortable, searchable, filters, bulk actions
- **Form builder** — Field types, validation, file upload
- **Dashboard widgets** — Stats, charts, recent activity
- **Sidebar navigation** — Auto-generated from registered resources
- **Role guard** — Access control per role

## Requirements

- PHP 8.1+
- Phalcon 5.x
- tavp-core
- tavphub

## Install

```bash
# Via tavp CLI
tavp module:install tavp/tavphub

# Via Composer
composer require tavp/tavphub
```

## Quick start

```php
// Define a resource
use Tavp\Hub\Resource;

class UserResource extends Resource
{
    protected string $model = 'User';
    protected array $columns = ['id', 'name', 'email', 'created_at'];
    protected array $form = [
        'name' => 'text',
        'email' => 'email',
    ];
}
```

## Routes

| Route | Description |
|---|---|
| `/admin` | Dashboard |
| `/admin/users` | Users list |
| `/admin/users/create` | Create user |
| `/admin/users/{id}/edit` | Edit user |
| `/admin/users/{id}/delete` | Delete user |

## Testing

```bash
composer test
```

## Status

Part of **0.1.0 Genesis** (ZeroVer `0.MINOR.PATCH`). Basic CRUD.
Full features (widgets, roles, media) in `0.3.0`.

## License

MIT
