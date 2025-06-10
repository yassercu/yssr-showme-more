<?php
/**
 * Clase para el frontend
 */
class WC_Role_Attributes_Frontend {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_action('init', [$this, 'init_display_hooks']);
        add_action('wp_head', [$this, 'add_custom_css']);
        // Hook alternativo para máxima compatibilidad con temas
        add_action('woocommerce_after_single_product', [$this, 'display_attributes_fallback'], 5);
        // Registrar el shortcode
        add_shortcode('wc_role_attributes', [$this, 'shortcode_display_attributes']);
        // Hooks para carrito y checkout
        if (wc_role_attr_user_can_view()) {
            add_filter('woocommerce_cart_item_name', [$this, 'add_cost_column_cart'], 99, 3);
            add_filter('woocommerce_cart_item_price', [$this, 'add_cost_column_cart_price'], 99, 3);
            add_filter('woocommerce_cart_item_subtotal', [$this, 'add_cost_column_cart_subtotal'], 99, 3);
            add_filter('woocommerce_cart_item_columns', [$this, 'add_cost_header_cart'], 99);
            add_action('woocommerce_after_cart_contents', [$this, 'show_cost_subtotal_below_cart']);
            add_action('woocommerce_cart_totals_after_order_total', [$this, 'show_cost_total_cart']);
            add_action('woocommerce_review_order_after_order_total', [$this, 'show_cost_total_checkout']);
        }
    }

    /**
     * Fallback para mostrar atributos si el hook principal falla
     */
    public function display_attributes_fallback(): void {
        if (!wc_role_attr_user_can_view()) {
            return;
        }
        global $product;
        if (!$product) {
            return;
        }
        $this->render_attributes($product->get_id());
    }

    /**
     * Shortcode para mostrar atributos en cualquier lugar
     * Uso: [wc_role_attributes product_id="123"]
     */
    public function shortcode_display_attributes($atts): string {
        if (!wc_role_attr_user_can_view()) {
            return '';
        }
        $atts = shortcode_atts([
            'product_id' => null,
        ], $atts);
        $product_id = $atts['product_id'];
        if (!$product_id) {
            global $product;
            if ($product) {
                $product_id = $product->get_id();
            } else {
                return '';
            }
        }
        ob_start();
        $this->render_attributes($product_id);
        return ob_get_clean();
    }
    
    /**
     * Cargar scripts del frontend
     */
    public function enqueue_frontend_scripts(): void {
        if (is_product() || is_shop() || is_product_category() || is_cart() || is_checkout()) {
            wp_enqueue_style('wc-role-attr-frontend', WC_ROLE_ATTR_PLUGIN_URL . 'assets/frontend.css', [], WC_ROLE_ATTR_VERSION);
        }
    }
    
    /**
     * Inicializar hooks de visualización
     */
    public function init_display_hooks(): void {
        if (!wc_role_attr_user_can_view()) {
            return;
        }
        
        $settings = wc_role_attr_get_settings();
        $location = $settings['display_location'] ?? 'after_price';
        
        switch ($location) {
            case 'after_price':
                add_action('woocommerce_single_product_summary', [$this, 'display_attributes'], 25);
                break;
            case 'before_add_to_cart':
                add_action('woocommerce_single_product_summary', [$this, 'display_attributes'], 29);
                break;
            case 'after_summary':
                add_action('woocommerce_single_product_summary', [$this, 'display_attributes'], 35);
                break;
        }
        
        // Mostrar en la tarjeta de producto del catálogo
        add_action('woocommerce_after_shop_loop_item', [$this, 'display_attributes_shop'], 15);
    }
    
    /**
     * Mostrar atributos en página de producto
     */
    public function display_attributes(): void {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $this->render_attributes($product->get_id());
    }
    
    /**
     * Mostrar atributos en página de tienda
     */
    public function display_attributes_shop(): void {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $this->render_attributes($product->get_id(), true);
    }
    
    /**
     * Renderizar atributos (modificado para solo mostrar precio de costo si existe y usando el meta key correcto)
     */
    public function render_attributes($product_id, $is_shop = false): void {
        $cost = wc_role_attr_get_product_cost($product_id);
        if ($cost === null) {
            return;
        }
        $class = $is_shop ? 'wc-role-attributes wc-role-attributes-shop' : 'wc-role-attributes';
        $output = '<div class="' . esc_attr($class) . '"><div class="wc-role-attr-item"><span class="wc-role-attr-label">' . esc_html__('Costo', 'wc-role-attributes') . ':</span> <span class="wc-role-attr-value">' . wc_price($cost) . '</span></div></div>';
        echo $output;
    }
    
    /**
     * Agregar CSS personalizado
     */
    public function add_custom_css(): void {
        $settings = wc_role_attr_get_settings();
        $custom_css = $settings['custom_css'] ?? '';
        
        if (!empty($custom_css)) {
            echo '<style type="text/css">' . wp_kses_post($custom_css) . '</style>';
        }
    }

    // NUEVO: Agregar columna de costo en el carrito (como columna real)
    public function add_cost_header_cart($columns): array {
        // Insertar la columna de costo después de la columna de precio
        $new_columns = [];
        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;
            if ($key === 'price') {
                $new_columns['cost'] = __('Costo', 'wc-role-attributes');
            }
        }
        return $new_columns;
    }
    public function add_cost_column_cart($product_name, $cart_item, $cart_item_key) {
        return $product_name;
    }
    public function add_cost_column_cart_price($price, $cart_item, $cart_item_key) {
        return $price;
    }
    // Mostrar el costo dentro de la columna de subtotal, debajo del subtotal normal y con estilo diferenciado
    public function add_cost_column_cart_subtotal($subtotal, $cart_item, $cart_item_key) {
        $cost = wc_role_attr_get_product_cost($cart_item['product_id']);
        if ($cost !== null) {
            $cost_total = $cost * $cart_item['quantity'];
            $cost_html = '<div class="wc-role-attr-cost-inline">' . esc_html__('Costo:', 'wc-role-attributes') . ' <span>' . wc_price($cost_total) . '</span></div>';
            return $subtotal . $cost_html;
        }
        return $subtotal;
    }
    // Mostrar el subtotal de costo debajo de la lista de productos del carrito
    public function show_cost_subtotal_below_cart(): void {
        if (!is_cart()) return;
        $cost_subtotal = $this->get_cart_cost_subtotal();
        echo '<div class="wc-role-attr-cost-summary"><span class="wc-role-attr-cost-summary-label">' . esc_html__('Subtotal de Costo de Productos:', 'wc-role-attributes') . '</span> <span class="wc-role-attr-cost-summary-value">' . wc_price($cost_subtotal) . '</span></div>';
    }
    // Mostrar el total de costo debajo del total tradicional en la tabla de totales
    public function show_cost_total_cart(): void {
        $cost_subtotal = $this->get_cart_cost_subtotal();
        $shipping_total = (float) WC()->cart->get_shipping_total();
        $tax_total = (float) WC()->cart->get_taxes_total();
        $cost_total = $cost_subtotal + $shipping_total + $tax_total;
        echo '<tr class="order-cost-total"><th style="color:#219150;">' . esc_html__('Total de Costo', 'wc-role-attributes') . '</th><td data-title="' . esc_attr__('Total de Costo', 'wc-role-attributes') . '" style="color:#219150;font-weight:900;">' . wc_price($cost_total) . '</td></tr>';
    }
    // Checkout: igual que en el carrito
    public function show_cost_total_checkout(): void {
        $cost_subtotal = $this->get_cart_cost_subtotal();
        $shipping_total = (float) WC()->cart->get_shipping_total();
        $tax_total = (float) WC()->cart->get_taxes_total();
        $cost_total = $cost_subtotal + $shipping_total + $tax_total;
        echo '<tr class="order-cost-total"><th style="color:#219150;">' . esc_html__('Total de Costo', 'wc-role-attributes') . '</th><td data-title="' . esc_attr__('Total de Costo', 'wc-role-attributes') . '" style="color:#219150;font-weight:900;">' . wc_price($cost_total) . '</td></tr>';
    }
    // NUEVO: Obtener costo de producto
    private function get_product_cost($product_id) {
        $cost = get_post_meta($product_id, '_alg_wc_cog_cost', true);
        if ($cost === '' || $cost === false) {
            $cost = get_post_meta($product_id, '_yssr_custom_cost', true);
        }
        if ($cost === '' || $cost === false) {
            return false;
        }
        return floatval($cost);
    }
    // NUEVO: Calcular subtotal de costo del carrito
    private function get_cart_cost_subtotal(): float {
        $cost_subtotal = 0;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $cost = wc_role_attr_get_product_cost($cart_item['product_id']);
            if ($cost !== null) {
                $cost_subtotal += $cost * $cart_item['quantity'];
            }
        }
        return $cost_subtotal;
    }
    // NUEVO: Calcular total de costo (subtotal de costo + envío + impuestos)
    private function get_cart_cost_total(): float {
        $cost_subtotal = $this->get_cart_cost_subtotal();
        $shipping_total = WC()->cart->get_shipping_total();
        $tax_total = WC()->cart->get_taxes_total();
        return $cost_subtotal + $shipping_total + $tax_total;
    }
}
