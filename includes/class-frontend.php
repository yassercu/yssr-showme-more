<?php
/**
 * Clase para el frontend
 */
class WC_Role_Attributes_Frontend {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('init', array($this, 'init_display_hooks'));
        add_action('wp_head', array($this, 'add_custom_css'));
        // Hook alternativo para máxima compatibilidad con temas
        add_action('woocommerce_after_single_product', array($this, 'display_attributes_fallback'), 5);
        // Registrar el shortcode
        add_shortcode('wc_role_attributes', array($this, 'shortcode_display_attributes'));
    }

    /**
     * Fallback para mostrar atributos si el hook principal falla
     */
    public function display_attributes_fallback() {
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
    public function shortcode_display_attributes($atts) {
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
    public function enqueue_frontend_scripts() {
        if (is_product() || is_shop() || is_product_category()) {
            wp_enqueue_style('wc-role-attr-frontend', WC_ROLE_ATTR_PLUGIN_URL . 'assets/frontend.css', array(), WC_ROLE_ATTR_VERSION);
        }
    }
    
    /**
     * Inicializar hooks de visualización
     */
    public function init_display_hooks() {
        if (!wc_role_attr_user_can_view()) {
            return;
        }
        
        $settings = wc_role_attr_get_settings();
        $location = isset($settings['display_location']) ? $settings['display_location'] : 'after_price';
        
        switch ($location) {
            case 'after_price':
                add_action('woocommerce_single_product_summary', array($this, 'display_attributes'), 25);
                break;
            case 'before_add_to_cart':
                add_action('woocommerce_single_product_summary', array($this, 'display_attributes'), 29);
                break;
            case 'after_summary':
                add_action('woocommerce_single_product_summary', array($this, 'display_attributes'), 35);
                break;
        }
        
        // Mostrar en la tarjeta de producto del catálogo
        add_action('woocommerce_after_shop_loop_item', array($this, 'display_attributes_shop'), 15);
    }
    
    /**
     * Mostrar atributos en página de producto
     */
    public function display_attributes() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $this->render_attributes($product->get_id());
    }
    
    /**
     * Mostrar atributos en página de tienda
     */
    public function display_attributes_shop() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $this->render_attributes($product->get_id(), true);
    }
    
    /**
     * Renderizar atributos (modificado para solo mostrar precio de costo si existe y usando el meta key correcto)
     */
    public function render_attributes($product_id, $is_shop = false) {
        $cost = get_post_meta($product_id, '_alg_wc_cog_cost', true);
        if ($cost === '' || $cost === false) {
            // Fallback: usar el meta personalizado si no existe el de Cost of Goods
            $cost = get_post_meta($product_id, '_yssr_custom_cost', true);
        }
        if ($cost === '' || $cost === false) {
            return;
        }
        $class = $is_shop ? 'wc-role-attributes wc-role-attributes-shop' : 'wc-role-attributes';
        $output = '<div class="' . esc_attr($class) . '"><div class="wc-role-attr-item"><span class="wc-role-attr-label">' . esc_html__('Costo', 'wc-role-attributes') . ':</span> <span class="wc-role-attr-value">' . wc_price($cost) . '</span></div></div>';
        echo $output;
    }
    
    /**
     * Agregar CSS personalizado
     */
    public function add_custom_css() {
        $settings = wc_role_attr_get_settings();
        $custom_css = isset($settings['custom_css']) ? $settings['custom_css'] : '';
        
        if (!empty($custom_css)) {
            echo '<style type="text/css">' . wp_kses_post($custom_css) . '</style>';
        }
    }
}
