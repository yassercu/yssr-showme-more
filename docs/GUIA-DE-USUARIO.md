# Guía de Usuario - YSSR Show Me More

## Índice
1. [Introducción](#introducción)
2. [¿Qué información veo en el frontend?](#información-en-frontend)
   - [Página de Productos](#página-de-productos)
   - [Página de Detalles del Producto](#página-de-detalles)
   - [Carrito de Compras](#carrito-de-compras)
   - [Checkout](#checkout)
   - [Área de Cliente](#área-de-cliente)
3. [Preguntas Frecuentes](#preguntas-frecuentes)
4. [Soporte](#soporte)

## Introducción

¡Bienvenido a YSSR Show Me More! Esta extensión de WooCommerce está diseñada para mostrar información adicional de productos a ciertos roles de usuario en tu tienda en línea. A continuación, te explicamos qué información se muestra y dónde en la experiencia de compra de tus clientes.

## ¿Qué información veo en el frontend?

### Página de Productos

#### Para Clientes Registrados con Permisos:
- **Precio de Costo**: Junto al precio regular, verán el costo del producto.

#### Para Clientes sin Permisos:
- Solo verán la información estándar de WooCommerce (precio, título, imagen).

### Página de Detalles del Producto

#### Para Clientes con Permisos:
1. **Sección de Precios Avanzados**:
   - Precio de venta
   - Precio de costo

2. **Histórico de Precios** (si está habilitado):
   - Variación de precios a lo largo del tiempo
   - Fechas de cambio de precios

### Carrito de Compras

#### Para Clientes con Permisos:
- **Costo Total de los Productos**: Suma de los costos de todos los productos en el carrito.

### Checkout

#### Para Clientes con Permisos:
- **Resumen de Costos**:
  - Subtotal basado en costos
  - Costo total del pedido

### Área de Cliente

#### Historial de Pedidos:
- **Columna de Costo**: Muestra el costo total del pedido junto al total pagado.

#### Detalles del Pedido:
- **Sección de Costos Detallados**:
  - Costo por producto
  - Costo total

## Preguntas Frecuentes

### ¿Quiénes pueden ver la información de costos?
Solo los usuarios con roles específicos (normalmente administradores y gerentes) pueden ver esta información. Los clientes regulares no verán estos datos.

### ¿La información de costos es visible en los correos electrónicos?
No, por defecto la información sensible de costos solo es visible en el área de administración y en el área de cliente para usuarios autorizados.

### ¿Puedo personalizar qué roles ven esta información?
Sí, puedes configurar qué roles tienen acceso a esta información desde el panel de administración de WordPress.

## Soporte

Si tienes alguna pregunta o necesitas ayuda con la configuración, por favor contacta a nuestro equipo de soporte:

- **Correo electrónico:** yssr@isla360shop.com

---

*Última actualización: Junio 2025*
*Versión del documento: 2.0*


### Ver Detalles de Costos

1. En la sección de información de costos, haz clic en el botón "👁 Ver Detalles".
2. Se desplegará una lista con el desglose detallado de costos.
3. La información incluye:
   - Nombre del producto
   - Cantidad
   - Costo unitario
   - Subtotal por producto

## Preguntas Frecuentes

### ¿Por qué no veo la sección de costos en los pedidos?
Asegúrate de que:
- Tu usuario tenga los permisos necesarios (administrador o gestor de tienda).
- El pedido tenga productos con costos definidos.
- El plugin esté correctamente instalado y activado.

### ¿Con qué frecuencia se actualizan los costos?
Los costos se calculan cuando:
- Se crea un nuevo pedido
- Se hace clic manualmente en "Recalcular Costo"
