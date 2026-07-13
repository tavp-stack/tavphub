# tavphub

Framework **admin panel** untuk TAVP. Bikin back-office (dashboard, CRUD)
jadi cepat, nggak perlu bikin dari scratch.

## Features

TavpHub adalah "Laravel Nova-nya Phalcon" — **gratis & MIT** (Nova berbayar
per project). Cukup definisikan satu `Resource` per model.

- **CRUD resources** — Auto-generated index, create, edit, delete views
- **Resource auto-discovery** — scan folder, tak perlu wiring manual
- **Filters** — saring tabel (select/date/boolean/custom)
- **Metrics** — kartu angka + delta di dashboard & atas tabel
- **Actions** — aksi bulk terhadap baris terpilih
- **Lenses** — tampilan alternatif yang sudah difilter
- **Relationships** — field `belongsTo` jadi dropdown otomatis
- **Policies** — otorisasi per ability (viewAny/view/create/update/delete/...)
- **Search** — pencarian global antar kolom
- **Table & Form builder** — field types, validation, old input
- **UI via tavpblocks** — StatCard, Pagination, Badge, Alert (dengan fallback)

## Requirements

- PHP 8.3+
- Phalcon 5.16+
- tavp-core

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
