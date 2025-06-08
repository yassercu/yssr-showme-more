<?php
/**
 * Clase para el panel de administración
 */
class WC_Role_Attributes_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('add_meta_boxes', array($this, 'add_custom_cost_meta_box'));
        add_action('save_post_product', array($this, 'save_custom_cost_meta_box'));
    }
    
    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=product',
            __('yssr Showme More', 'wc-role-attributes'),
            __('yssr Showme More', 'wc-role-attributes'),
            'manage_woocommerce',
            'wc-role-attributes',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        register_setting('wc_role_attributes_settings', 'wc_role_attributes_settings');
    }
    
    /**
     * Cargar scripts de administración
     */
    public function enqueue_admin_scripts($hook) {
        if ('woocommerce_page_wc-role-attributes' !== $hook) {
            return;
        }
        
        wp_enqueue_style('wc-role-attr-admin', WC_ROLE_ATTR_PLUGIN_URL . 'assets/admin.css', array(), WC_ROLE_ATTR_VERSION);
        wp_enqueue_script('wc-role-attr-admin', WC_ROLE_ATTR_PLUGIN_URL . 'assets/admin.js', array('jquery'), WC_ROLE_ATTR_VERSION, true);
    }
    
    /**
     * Página de administración (rediseñada)
     */
    public function admin_page() {
        $settings = wc_role_attr_get_settings();
        $roles = wp_roles()->get_names();
        $cost_of_goods_active = class_exists('Alg_WC_Cost_of_Goods');
        
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['wc_role_attr_nonce'], 'wc_role_attr_save')) {
            $new_settings = array(
                'enabled_roles' => isset($_POST['enabled_roles']) ? array_map('sanitize_text_field', $_POST['enabled_roles']) : array(),
                'visible_attributes' => isset($_POST['visible_attributes']) ? array_map('sanitize_text_field', $_POST['visible_attributes']) : array(),
                'display_location' => sanitize_text_field($_POST['display_location']),
                'custom_css' => sanitize_textarea_field($_POST['custom_css'])
            );
            
            update_option('wc_role_attributes_settings', $new_settings);
            $settings = $new_settings;
            
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada exitosamente.', 'wc-role-attributes') . '</p></div>';
        }
        ?>
        <style>
        .yssr-admin-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            padding: 32px 32px 24px 32px;
            max-width: 600px;
            margin: 32px auto 0 auto;
            border: 1.5px solid #f2f2f2;
        }
        .yssr-admin-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 2em;
            font-weight: 700;
            color: #ff9800;
            margin-bottom: 18px;
        }
        .yssr-admin-title .dashicon {
            font-size: 1.2em;
            color: #ff9800;
        }
        .yssr-admin-section {
            margin-bottom: 28px;
        }
        .yssr-admin-label {
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
            color: #333;
        }
        .yssr-admin-note {
            color: #a00;
            font-size: 0.98em;
            margin-top: 6px;
        }
        .yssr-admin-select, .yssr-admin-textarea {
            width: 100%;
            padding: 7px 10px;
            border-radius: 6px;
            border: 1.2px solid #e0e0e0;
            font-size: 1em;
            margin-bottom: 4px;
        }
        .yssr-admin-roles {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 24px;
        }
        .yssr-admin-role {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 1em;
        }
        .yssr-admin-submit {
            background: #ff9800;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 28px;
            font-size: 1.1em;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(255,152,0,0.08);
            transition: background 0.2s;
        }
        .yssr-admin-submit:hover {
            background: #e67c00;
        }
        </style>
        <div class="wrap wc-role-attributes-admin">
            <div class="yssr-admin-card">
                <div class="yssr-admin-title">
                    <span class="dashicon dashicons-admin-generic"></span>
                    <?php _e('Configuración de yssr Showme More', 'wc-role-attributes'); ?>
                    <span style="font-size:0.6em;color:#888;vertical-align:middle;">v<?php echo WC_ROLE_ATTR_VERSION; ?></span>
                </div>
            <form method="post" action="">
                <?php wp_nonce_field('wc_role_attr_save', 'wc_role_attr_nonce'); ?>
                    <div class="yssr-admin-section">
                        <label class="yssr-admin-label"><?php _e('Roles que pueden ver el costo', 'wc-role-attributes'); ?></label>
                        <div class="yssr-admin-roles">
                            <?php foreach ($roles as $role_key => $role_name): ?>
                                <label class="yssr-admin-role">
                                    <input type="checkbox" name="enabled_roles[]" value="<?php echo esc_attr($role_key); ?>" 
                                           <?php checked(in_array($role_key, isset($settings['enabled_roles']) ? $settings['enabled_roles'] : array())); ?>>
                                    <?php echo esc_html($role_name); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="yssr-admin-section">
                        <label class="yssr-admin-label"><?php _e('Mostrar Costo', 'wc-role-attributes'); ?></label>
                        <?php if ($cost_of_goods_active): ?>
                            <span style="color:#ff9800;font-weight:600;"><span class="dashicon dashicons-yes"></span> <?php _e('Costo (proporcionado por Cost of Goods)', 'wc-role-attributes'); ?></span>
                        <?php else: ?>
                            <span class="yssr-admin-note"><span class="dashicon dashicons-warning"></span> <?php _e('Debes instalar y activar el plugin "Cost of Goods: Product Cost & Profit Calculator for WooCommerce" para mostrar el costo.', 'wc-role-attributes'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="yssr-admin-section">
                        <label class="yssr-admin-label" for="display_location"><?php _e('Ubicación de Visualización', 'wc-role-attributes'); ?></label>
                        <select class="yssr-admin-select" name="display_location" id="display_location">
                                <option value="after_price" <?php selected(isset($settings['display_location']) ? $settings['display_location'] : 'after_price', 'after_price'); ?>>
                                    <?php _e('Después del precio', 'wc-role-attributes'); ?>
                                </option>
                                <option value="before_add_to_cart" <?php selected(isset($settings['display_location']) ? $settings['display_location'] : '', 'before_add_to_cart'); ?>>
                                    <?php _e('Antes del botón "Agregar al carrito"', 'wc-role-attributes'); ?>
                                </option>
                                <option value="after_summary" <?php selected(isset($settings['display_location']) ? $settings['display_location'] : '', 'after_summary'); ?>>
                                    <?php _e('Después del resumen', 'wc-role-attributes'); ?>
                                </option>
                            </select>
                    </div>
                    <div class="yssr-admin-section">
                        <label class="yssr-admin-label" for="custom_css"><?php _e('CSS Personalizado', 'wc-role-attributes'); ?></label>
                        <textarea class="yssr-admin-textarea" name="custom_css" id="custom_css" rows="4"><?php echo esc_textarea(isset($settings['custom_css']) ? $settings['custom_css'] : ''); ?></textarea>
                        <div style="font-size:0.97em;color:#888;margin-top:4px;">
                            <?php _e('Personaliza el estilo visual de la pegatina de costo si lo deseas.', 'wc-role-attributes'); ?>
                        </div>
                    </div>
                    <button type="submit" class="yssr-admin-submit"><?php _e('Guardar configuración', 'wc-role-attributes'); ?></button>
            </form>
            </div>
        </div>
        <?php
    }
    
    public function add_custom_cost_meta_box() {
        add_meta_box(
            'yssr_custom_cost',
            __('Costo Personalizado (yssr Showme More)', 'wc-role-attributes'),
            array($this, 'render_custom_cost_meta_box'),
            'product',
            'side',
            'default'
        );
    }
    
    public function render_custom_cost_meta_box($post) {
        $cog_cost = get_post_meta($post->ID, '_alg_wc_cog_cost', true);
        $custom_cost = get_post_meta($post->ID, '_yssr_custom_cost', true);
        if ($cog_cost !== '' && $cog_cost !== false) {
            echo '<p style="color:#888;">' . __('El costo de este producto es gestionado por el plugin Cost of Goods.', 'wc-role-attributes') . '</p>';
            return;
        }
        wp_nonce_field('yssr_custom_cost_save', 'yssr_custom_cost_nonce');
        echo '<label for="yssr_custom_cost_field">' . __('Costo personalizado', 'wc-role-attributes') . ':</label>';
        echo '<input type="number" step="0.01" min="0" name="yssr_custom_cost_field" id="yssr_custom_cost_field" value="' . esc_attr($custom_cost) . '" style="width:100%;margin-top:6px;" />';
        echo '<p style="color:#888;font-size:0.97em;">' . __('Este valor solo se usará si no existe un costo definido por el plugin Cost of Goods.', 'wc-role-attributes') . '</p>';
    }
    
    public function save_custom_cost_meta_box($post_id) {
        if (!isset($_POST['yssr_custom_cost_nonce']) || !wp_verify_nonce($_POST['yssr_custom_cost_nonce'], 'yssr_custom_cost_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['yssr_custom_cost_field'])) {
            update_post_meta($post_id, '_yssr_custom_cost', sanitize_text_field($_POST['yssr_custom_cost_field']));
        }
    }
}
