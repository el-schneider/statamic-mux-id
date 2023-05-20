# Statamic Mux Id

Statamic Mux Id is an addon for Statamic CMS that integrates Mux Video API for effortless video asset handling.

## Features

- Automates Mux asset creation on Statamic asset upload.
- Utilizes asynchronous processing for Mux API requests.
- Syncs Mux video asset metadata with Statamic assets.
- Listens to and acts on Mux webhooks for asset events.

## How to Install

Search for "Statamic Mux Id" in `Tools > Addons` in Statamic control panel and click **install**, or use:

```bash
composer require el-schneider/statamic-mux-id
```

## How to Use

1. Add env vars `MUX_TOKEN_ID` and `MUX_TOKEN_SECRET`
2. Point the webhook in your Mux dashboard to `<yourdomain>/!/statamic-mux-id/listen`
