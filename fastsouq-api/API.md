# FastSouq API — v1

REST API for the FastSouq mobile app (iPhone / Android). It is a WordPress plugin that runs next to the **FastSouq theme** and uses the theme's own data and rules, so the app and the website always match: same accounts, categories, filters, posting rules, admin approval, sponsored banners.

**Base URL:** `https://fastsouq.ae/wp-json/fastsouq/v1`

> **بالعربي باختصار:** هذا الـ plugin هو الجسر بين التطبيق والموقع. ثبّته من **الإضافات ← أضف جديد ← رفع**، ثم فعّله، مع إبقاء ثيم FastSouq مفعلاً. لا يحتاج أي إعدادات. هذا الملف موجّه للمبرمج الذي سيبني التطبيق.

---

## Conventions

| | |
|---|---|
| Language | `?lang=ar` or `?lang=en`, or the `Accept-Language` header. Default Arabic. All names, labels, prices and error messages come back in that language. |
| Sign-in | `Authorization: Bearer <token>`. If the host strips this header, send `X-FS-Token: <token>`. |
| Errors | HTTP status + `{ "code": "...", "message": "readable text in the request language", "data": { "status": 400 } }`. Show `message` to the user. |
| Lists | `{ items, total, page, pages }`. `page` starts at 1. |
| Dates | ISO 8601 (`date`). `ago` is a ready-made text such as "today" or "2 days ago". |
| Money | `price` is a number or `null` (= "ask for price"). `price_text` is ready to display. Currency is AED. |

### Ad (list item)
```json
{
  "id": 8, "ref": "QS-1008", "title": "Toyota Land Cruiser GXR 2021 GCC",
  "group": "vehicle", "status": "publish",
  "price": 235000, "price_text": "235,000 AED", "currency": "AED",
  "facts": [ { "icon": "cal", "text": "2021" }, { "icon": "gauge", "text": "64,000 km" } ],
  "image": "https://…/photo-768x576.jpg", "images_count": 6,
  "category": { "slug": "cars-for-sale", "name": "Cars for sale" },
  "city": "Al Quoz, Dubai", "featured": true, "wanted": false,
  "date": "2026-11-10T14:02:00+00:00", "ago": "today",
  "phone": "+971501234567", "whatsapp": "https://wa.me/971501234567?text=…",
  "url": "https://fastsouq.ae/ad/…", "is_favorite": false,
  "service": null
}
```
- `group`: `vehicle` (cars & vehicles), `item` (parts, accessories, tools), `service` (car services), or `general` (all other categories).
- `facts`: the 0–2 key facts shown on the card (website card = app card).
- `service`: for service ads, the service type. Show it in place of the price.
- `image`: an empty string means the ad has no photo; show a placeholder.

---

## Start-up data (public)

### `GET /config`
App settings and the **form definition**, so the app builds the post-ad form and filters without hard-coding:
- `site` — name, logo, icon, phone, WhatsApp link, email, privacy policy URL.
- `slides` — home photo slider.
- `sort` — sort options.
- `report_reasons`
- `listing_days` — how long ads stay published.
- `form.groups.{vehicle|item|service|general}` — ordered fields: `key`, `label`, `required`, `type`, plus `options` where the type needs them. Types:
  - `text`, `number`, `textarea`
  - `chips` / `select` — with `options` `[ {value, label} ]`.
  - `select` for `body` — uses `options_by_sub` (the options depend on the chosen sub-category).
  - `make` / `model` — lists come from `/makes`. A model with `other: true` means "Other": show the `model_other` text field.
  - `category` — the `itype` field: the children of the chosen sub-category, from `/categories`.
  - `period` — only for `sub = cars-for-rent`.
- `form.common` — fields every ad has: emirate, city, area, lat/lng, phone, photos, agree.
- `form.guest_account` — the extra fields for posting without an account.

