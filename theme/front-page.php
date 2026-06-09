<?php
/**
 * puralux front page.
 *
 * @package puralux
 */

$clone_dir   = get_template_directory()     . '/assets/puralux-clone';
$clone_uri   = get_template_directory_uri() . '/assets/puralux-clone';
$source_file = $clone_dir . '/site/products/ultrasonic-retainer-cleaner.html';

if ( ! file_exists( $source_file ) ) {
    status_header( 500 );
    echo 'Clone source missing: ' . esc_html( $source_file );
    exit;
}

$html = file_get_contents( $source_file );

$replacements = array(
    '../../cdn-assets/'   => $clone_uri . '/cdn-assets/',
    '../../fonts-google/' => $clone_uri . '/fonts-google/',
    '../../fonts-static/' => $clone_uri . '/fonts-static/',
    'https://cdn-assets/' => $clone_uri . '/cdn-assets/',
    '"//cdn-assets/'      => '"' . $clone_uri . '/cdn-assets/',
    '../cdn/'             => $clone_uri . '/site/cdn/',
    '../checkouts/'       => $clone_uri . '/site/checkouts/',
);

$html = strtr( $html, $replacements );
// Fix broken brand-replaced domains
$brand_domain_map = array(
    "//restora-paris.com/cdn/" => $clone_uri . "/site/cdn/",
    "//soya-paris.com/cdn/"    => $clone_uri . "/site/cdn/",
    "//puralux.local/cdn/"     => $clone_uri . "/site/cdn/",
    "//zimadental.nl/cdn/"     => $clone_uri . "/site/cdn/",
    "//stepora.local/cdn/"     => $clone_uri . "/site/cdn/",
    "//treatmedy.com/cdn/"     => $clone_uri . "/site/cdn/",
    "//calmara.local/cdn/"     => $clone_uri . "/site/cdn/",
    "//callixe.com/cdn/"       => $clone_uri . "/site/cdn/",
    "https://restora-paris.com/cdn/" => $clone_uri . "/site/cdn/",
    "https://soya-paris.com/cdn/"    => $clone_uri . "/site/cdn/",
    "https://puralux.local/cdn/"     => $clone_uri . "/site/cdn/",
    "https://zimadental.nl/cdn/"     => $clone_uri . "/site/cdn/",
    "https://stepora.local/cdn/"     => $clone_uri . "/site/cdn/",
    "https://treatmedy.com/cdn/"     => $clone_uri . "/site/cdn/",
    "https://calmara.local/cdn/"     => $clone_uri . "/site/cdn/",
    "https://callixe.com/cdn/"       => $clone_uri . "/site/cdn/",
);
$html = strtr( $html, $brand_domain_map );


// Strip Shrine theme protection + suspicious shopify.jsdeliver.cloud loader.
$html = preg_replace(
    array(
        '#\s*<link[^>]*href="https://js\.shrinetheme\.com[^"]*"[^>]*>\s*#i',
        '#\s*<script[^>]*src="https://js\.shrinetheme\.com[^"]*"[^>]*></script>\s*#i',
        '#\s*<script[^>]*src="https://shopify\.jsdeliver\.cloud[^"]*"[^>]*></script>\s*#i',
    ),
    "\n",
    $html
);

