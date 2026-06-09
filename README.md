# Puralux-SI

WordPress theme + plugins for **Puralux** brand, market **SI**.

- **Subdomain:** `si-puralux.noriks.com` → brand-machine (18.197.40.171)
- **Theme:** `puralux/` (Shopify-clone served via front-page.php)
- **Plugins:** `plugins/` (only managed plugins, WP defaults excluded)
- **Demo product:** Dental Pod Pro (created via WooCommerce REST)

## Structure
```
puralux-repo/
├── puralux/                # WP theme (symlinked from wp-content/themes/puralux)
│   ├── front-page.php       # serves Shopify clone HTML + injects WC add-to-cart
│   └── assets/puralux-clone/   # static HTML + assets
└── plugins/
    └── woocommerce/         # WooCommerce plugin (symlinked)
```

## Deploy
On brand-machine:
```
cd /var/www/puralux/si/.puralux-repo
GIT_SSH_COMMAND='ssh -i ~/.ssh/github_noriks' git pull
```

## ADD TO CART
The front-page.php injects a JS handler that POSTs to `/?wc-ajax=add_to_cart` with the demo product ID. Triggered on any visible button matching selectors like `button.add-to-cart`, `button[name=add]`, etc.