### `GET /categories`
The full tree, alphabetical in the request language: main → sub → detail. Use `?parent=slug` for one branch.
```json
{ "slug": "cars", "name": "Cars", "icon": "car", "count": 0, "menu": 1,
  "children": [ { "slug": "vehicles", "name": "Cars & Vehicles", "icon": "car", "group": "vehicle",
                  "children": [ { "slug": "cars-for-sale", "name": "Cars for sale", "children": [ … ] } ] } ] }
```
- `menu` (1–6) — the 6 categories of the website's top menu. `0` means it sits under "More".
- `group` is set on the node an ad is **posted in**. Under "Cars" that is one of its 5 children (vehicles, spare parts, accessories, tools, services). For every other main category it is the main category itself (`general`).
- `icon` — the icon name; the app maps these names to its own icons.

### `GET /makes`
Makes with their models, alphabetical. "Other" (`other: true`) is always last, both as a make and as a model.

### `GET /cities`
Emirates with their cities.

### `GET /home`
Everything for the home screen:
- `slides`, `title`
- `categories` — with ad counts.
- `banners.top`, `banners.middle` — sponsored banners, or `null`.
- `sections` — the latest **10** ads of each main category that has ads, menu categories first.

### `GET /sponsored?place=…&category=slug`
One sponsored banner: `{ id, title, image, image_mobile, link, new_tab }`, or `null`.
- Places: `home_top`, `home_mid`, `cat_side`, `cat_list`, `ad_side`.
- `link` goes through the website, which counts the click and redirects to the advertiser. Open it in the browser.

---

## Ads

### `GET /ads`
Search and filters. These are the same parameters as the website URL filters:

| Param | |
|---|---|
| `q` | search text (Arabic make names also find English ones) |
| `category` | any level: `cars`, `vehicles`, `cars-for-sale`, `real-estate`, … |
| `group` | `vehicle`, `item`, `service`, `general` |
| `make`, `model` | slugs from `/makes` |
| `emirate`, `city` | slugs from `/cities` |
| `ymin`, `ymax`, `pmin`, `pmax`, `kmin`, `kmax` | year / price / km ranges |
| `body`, `cond`, `gear`, `fuel`, `cyl`, `specs`, `color`, `part_type`, `deal`, `brand` | option values from `/config` |
| `sort` | empty = newest (featured first), `asc` / `desc` = price |
| `seller` | user id — that seller's ads |
| `ids` | comma list — e.g. favorites kept on the phone before sign-in |
| `page`, `per_page` | `per_page` ≤ 50 (default 20) |

Returns `{ items, total, page, pages, banner }`. On page 1, `banner` is the in-list sponsored banner (`cat_list`), if one is running.
Ads by sellers the user blocked are left out.

### `GET /ads/{id}`
Everything in the list item, plus:
- `description`
- `images[]` — each with `{id, thumb, large, full}`.
- `specs[]` — ready-made `{label, value}` rows, the same table as the website.
- `path[]` — category breadcrumb.
- `make`, `model`
- `location` — `{emirate, city, area, lat, lng}`.
- `seller` — `{id, name, member_since, ads_count}`.
- `views`
- `similar[]` — 5 ads.
- `banner` — `ad_side`, or `null`.

When the signed-in user owns the ad, they also get `is_owner: true` and `edit_values` (to pre-fill the edit form). Owners can also open their own ads that are still under review.

### `POST /ads` — post an ad
Send `multipart/form-data` with the same field names as the website form:

```
main         = cars | <main category slug>       (required)
section      = vehicles | spare-parts | accessories | tools | services   (only when main = cars)
sub          = <sub-category slug>                (required)
itype        = <detail slug>                      (when the sub has children)
title, desc, price, year, km, make, model, model_other, cond, gear, fuel, cyl, specs, color, body, deal, period,
brand, part_type, sku                             (per form.groups in /config)
emirate (required), city, area, lat, lng
phone (required)    – UAE number, any format (05…, 5…, +9715…)
agree = 1 (required)
photos[]            – up to 10 images (jpg/png/webp, max 10 MB each); the first one is the cover
```

**Without an account** (no token): also send `acc_name`, `acc_pass` (8+ characters) and optionally `acc_email`.
- The account is made from the ad's `phone`.
- If that phone already has an account, only `acc_pass` is needed and it must be that account's password. An ad is never attached to an existing account without its password.
- Call `POST /auth/check-phone {phone}` first, so the app can show either the name field or "enter your password".

