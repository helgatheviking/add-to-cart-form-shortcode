# Add to Cart Form Shortcode for WooCommerce

Add `[add_to_cart_form]` shortcode that display a single product add to cart form for WooCommerce.

The default WooCommerce shortcodes can be a little lacking for complex product types or for products with custom add-ons such as Name Your Price. For example: `[add_to_cart id="99"]` shows only the loop add to cart button, which lacks the hooks that a plugin such as Name Your Price needs to display it's own input.

And the product page shortcode, `[product_page id="99"]` displays the entire product page, which may or may not work well for the layout you are trying to build.... especially with page builders that don't have good WooCommerce modules.

## The [add_to_cart_form] solution

The newly added `[add_to_cart_form]` shortcode lives in between the `[add_to_cart]` and `[product_page]` shortcodes. It displays the entire add to cart form from the single product page, but *only* the form. 

## How to Use

Like any other shortcode you can paste it into your page/post content. Or use it in a page builder's text editor module. The shortcode supports product identification by either product ID:

`[add_to_cart_form id="99"]`

or product SKU:

`[add_to_cart_form sku="GUACAMOLE99"]`

To use in a template you would use:

`echo do_shortcode( '[add_to_cart_form id="99"]' );`

## Additional Parameters

### show_price

By default, the price template will be displayed just prior to the add to cart template. You can hide it by setting the parameter to "false".

`[add_to_cart_form id="99" show_price="false"]`

### hide_quantity

The quantity buttons can look a little weird when displayed outside the product page. You can hide them, by setting the parameter to "true". Effectively, this sets the quantity to 1.
`[add_to_cart_form id="99" hide_quantity="true"]`

### allow_form_action

This form action is disabled by default so the page will refresh in place instead of redirecting to the single product page. However, the form action is filterable and so some users may wish to allow the page to redirect (for example, directly to the cart). To enable the form action, you can use `[add_to_cart_form id="99" allow_form_action="true"]`

## Screenshot

Here's an example of the shortcode used on a simple Name Your Price product

![Add to cart form, displays Name Your Price text input](https://user-images.githubusercontent.com/507025/35475356-b88013fa-0362-11e8-8659-e7a9168065cf.png)

## Download
[Download latest release](https://github.com/helgatheviking/add-to-cart-form-shortcode/releases/latest/download/add-to-cart-form-shortcode.zip)
