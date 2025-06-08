=== WooCommerce Role-Based Product Attributes ===
Contributors: tu-usuario
Tags: woocommerce, roles, attributes, cost-price, wholesale
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Permite mostrar atributos ocultos de productos WooCommerce (como precio de costo) a roles específicos de usuarios.

== Description ==

Este plugin permite configurar qué atributos especiales de productos WooCommerce (como precio de costo, proveedor, SKU de proveedor, etc.) son visibles para roles específicos de usuarios.

Características principales:
* Configuración de roles con acceso a atributos especiales
* Selección de atributos visibles por rol
* Campos personalizados para precio de costo, proveedor, etc.
* Ubicación configurable de visualización
* CSS personalizable
* Integración completa con WooCommerce

== Installation ==

1. Sube los archivos del plugin al directorio `/wp-content/plugins/wc-role-attributes/`
2. Activa el plugin a través del menú 'Plugins' en WordPress
3. Ve a WooCommerce > Atributos por Rol para configurar el plugin

== Frequently Asked Questions ==

= ¿Funciona con todos los temas? =
Sí, el plugin está diseñado para funcionar con cualquier tema compatible con WooCommerce.

= ¿Puedo personalizar los estilos CSS? =
Sí, puedes agregar CSS personalizado desde la página de configuración del plugin.

== Changelog ==

= 1.1.1 =
* Eliminados los meta boxes personalizados en la edición de productos.
* Depuración y optimización del código: se eliminaron métodos y hooks no utilizados para mayor eficiencia.

= 1.1.0 =
* Compatibilidad total con el plugin Cost of Goods: Product Cost & Profit Calculator for WooCommerce (usa el meta _alg_wc_cog_cost).
* El precio de costo solo se muestra si existe y el plugin Cost of Goods está activo.
* Rediseño visual destacado para el precio de costo, compatible con cualquier tema (incluido Bacola).
* El precio de costo se muestra en la posición configurada (después del precio, antes del botón de agregar al carrito, o después del resumen).
* El precio de costo también se muestra en la tarjeta de producto del catálogo/tienda.
* El menú de configuración del plugin ahora está en Productos > yssr Showme More.
* La página de configuración solo permite seleccionar roles y la posición de visualización.
* Si el plugin Cost of Goods no está activo, se muestra una advertencia y el campo deshabilitado.
* Mejoras de compatibilidad y limpieza de código.

= 1.0.0 =
* Versión inicial del plugin
* Configuración de roles y atributos
* Campos personalizados para productos
* Visualización en frontend basada en roles

= 1.2.0 =
* Nuevo: Si no existe el meta de Cost of Goods (`_alg_wc_cog_cost`), el plugin permite ingresar un costo personalizado por producto y lo muestra en el frontend.
* Se añade un meta box de "Costo personalizado" en la edición de productos cuando no hay costo de Cost of Goods.
* Rediseño de la etiqueta de costo en frontend: ahora es minimalista y sigue el estilo Material Design de Google.
* Mejoras visuales y de compatibilidad.

= 1.2.1 =
* Fix: Ahora la configuración del plugin se guarda y persiste correctamente.
* Mejora de experiencia de usuario en la administración.