Response `201`:
```json
{ "id": 48, "status": "pending", "message": "…", "ad": { …list item… },
  "token": "fs_…", "user": { … }, "new_account": true }
```
- Every new ad waits for admin approval (`status: pending`), as on the website.
- `token`, `user` and `new_account` are only returned when the ad was posted without an account. Save the token: the user is now signed in.

Errors use the same codes as the website:
| `code` | Meaning |
|---|---|
| `err_required` | a required field is missing |
| `err_agree` | `agree` was not sent |
| `err_limit` (429) | daily ad limit per account (website setting) |
| `reg_err_phone` | phone number not valid |
| `reg_err_name` | name missing |
| `reg_err_pw` | password shorter than 8 characters |
| `acc_exists` | phone already has an account; send its password |
| `acc_wrong_pw` | wrong password for that phone |
| `acc_email_used` | email belongs to another account |

### `POST /ads/{id}` — edit (owner)
Same fields as posting. Photos:
- Send `keep_photos=12,15` (the ids from `images[]`) to keep some of the current photos, plus new `photos[]` to add.
- Without `keep_photos` and without new photos, the current photos stay as they are.

The ad goes back to review.

### `DELETE /ads/{id}` — delete (owner)
The ad goes to the bin, the same as deleting from the website.

### `POST /ads/{id}/report` `{reason}`
Counted once per account or device. After 3 reports the ad is hidden until an admin reviews it. Reasons come from `/config`.

---

## Account

| Method | Path | Body | Notes |
|---|---|---|---|
| POST | `/auth/login` | `login` (phone or email), `password`, `device?` | → `{token, user}`. 20 wrong passwords per IP in 15 min → `429`. |
| POST | `/auth/register` | `name`, `phone`, `email`, `password` | → `201 {token, user}`. Same rules as the website register form. |
| POST | `/auth/check-phone` | `phone` | → `{valid, exists}` |
| POST | `/auth/forgot` | `login` | WordPress emails a reset link when the account has an email. The answer is always the same. |
| POST | `/auth/logout` | – | Signs this device out (revokes its token). |
| GET | `/me` | – | `{id, name, phone, email, member_since, ads:{publish,pending,expired,draft}, favorites}` |
| POST | `/me` | `name?`, `email?`, `phone?` | Updates the profile. |
| POST | `/me/password` | `current`, `new` | Signs all devices out and returns a new token for this one. |
| DELETE | `/me` | `password` | **Deletes the account with its ads and photos.** Required by the App Store. |
| GET | `/me/ads?status=` | – | `publish`, `pending`, `expired`, or all. Each item also has `views` and `expires`. |
| GET | `/me/favorites` | – | `{ids, items}` |
| POST | `/me/favorites` | `ids: [..]` | Merges favorites kept on the phone before sign-in. |
| PUT / DELETE | `/me/favorites/{id}` | – | Adds / removes one favorite. |
| GET | `/me/blocks` | – | Blocked sellers. |
| PUT / DELETE | `/me/blocks/{userId}` | – | Blocks / unblocks a seller: their ads are hidden for this user. Required by the App Store for user-posted content. |
| POST | `/me/devices` | `token`, `platform` (`ios` / `android`) | Saves the Firebase push token. Sending notifications comes with the app stage. |

**Tokens**
- Each sign-in gets its own token, valid until logout or a password change. Up to 20 devices per account.
- Only a hash of each token is stored.
- Changing the password (in the app, on the website, or from the dashboard) signs out every app device.

---

## Notes for the server

- **Install:** WordPress → Plugins → Add New → Upload `fastsouq-api.zip` → Activate. The FastSouq theme must stay active.
- **Authorization header:** some Apache hosts strip it. Either send `X-FS-Token` from the app, or add this to `.htaccess`:
  ```
  RewriteEngine On
  RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
  ```
- **Caching:** do not cache `/wp-json/fastsouq/v1/*` responses that are sent with a token. Public GET endpoints (`/config`, `/categories`, `/makes`, `/cities`) can be cached for a few minutes.
- **Privacy policy:** `config.site.privacy` comes from WordPress → Settings → Privacy. Publish that page before submitting the app; both stores ask for it.
- **Quick check:** open `https://fastsouq.ae/wp-json/fastsouq/v1/categories` in a browser. You should see the category list as JSON.
