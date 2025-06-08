<?php
/**
 * Plugin Name: yssr showme more
 * Plugin URI: https://encontruci.om
 * Description: Permite mostrar atributos ocultos de productos WooCommerce (como precio de costo) a roles específicos de usuarios.
 * Version: 1.2.1
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
define('WC_ROLE_ATTR_VERSION', '1.2.1');
define('WC_ROLE_ATTR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_ROLE_ATTR_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Clase principal del plugin
 */
class WC_Role_Attributes_Plugin {
    
    private static $instance = null;
    
    /**
     * Instancia singleton
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Inicializar el plugin
     */
    public function init() {
        // Verificar si WooCommerce está activo
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
     * Cargar dependencias
     */
    private function load_dependencies() {
        require_once WC_ROLE_ATTR_PLUGIN_PATH . 'includes/class-admin.php';
        require_once WC_ROLE_ATTR_PLUGIN_PATH . 'includes/class-frontend.php';
        require_once WC_ROLE_ATTR_PLUGIN_PATH . 'includes/class-settings.php';
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Activación del plugin
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Inicializar clases
        new WC_Role_Attributes_Admin();
        new WC_Role_Attributes_Frontend();
        new WC_Role_Attributes_Settings();
        
        // Agregar enlace de configuración
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links'));
    }
    
    /**
     * Activación del plugin
     */
    public function activate() {
        // Crear opciones por defecto
        $default_options = array(
            'enabled_roles' => array('administrator'),
            'visible_attributes' => array('_cost_price', '_supplier', '_sku_supplier'),
            'display_location' => 'after_price',
            'custom_css' => '.wc-role-attributes { margin: 10px 0; padding: 10px; background: #f9f9f9; border-radius: 4px; }'
        );
        
        add_option('wc_role_attributes_settings', $default_options);
        
        // Crear campos personalizados si no existen
        $this->create_custom_fields();
    }
    
    /**
     * Crear campos personalizados
     */
    private function create_custom_fields() {
        // Estos campos se pueden crear automáticamente o el usuario puede crearlos manualmente
        // Aquí solo definimos los meta_keys que el plugin reconocerá
    }
    
    /**
     * Desactivación del plugin
     */
    public function deactivate() {
        // Limpiar cache si es necesario
        wp_cache_flush();
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
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('WooCommerce Role-Based Product Attributes requiere que WooCommerce esté instalado y activado.', 'wc-role-attributes'); ?></p>
        </div>
        <?php
    }
}

// Inicializar el plugin
WC_Role_Attributes_Plugin::get_instance();

/**
 * Funciones auxiliares
 */

/**
 * Obtener configuraciones del plugin
 */
function wc_role_attr_get_settings() {
    return get_option('wc_role_attributes_settings', array());
}

/**
 * Verificar si el usuario actual puede ver los atributos
 */
function wc_role_attr_user_can_view() {
    if (!is_user_logged_in()) {
        return false;
    }
    
    $settings = wc_role_attr_get_settings();
    $enabled_roles = isset($settings['enabled_roles']) ? $settings['enabled_roles'] : array();
    $current_user = wp_get_current_user();
    
    return !empty(array_intersect($enabled_roles, $current_user->roles));
}

/**
 * Obtener atributos visibles configurados
 */
function wc_role_attr_get_visible_attributes() {
    $settings = wc_role_attr_get_settings();
    return isset($settings['visible_attributes']) ? $settings['visible_attributes'] : array();
}

/**
 * Formatear precio de costo
 */
function wc_role_attr_format_cost_price($price) {
    return wc_price($price);
}

/**
 * Obtener etiqueta amigable para atributo
 */
function wc_role_attr_get_attribute_label($attribute_key) {
    $labels = array(
        '_cost_price' => __('Precio de Costo', 'wc-role-attributes'),
        '_supplier' => __('Proveedor', 'wc-role-attributes'),
        '_sku_supplier' => __('SKU Proveedor', 'wc-role-attributes'),
        '_purchase_date' => __('Fecha de Compra', 'wc-role-attributes'),
        '_margin' => __('Margen', 'wc-role-attributes'),
        '_notes' => __('Notas Internas', 'wc-role-attributes')
    );
    
    return isset($labels[$attribute_key]) ? $labels[$attribute_key] : $attribute_key;
}
