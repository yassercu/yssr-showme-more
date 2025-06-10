<?php
/**
 * Plugin Name: yssr showme more
 * Plugin URI: https://encontruci.om 
 * Description: Permite mostrar atributos ocultos de productos WooCommerce (como precio de costo) a roles específicos de usuarios.
 * Version: 1.6.0
 * Author: CuDev ~ yssr
 * Author URI: https://encontruci.om 
 * Text Domain: yssr-showme-more
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html 
 */
// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}
add_action(
    'before_woocommerce_init',
    function() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
);
// Definir constantes del plugin
define('WC_ROLE_ATTR_VERSION', '1.6.0');
define('WC_ROLE_ATTR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_ROLE_ATTR_PLUGIN_PATH', plugin_dir_path(__FILE__));
/**
 * Clase principal del plugin
 */
class WC_Role_Attributes_Plugin {
    private static ?WC_Role_Attributes_Plugin $instance = null;
    /**
     * Singleton
     * @return WC_Role_Attributes_Plugin
     */
    public static function get_instance(): WC_Role_Attributes_Plugin {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('wp_ajax_yssr_recalculate_order_cost', array($this, 'ajax_recalculate_order_cost'));
    }
    public function init() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        // Cargar archivos necesarios
        $this->load_dependencies();
        // Inicializar componentes
        $this->init_hooks();
        // Cargar textdomain
        load_plugin_textdomain('wc-role-attributes', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    /**
     * Cargar dependencias según contexto
     */
    private function load_dependencies(): void {
        if (is_admin()) {
            require_once WC_ROLE_ATTR_PLUGIN_PATH . 'includes/class-admin.php';
            new WC_Role_Attributes_Admin();
        }
        if (!is_admin() || defined('DOING_AJAX')) {
            require_once WC_ROLE_ATTR_PLUGIN_PATH . 'includes/class-frontend.php';
            new WC_Role_Attributes_Frontend();
        }
        require_once WC_ROLE_ATTR_PLUGIN_PATH . 'includes/class-settings.php';
        new WC_Role_Attributes_Settings();
    }
    /**
     * Inicializar hooks
     */
    private function init_hooks(): void {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links'));
        add_action('woocommerce_checkout_order_created', array($this, 'save_order_cost_total'), 10, 1);
        add_action('woocommerce_payment_complete', array($this, 'save_order_cost_total_on_payment'), 10, 1);
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_admin_order_cost_total'));
        add_action('add_meta_boxes', array($this, 'add_order_cost_metabox'));
        add_action('woocommerce_before_account_orders', array($this, 'display_inventory_summary'));
    }
    /**
     * Guardar total de costo del pedido - Método principal
     */
    public function save_order_cost_total($order): void {
        error_log("YSSR Plugin: Iniciando guardado de costo para pedido #{$order->get_id()} vía 'order_created' hook.");
        $this->calculate_and_save_cost_total($order);
    }
    /**
     * Guardar total de costo del pedido - Método de respaldo
     */
    public function save_order_cost_total_on_payment($order_id): void {
        $order = wc_get_order($order_id);
        if (!$order) return;
        error_log("YSSR Plugin: Iniciando guardado de costo para pedido #{$order_id} vía 'payment_complete' hook.");
        $this->calculate_and_save_cost_total($order);
    }
    /**
     * Función centralizada para calcular y guardar el total de costo
     */
    private function calculate_and_save_cost_total($order): void {
        if (!$order || !is_a($order, 'WC_Order')) {
            error_log("YSSR Plugin: Se intentó calcular el costo pero el objeto de pedido no era válido.");
            return;
        }
        $order_id = $order->get_id();
        if (!defined('YSSR_FORCE_RECALC')) {
            $existing_cost = $order->get_meta('_yssr_total_costo');
            if (!empty($existing_cost) && is_numeric($existing_cost)) {
                error_log("YSSR Plugin: El pedido #{$order_id} ya tiene un costo guardado ($existing_cost). No se recalcula.");
                return;
            }
        }

        $total_costo_productos = 0;
        $detalles_productos = [];

        foreach ($order->get_items() as $item_id => $item) {
            $product_id = $item->get_product_id();
            $variation_id = $item->get_variation_id();
            $id_to_check = $variation_id > 0 ? $variation_id : $product_id;

            if (!$id_to_check) continue;

            $cost_price = wc_role_attr_get_product_cost($id_to_check);
            if (empty($cost_price) && $variation_id > 0) {
                $cost_price = wc_role_attr_get_product_cost($product_id);
            }

            $quantity = $item->get_quantity();
            $product_name = $item->get_name();

            if (!empty($cost_price) && is_numeric($cost_price)) {
                $costo_linea = floatval($cost_price) * intval($quantity);
                $total_costo_productos += $costo_linea;
                $detalles_productos[] = [
                    'name' => $product_name,
                    'quantity' => $quantity,
                    'cost_price' => $cost_price,
                    'line_total' => $costo_linea,
                    'product_id' => $id_to_check
                ];
            } else {
                $detalles_productos[] = [
                    'name' => $product_name,
                    'quantity' => $quantity,
                    'cost_price' => 0,
                    'line_total' => 0,
                    'product_id' => $id_to_check,
                    'error' => 'Sin precio de costo'
                ];
                error_log("YSSR Plugin: No se encontró precio de costo para '{$product_name}' (ID: {$id_to_check}).");
            }
        }

        // Validar y formatear valores con precisión decimal
        $tax_total = floatval($order->get_total_tax());

        // Formatear con precisión decimal según WooCommerce
        $total_costo_productos = wc_format_decimal($total_costo_productos, wc_get_price_decimals());
        $tax_total = wc_format_decimal($tax_total, wc_get_price_decimals());

        // Registrar valores para depuración
        error_log("YSSR Plugin: Cálculo para pedido #{$order_id}");
        error_log("Costo productos: " . $total_costo_productos);
        error_log("Impuestos: " . $tax_total);

        $total_final = $total_costo_productos + $tax_total;

        // Guardar en el pedido
        $order->update_meta_data('_yssr_total_costo', $total_final);
        $order->update_meta_data('_yssr_costo_productos', $total_costo_productos);
        $order->update_meta_data('_yssr_costo_impuestos', $tax_total);
        $order->update_meta_data('_yssr_costo_calculado_fecha', current_time('mysql'));
        $order->update_meta_data('_yssr_detalles_productos', $detalles_productos);
        $order->save();
    }
    /**
     * Método auxiliar para obtener el precio de costo de un producto
     */
    private function get_product_cost_price($product_id) {
        $cost_fields = ['_alg_wc_cog_cost']; // Solo se usa el campo del plugin "Cost of Goods"
        foreach ($cost_fields as $field) {
            $cost = get_post_meta($product_id, $field, true);
            if (!empty($cost) && is_numeric($cost)) {
                return $cost;
            }
        }
        return null;
    }
    /**
     * Muestra el cuadro de información de costos en la página de edición del pedido.
     */
    public function display_admin_order_cost_total($order): void {
        if (!current_user_can('manage_woocommerce')) return;
        $total_costo = $order->get_meta('_yssr_total_costo');
        $fecha_calculo = $order->get_meta('_yssr_costo_calculado_fecha');
        $detalles = $order->get_meta('_yssr_detalles_productos');
        echo '<div class="yssr-admin-cost-info" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #ff9800; border-radius: 4px;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #ff9800;">📊 Información de Costos YSSR</h4>';
        if (is_numeric($total_costo)) {
            echo '<p><strong>Total de Costo:</strong> <span style="font-size: 18px; color: #ff9800; font-weight: bold;">' . wc_price($total_costo) . '</span></p>';
            if (!empty($fecha_calculo)) echo '<p><small><strong>Calculado el:</strong> ' . date_i18n('d/m/Y H:i:s', strtotime($fecha_calculo)) . '</small></p>';
        } else {
            echo '<p><strong>Total de Costo:</strong> <span style="color: #999;">No calculado</span></p>';
        }
        echo '<button type="button" class="button button-small" onclick="yssr_recalculate_cost(' . $order->get_id() . ')" style="margin-right: 10px;">🔄 Recalcular Costo</button>';
        if (!empty($detalles) && is_array($detalles)) {
            echo '<button type="button" class="button button-small" onclick="yssr_toggle_details()">👁 Ver Detalles</button>';
            echo '<div id="yssr-cost-details" style="display: none; margin-top: 15px; max-height: 200px; overflow-y: auto; background: #fff; padding: 10px; border-radius: 4px;">';
            foreach ($detalles as $detalle) {
                echo '<div style="margin-bottom: 8px; padding: 8px; background: #f9f9f9; border-radius: 3px; font-size:12px;">';
                echo '<strong>' . esc_html($detalle['name']) . '</strong><br>';
                echo 'Cant: ' . esc_html($detalle['quantity']) . ' | Costo U: ' . wc_price($detalle['cost_price']) . ' | Total: ' . wc_price($detalle['line_total']);
                if (!empty($detalle['error'])) echo '<br><span style="color: #dc3545;">⚠ ' . esc_html($detalle['error']) . '</span>';
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
        ?>
        <script type="text/javascript">
        function yssr_recalculate_cost(order_id) {
            if (confirm('¿Recalcular el total de costo para este pedido?\nEsto sobrescribirá los valores actuales.')) {
                const button = event.target; button.disabled = true; button.textContent = '⏳ Calculando...';
                jQuery.post(ajaxurl, {
                    action: 'yssr_recalculate_order_cost', order_id: order_id, nonce: '<?php echo wp_create_nonce("yssr_recalc_nonce"); ?>'
                }, function(response) {
                    if (response.success) { alert('✓ Costo recalculado correctamente.'); location.reload(); } 
                    else { alert('❌ Error: ' + response.data); button.disabled = false; button.textContent = '🔄 Recalcular Costo'; }
                }).fail(function() {
                    alert('❌ Error de conexión con el servidor.'); button.disabled = false; button.textContent = '🔄 Recalcular Costo';
                });
            }
        }
        function yssr_toggle_details() {
            const details = document.getElementById('yssr-cost-details'); const button = event.target;
            if (details.style.display === 'none') { details.style.display = 'block'; button.textContent = '🙈 Ocultar Detalles'; } 
            else { details.style.display = 'none'; button.textContent = '👁 Ver Detalles'; }
        }
        </script>
        <?php
    }
    /**
     * Lógica AJAX para recalcular el costo
     */
    public function ajax_recalculate_order_cost(): void {
        if (!wp_verify_nonce($_POST['nonce'], 'yssr_recalc_nonce')) wp_send_json_error('Nonce inválido');
        if (!current_user_can('manage_woocommerce')) wp_send_json_error('Sin permisos');
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        if (!$order) wp_send_json_error('Pedido no encontrado');
        define('YSSR_FORCE_RECALC', true);
        $this->calculate_and_save_cost_total($order);
        $new_cost = $order->get_meta('_yssr_total_costo');
        if (is_numeric($new_cost)) wp_send_json_success('Costo recalculado: ' . wc_price($new_cost));
        else wp_send_json_error('El recálculo no generó un costo.');
    }
    /**
     * Agrega el metabox en la barra lateral del pedido
     */
    public function add_order_cost_metabox(): void {
        if (!wc_role_attr_user_can_view()) return;
        add_meta_box('yssr-order-cost-info', '💰 Información de Costos YSSR', array($this, 'render_order_cost_metabox'), ['shop_order', 'woocommerce_page_wc-orders'], 'side', 'default');
    }
    /**
     * Muestra el contenido del metabox
     */
    public function render_order_cost_metabox($post_or_order_object): void {
        $order = ($post_or_order_object instanceof WP_Post) ? wc_get_order($post_or_order_object->ID) : $post_or_order_object;
        if (!$order) return;
        $total_costo = $order->get_meta('_yssr_total_costo');
        echo '<p><strong>Total de Costo:</strong><br>';
        if (is_numeric($total_costo)) echo '<span style="font-size: 1.2em; color: #ff9800; font-weight: bold;">' . wc_price($total_costo) . '</span>';
        else echo '<span style="color: #999;">No calculado</span>';
        echo '</p>';
    }
    /**
     * Muestra el resumen de inventario en la página "Mi Cuenta"
     */
    public function display_inventory_summary(): void {
        if (!wc_role_attr_user_can_view()) return;
        $user_id = get_current_user_id();
        if (!$user_id) return;
        $now = new DateTime('now', new DateTimeZone(wp_timezone_string()));
        $orders = wc_get_orders(['customer_id' => $user_id, 'status' => ['completed', 'processing', 'on-hold'], 'date_query' => [['year' => $now->format('Y'), 'month' => $now->format('n')]], 'return' => 'objects', 'limit' => -1]);
        echo '<div class="woocommerce-account-inventario yssr-inventario-wrap" style="margin-bottom:3em; padding:1.5em; box-shadow:0 6px 32px rgba(255,152,0,0.13); border:2.5px solid #ff9800; background:#fffbe7; border-radius:8px;">';
        echo '<h2 style="font-size:1.5em; font-weight:700; color:#ff9800; margin-bottom:1em;">Resumen de Inventario del Mes</h2>';
        if (empty($orders)) { echo '<p>No se encontraron pedidos este mes.</p></div>'; return; }
        echo '<div style="overflow-x:auto;"><table class="shop_table shop_table_responsive" style="width:100%;">';
        echo '<thead><tr><th>Pedido</th><th>Fecha</th><th>Estado</th><th style="color:#ff9800;">Total de Costo</th></tr></thead><tbody>';
        $total_general = 0;
        foreach ($orders as $order) {
            $total_costo = $order->get_meta('_yssr_total_costo');
            if (!is_numeric($total_costo)) { $this->calculate_and_save_cost_total($order); $total_costo = $order->get_meta('_yssr_total_costo'); }
            if (is_numeric($total_costo)) $total_general += floatval($total_costo);
            echo '<tr><td><a href="' . esc_url($order->get_view_order_url()) . '">#' . $order->get_order_number() . '</a></td>';
            echo '<td><time datetime="' . $order->get_date_created()->date('c') . '">' . $order->get_date_created()->date_i18n('d M Y') . '</time></td>';
            echo '<td>' . wc_get_order_status_name($order->get_status()) . '</td>';
            echo '<td style="font-weight:bold; color:#ff9800;">' . (is_numeric($total_costo) ? wc_price($total_costo) : 'N/A') . '</td></tr>';
        }
        echo '</tbody><tfoot><tr><th colspan="3" style="text-align:right;">Total General del Mes:</th>';
        echo '<td style="font-weight:bold; color:#ff9800; font-size:1.2em;">' . wc_price($total_general) . '</td></tr></tfoot></table></div></div>';
    }
    /**
     * Activación del plugin
     */
    public function activate(): void {
        add_option('wc_role_attributes_settings', ['enabled_roles' => ['administrator'], 'visible_attributes' => ['_cost_price', '_supplier']]);
        flush_rewrite_rules();
    }
    /**
     * Desactivación del plugin
     */
    public function deactivate(): void {
        flush_rewrite_rules();
    }
    /**
     * Agregar enlaces de acción
     */
    public function add_action_links($links) {
        $settings_link = '<a href="admin.php?page=wc-role-attributes">' . __('Configuración', 'wc-role-attributes') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    /**
     * Aviso si WooCommerce no está instalado
     */
    public function woocommerce_missing_notice(): void {
        echo '<div class="notice notice-error"><p>' . __('"yssr showme more" requiere que WooCommerce esté instalado y activado.', 'wc-role-attributes') . '</p></div>';
    }
}
// Inicializar el plugin
WC_Role_Attributes_Plugin::get_instance();
/**
 * Funciones auxiliares globales
 */
function wc_role_attr_get_settings(): array {
    return get_option('wc_role_attributes_settings', []);
}
function wc_role_attr_user_can_view(): bool {
    if (!is_user_logged_in()) return false;
    $settings = wc_role_attr_get_settings();
    $enabled_roles = isset($settings['enabled_roles']) ? $settings['enabled_roles'] : ['administrator'];
    $user = wp_get_current_user();
    return !empty(array_intersect((array) $user->roles, $enabled_roles)) || user_can($user, 'manage_woocommerce');
}
function wc_role_attr_get_visible_attributes(): array {
    $settings = wc_role_attr_get_settings();
    return isset($settings['visible_attributes']) ? $settings['visible_attributes'] : [];
}
function wc_role_attr_format_cost_price($price): string {
    return wc_price($price);
}
function wc_role_attr_get_attribute_label($attribute_key): string {
    $labels = [
        '_cost_price'     => __('Precio de Costo', 'wc-role-attributes'),
        '_supplier'       => __('Proveedor', 'wc-role-attributes'),
        '_sku_supplier'   => __('SKU Proveedor', 'wc-role-attributes'),
        '_purchase_date'  => __('Fecha de Compra', 'wc-role-attributes'),
        '_margin'         => __('Margen', 'wc-role-attributes'),
        '_notes'          => __('Notas Internas', 'wc-role-attributes')
    ];
    return $labels[$attribute_key] ?? $attribute_key;
}
/**
 * Obtener el costo del producto (centralizado para frontend y backend)
 * @param int $product_id
 * @return float|null
 */
function wc_role_attr_get_product_cost(int $product_id): ?float {
    $cost_fields = ['_alg_wc_cog_cost', '_yssr_custom_cost', '_cost_price', 'cost_price', '_purchase_cost', '_wholesale_price', '_cost', 'wc_cost_price'];
    foreach ($cost_fields as $field) {
        $cost = get_post_meta($product_id, $field, true);
        if ($cost !== '' && $cost !== false && is_numeric($cost)) {
            return floatval($cost);
        }
    }
    return null;
}