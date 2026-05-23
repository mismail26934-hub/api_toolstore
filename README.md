# API Toolstore

REST-style API PHP untuk modul tool store (form, user, PO/SO, receive, dll.).

## Struktur folder

| Folder | Peran |
|--------|--------|
| `public/` | Front controller (`index.php`), entry point web |
| `controller/` | Handler per resource (POST + `param`) |
| `model/` | `Proses_sql` (facade) + repository per tabel |
| `conn/` | Koneksi, auth, env, `api_bootstrap.php` |
| `lib/` | Utilitas (mis. baca spreadsheet upload) |
| `scripts/` | Alat refactor (split repository, generate facade) |

## Arsitektur model

```
Controller → Proses_sql (facade) → UserRepository / FormRepository / …
```

| Repository | Tabel / domain |
|------------|----------------|
| `UserRepository` | `tb_users`, login, token |
| `FormRepository` | `tb_form`, dashboard count |
| `FormDetailRepository` | `tb_form_detail` |
| `ActionNoteRepository` | `tb_action_note` |
| `PoRepository` | `tb_po` |
| `SoRepository` | `tb_so` |
| `SuperiorRepository` | `tb_superior` |
| `RcvWhRepository` | `tb_rcv_wh` |
| `RcvToolRepository` | `tb_rcv_tool` |

Semua repository extends `RepositoryBase` (`DbTable` + `DbStatementTrait`).

## Menjalankan lokal

1. Salin env: `cp .env.example .env` lalu sesuaikan DB.
2. Document root Apache/Nginx arahkan ke folder **`public/`**.
3. Request: `POST http://<host>/.../v1/<route>` dengan `param` di body.

Contoh route (`public/index.php`):

| Route | Controller |
|-------|------------|
| `POST /v1/auth/login` | `login.php` |
| `POST /v1/user` | `cont_user.php` |
| `POST /v1/user/upload` | `cont_user_upload.php` |
| `POST /v1/form` | `cont_form.php` |
| `POST /v1/form/detail` | `cont_form_detail.php` |
| `POST /v1/form/action-note` | `cont_action_note.php` |
| `POST /v1/superior` | `cont_superrior.php` |
| `POST /v1/superior/upload` | `cont_superrior_upload.php` |
| `POST /v1/so` | `cont_so.php` |
| `POST /v1/po` | `cont_po.php` |
| `POST /v1/receive/tool` | `cont_rcv_tool.php` |
| `POST /v1/receive/wh` | `cont_rcv_wh.php` |

## Autentikasi

- `API_AUTH_REQUIRED=0` — auth opsional
- `API_AUTH_REQUIRED=1` — wajib `id_users` + `token` di setiap endpoint terproteksi

## Bootstrap controller

```php
require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../conn/api_crud.php";

if (!api_is_post_with_param()) {
    return;
}

$data = api_bootstrap_data();
```

Semua controller CRUD memakai pola: konstanta `param`, helper `api_crud_*`, fungsi `cont_*_format_row` / `cont_*_handle_mutation`.

Untuk VIEW: `api_mysqli_fetch_all_objects()` mengambil semua baris dulu sebelum `count_*` atau query lain (satu koneksi mysqli = satu result set aktif).

## Konvensi response (jangan diubah untuk client lama)

- **Mutasi:** `[{ "value": "1"|"0"|"2", "message": "..." }]`
- **List VIEW:** `{ "total": N, "data": [ ... ] }`
- **401:** `{ "value": "0", "message": "Unauthorized" }`

## Format kode

```bash
npx prettier --write "**/*.php"
```

## Pencarian form (`VIEW DATA FORM`)

| POST | Keterangan |
|------|------------|
| `search` | Kata kunci (atau `keyword`) — **centang/enabled** di Postman |
| `search_field` | Opsional: `all` (default), `form_no`, `serviceman`, `pngroup`, `pndesc`, `order`, `extended` |

Setelah deploy, restart Apache/PHP (clear OPcache) agar file `form_repository.php` / `cont_form.php` / `m_proses.php` terbaru terbaca.

## Regenerasi facade (opsional)

Setelah mengubah signature di repository:

```bash
node scripts/generate_proses_facade.mjs
```
