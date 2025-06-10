=== WooCommerce Role-Based Product Attributes (yssr Showme More) ===
Contributors: cudev, yssr
Tags: woocommerce, roles, attributes, cost-price, wholesale, inventory
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Permite mostrar atributos ocultos de productos WooCommerce (como precio de costo) a roles específicos de usuarios, con diseño profesional y código optimizado.

== Descripción ==

Este plugin permite configurar qué atributos especiales de productos WooCommerce (como precio de costo, proveedor, SKU de proveedor, etc.) son visibles para roles específicos de usuarios. Incluye integración con el plugin Cost of Goods, soporte para campos personalizados y visualización avanzada en el frontend y backend.

**Características principales:**
* Configuración de roles con acceso a atributos especiales
* Selección de atributos visibles por rol
* Campos personalizados para precio de costo, proveedor, etc.
* Ubicación configurable de visualización (después del precio, antes del botón, después del resumen)
* CSS personalizable desde la administración
* Integración completa con WooCommerce y Cost of Goods
* Visualización de costos en carrito y checkout solo para roles permitidos
* Código optimizado, seguro y estandarizado (PSR-12, PHPDoc, tipado)
* Diseño visual profesional y consistente (Material Design)
* Listo para traducción y compatible con WPML

== Instalación ==

1. Sube los archivos del plugin al directorio `/wp-content/plugins/yssr-showme-more/` o instala el ZIP desde el panel de WordPress.
2. Activa el plugin desde el menú 'Plugins' en WordPress.
3. Ve a Productos > yssr Showme More para configurar los roles, atributos y estilos.

== Preguntas Frecuentes ==

= ¿Funciona con todos los temas? =
Sí, el plugin está diseñado para funcionar con cualquier tema compatible con WooCommerce.

= ¿Puedo personalizar los estilos CSS? =
Sí, puedes agregar CSS personalizado desde la página de configuración del plugin.

= ¿Qué meta campos de costo soporta? =
Soporta `_alg_wc_cog_cost` (Cost of Goods), `_yssr_custom_cost` y otros campos comunes de costo.

= ¿El plugin es seguro y eficiente? =
Sí, el código sigue buenas prácticas, validación y sanitización de datos, y está optimizado para rendimiento y seguridad.

== Changelog ==

= 1.6.1 =
* Actualizada la interfaz de administración con notificaciones modernas
* Reemplazo de alertas nativas por un sistema de notificaciones personalizado
* Mejora en la experiencia de usuario al recalcular costos
* Corrección de errores menores en la visualización de costos
* Actualización de documentación y guía de usuario

= 1.6.0 =
* Refactorización completa: tipado, visibilidad, PHPDoc y centralización de lógica.
* Carga condicional de clases admin/frontend para mejor rendimiento.
* Estandarización visual y de código (PSR-12, arrays cortos, null coalescente).
* Mejoras en la documentación y ejemplos de uso.
* Mejoras en la validación y manejo de configuraciones.

= 1.3.0 =
* Integración visual y lógica del costo en el frontend del carrito y checkout.
* Subtotal de costo de productos mostrado debajo de la lista de productos en el carrito.
* Total de costo mostrado debajo del total tradicional, sumando correctamente impuestos y envío.
* Visualización de costos solo para roles permitidos.
* Diseño minimalista, profesional y destacado para los elementos de costo.
* Listo para producción.

= 1.2.1 =
* Fix: Ahora la configuración del plugin se guarda y persiste correctamente.
* Mejora de experiencia de usuario en la administración.

= 1.2.0 =
* Nuevo: Si no existe el meta de Cost of Goods (`_alg_wc_cog_cost`), el plugin permite ingresar un costo personalizado por producto y lo muestra en el frontend.
* Se añade un meta box de "Costo personalizado" en la edición de productos cuando no hay costo de Cost of Goods.
* Rediseño de la etiqueta de costo en frontend: ahora es minimalista y sigue el estilo Material Design de Google.
* Mejoras visuales y de compatibilidad.

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


