<?php
/**
 * Clase para configuraciones adicionales
 */
class WC_Role_Attributes_Settings {
    
    public function __construct() {
        // No se requiere integración con admin_init
    }
    
    /**
     * Obtener configuración por defecto
     * @return array
     */
    public static function get_default_settings(): array {
        return array(
            'enabled_roles' => array('administrator'),
            'visible_attributes' => array('_alg_wc_cog_cost'),
            'display_location' => 'after_price',
            'custom_css' => '.wc-role-attributes { margin: 10px 0; padding: 10px; background: #fff; border-radius: 4px; }'
        );
    }
    
    /**
     * Validar configuraciones
     * @param array $settings
     * @return array
     */
    public static function validate_settings(array $settings): array {
        $available_roles = array_keys(wp_roles()->get_names());
        $settings['enabled_roles'] = array_intersect($settings['enabled_roles'], $available_roles);
        // Solo permitir el atributo de costo
        $settings['visible_attributes'] = array('_alg_wc_cog_cost');
        return $settings;
    }
}
