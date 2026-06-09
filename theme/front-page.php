<?php readfile("/var/www/puralux/si/static/site/products/ultrasonic-retainer-cleaner.html"); ?>
<script>
(function(){
  var SELECTORS = "button[name=add],button.add-to-cart,button[data-add-to-cart],a.add-to-cart,[data-add-to-cart],button.product-form__submit,button#AddToCart,button[id*=AddToCart],button[class*=add-to-cart],button[class*=AddToCart],button[class*=product-form__cart],button[class*=cart-btn],button[class*=buy-now],input[name=add],[data-buy-now]";
  function getPID(el){ var p=el.closest("[data-product-id]"); if(p) return p.dataset.productId; var f=el.closest("form"); if(f){var i=f.querySelector("input[name=\"product_id\"],input[name=id]"); if(i) return i.value;} return el.dataset.productId||el.dataset.id||1; }
  function getQty(el){ var f=el.closest("form"); if(f){var q=f.querySelector("input[name=quantity]"); if(q&&q.value) return q.value;} return 1; }
  document.addEventListener("click", function(e){ var btn=e.target.closest(SELECTORS); if(!btn) return; e.preventDefault(); e.stopPropagation(); var PID=getPID(btn), qty=getQty(btn); var body=new FormData(); body.append("product_id", PID); body.append("quantity", qty); body.append("add-to-cart", PID); fetch("/?wc-ajax=add_to_cart",{method:"POST",body:body,credentials:"include"}).then(function(){ window.location.href="/cart/"; }).catch(function(){ window.location.href="/?add-to-cart="+PID+"&quantity="+qty; }); return false; }, true);
})();
</script>