// === BORIS PATCH: strip broken Shopify-clone refs + inject WC add-to-cart ===
$html = preg_replace(
    array(
        // External third-party that 403/404
        '#<link[^>]*href="(https?:)?//js\.shrinetheme\.com[^"]*"[^>]*>#i',
        '#<script[^>]*src="(https?:)?//js\.shrinetheme\.com[^"]*"[^>]*></script>#i',
        '#<script[^>]*src="(https?:)?//shopify\.jsdeliver\.cloud[^"]*"[^>]*></script>#i',
        '#<script[^>]*src="(https?:)?//d1um8515vdn9kb\.cloudfront\.net[^"]*"[^>]*></script>#i',
        '#<link[^>]*href="(https?:)?//d1um8515vdn9kb\.cloudfront\.net[^"]*"[^>]*>#i',
        '#<script[^>]*src="(https?:)?//tag\.segmetrics\.io[^"]*"[^>]*></script>#i',
        '#<script[^>]*src="(https?:)?//(cdn\.)?judge\.me[^"]*"[^>]*></script>#i',
        '#<link[^>]*href="(https?:)?//(cdn\.)?judge\.me[^"]*"[^>]*>#i',
        '#<script[^>]*src="(https?:)?//shop\.app/[^"]*"[^>]*></script>#i',
        '#<script[^>]*src="(https?:)?//(cdn\.)?shopify\.com[^"]*"[^>]*></script>#i',
        '#<script[^>]*src="(https?:)?//calmara\.com/[^"]*"[^>]*></script>#i',
        // Any reference (anywhere in URL) pointing to Shopify infra that returns 404 on our WP
        '#<script[^>]*src="[^"]*shopifycloud[^"]*"[^>]*></script>#i',
        '#<link[^>]*href="[^"]*shopifycloud[^"]*"[^>]*>#i',
        '#<script[^>]*src="[^"]*compiled_assets/scripts\.js[^"]*"[^>]*></script>#i',
        '#<script[^>]*src="[^"]*checkouts/internal/preloads\.js[^"]*"[^>]*></script>#i',
        '#<script[^>]*src="[^"]*gempagev2\.js[^"]*"[^>]*></script>#i',
        // Shopify Pixel Manager (trekkie shim) — refs Shopify backend
        '#<script[^>]*src="[^"]*/cdn/wpm/[^"]*"[^>]*></script>#is',
        '#<script[^>]*data-trekkie-shim[^>]*></script>#is',
        // Judge.me reviews (broken local copy)
        '#<script[^>]*src="[^"]*judge\.me[^"]*"[^>]*></script>#i',
        '#<link[^>]*href="[^"]*judge\.me[^"]*"[^>]*>#i',
        '#<noscript>\s*<link[^>]*judge\.me[^"]*>\s*</noscript>#is',
        // shop.app preconnect/dns-prefetch
        '#<link[^>]*(href|src)="(https?:)?//shop\.app[^"]*"[^>]*>#i',
        // Inline Shopify.PaymentButton.init that dynamically loads portable-wallets
        '#<script[^>]*data-source-attribution="shopify\.dynamic_checkout\.dynamic\.init"[^>]*>[\s\S]*?</script>#i',
        // Bare /cdn/ refs (resolve to non-existent on our domain)
        '#<script[^>]*src="/cdn/[^"]*"[^>]*></script>#i',
        '#<link[^>]*href="/cdn/[^"]*"[^>]*>#i',
    ),
    "\n",
    $html
);

// Inject WC add-to-cart handler before </body>
$wc_product_id = 10;
$wc_cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '/cart/';
$wc_handler = '<script>(function(){
  var PID = ' . intval( $wc_product_id ) . ';
  var CART = ' . wp_json_encode( $wc_cart_url ) . ';
  function bind(){
    var selectors = [
      "button[name=\"add\"]","button.add-to-cart","button[data-add-to-cart]",
      "form[action*=cart] button[type=submit]",
      "a.add-to-cart","[data-product-add-to-cart]","button.product-form__submit",
      "button#AddToCart","button[id*=AddToCart]","button[class*=add-to-cart]",
      "button[class*=AddToCart]","button[class*=product-form__cart]",
      "button[class*=cart-btn]","button[class*=buy-now]",
      "input[name=add]","[data-buy-now]","[data-add-to-cart-button]"
    ];
    var btns = document.querySelectorAll(selectors.join(","));
    btns.forEach(function(b){
      if (b.dataset.wcBound) return;
      b.dataset.wcBound = "1";
      b.addEventListener("click", function(e){
        e.preventDefault(); e.stopPropagation();
        var qtyEl = document.querySelector("input[name=quantity],input.qty,[data-quantity]");
        var qty = qtyEl ? (parseInt(qtyEl.value,10)||1) : 1;
        var orig = b.innerHTML;
        try { b.innerHTML = "Dodajam…"; b.disabled = true; } catch(_){}
        var body = new URLSearchParams();
        body.append("product_id", PID);
        body.append("quantity", qty);
        body.append("add-to-cart", PID);
        fetch("/?wc-ajax=add_to_cart", {
          method: "POST",
          credentials: "include",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: body.toString()
        }).then(function(r){return r.text();})
          .then(function(){ window.location.href = CART; })
          .catch(function(){ window.location.href = "/?add-to-cart=" + PID + "&quantity=" + qty; });
      }, true);
    });
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bind);
  } else { bind(); }
  setTimeout(bind, 1500); setTimeout(bind, 3500);
})();</script>';
$html = preg_replace( "#</body>#i", $wc_handler . "</body>", $html, 1 );
// === END BORIS PATCH ===

header( 'Content-Type: text/html; charset=UTF-8' );
echo $html;
exit;
