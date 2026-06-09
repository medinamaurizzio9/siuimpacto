-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para impacto_urbanizaciones
CREATE DATABASE IF NOT EXISTS `impacto_urbanizaciones` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `impacto_urbanizaciones`;

-- Volcando estructura para tabla impacto_urbanizaciones.asesores
CREATE TABLE IF NOT EXISTS `asesores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `supervisor_id` bigint unsigned DEFAULT NULL,
  `grupo_comercial_id` bigint unsigned DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ci` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `celular` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grupo_comercial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asesores_ci_unique` (`ci`),
  UNIQUE KEY `asesores_email_unique` (`email`),
  KEY `asesores_user_id_foreign` (`user_id`),
  KEY `asesores_supervisor_id_activo_index` (`supervisor_id`,`activo`),
  KEY `asesores_grupo_comercial_id_foreign` (`grupo_comercial_id`),
  CONSTRAINT `asesores_grupo_comercial_id_foreign` FOREIGN KEY (`grupo_comercial_id`) REFERENCES `grupos_comerciales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asesores_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asesores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.asesores: ~1 rows (aproximadamente)
DELETE FROM `asesores`;
INSERT INTO `asesores` (`id`, `user_id`, `supervisor_id`, `grupo_comercial_id`, `nombre`, `apellido`, `ci`, `celular`, `email`, `direccion`, `grupo_comercial`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 4, 3, 1, 'Asesor', 'de Ventas', 'VEN-100', '70000002', 'vendedor@impacto.test', 'Oficina comercial', NULL, 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31');

-- Volcando estructura para tabla impacto_urbanizaciones.audit_logs
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `modelo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modelo_id` bigint unsigned DEFAULT NULL,
  `accion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `datos_anteriores` json DEFAULT NULL,
  `datos_nuevos` json DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_modelo_modelo_id_index` (`modelo`,`modelo_id`),
  KEY `audit_logs_user_id_accion_index` (`user_id`,`accion`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.audit_logs: ~9 rows (aproximadamente)
DELETE FROM `audit_logs`;
INSERT INTO `audit_logs` (`id`, `user_id`, `modelo`, `modelo_id`, `accion`, `descripcion`, `datos_anteriores`, `datos_nuevos`, `ip`, `user_agent`, `created_at`) VALUES
	(1, 1, 'Role', NULL, 'configurar_roles_permisos', 'Roles y permisos iniciales configurados por seeder.', NULL, '{"permissions": ["ver dashboard", "ver lotes", "ver clientes", "ver ventas", "ver reservas", "crear urbanizaciones", "editar urbanizaciones", "eliminar urbanizaciones", "crear manzanos", "editar manzanos", "eliminar manzanos", "crear lotes", "editar lotes", "eliminar lotes", "crear clientes", "editar clientes", "eliminar clientes", "crear ventas", "editar ventas", "anular ventas", "crear reservas", "editar reservas", "cancelar reservas", "cobrar cuotas", "convertir reservas", "ver reservas equipo", "anular caja", "ver reportes", "exportar reportes", "ver reporte reservas", "exportar reporte reservas", "ver reporte mejor vendedor", "exportar reporte mejor vendedor", "administrar usuarios", "crear supervisores", "editar supervisores", "desactivar supervisores", "crear grupos comerciales", "editar grupos comerciales", "crear asesores", "editar asesores", "desactivar asesores", "asignar urbanizaciones a asesores", "resetear contraseña asesor", "asignar urbanizaciones a grupos", "ver reporte comercial", "exportar reporte comercial", "gestionar supervisores comerciales", "gestionar supervisores de ventas"]}', NULL, NULL, '2026-06-05 06:54:31'),
	(2, 1, 'SystemSetting', 1, 'cambiar_configuracion_sistema', 'Configuracion general del sistema actualizada.', '{"nit": "", "email": "", "ciudad": "Santa Cruz", "celular": "", "website": "", "logo_pdf": "", "telefono": "", "whatsapp": "", "direccion": "", "logo_main": "", "logo_login": "", "footer_text": "Version piloto - MVP funcional.", "system_name": "IMPACTO URBANIZACIONES", "company_name": "IMPACTO URBANIZACIONES", "departamento": "Santa Cruz", "razon_social": "IMPACTO URBANIZACIONES", "primary_color": "#0f766e", "secondary_color": "#0f2530", "system_subtitle": "Sistema Integral de Terrenos"}', '{"nit": "", "email": "xwebia6@gmail.com", "ciudad": "La Paz", "celular": "75865765", "website": "", "logo_pdf": "logos/66lwm91cOwagUoXpYsb1MN6VCSwM4icz7tBVqZuV.png", "telefono": "", "whatsapp": "", "direccion": "Av 20 de octubre 2608 Sopocachi", "logo_main": "", "logo_login": "logos/9R3aF271r2vqL7a3Zcd4TxZN8vIfu5Sa72FrJYTv.png", "footer_text": "desarrollado por Xweb Ingenieria", "system_name": "INMOLIDER CRM", "company_name": "INMOLIDER LV", "departamento": "La Paz", "razon_social": "INMOLIDER LV", "primary_color": "#40726e", "secondary_color": "#182f0f", "system_subtitle": "Sistema Integral de Terrenos"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-05 07:10:50'),
	(3, 1, 'SystemSetting', 1, 'cambiar_configuracion_sistema', 'Configuracion general del sistema actualizada.', '{"nit": "", "email": "xwebia6@gmail.com", "ciudad": "La Paz", "celular": "75865765", "website": "", "logo_pdf": "logos/66lwm91cOwagUoXpYsb1MN6VCSwM4icz7tBVqZuV.png", "telefono": "", "whatsapp": "", "direccion": "Av 20 de octubre 2608 Sopocachi", "logo_main": "", "logo_login": "logos/9R3aF271r2vqL7a3Zcd4TxZN8vIfu5Sa72FrJYTv.png", "footer_text": "desarrollado por Xweb Ingenieria", "system_name": "INMOLIDER CRM", "company_name": "INMOLIDER LV", "departamento": "La Paz", "razon_social": "INMOLIDER LV", "primary_color": "#40726e", "secondary_color": "#182f0f", "system_subtitle": "Sistema Integral de Terrenos"}', '{"nit": "", "email": "xwebia6@gmail.com", "ciudad": "La Paz", "celular": "75865765", "website": "", "logo_pdf": "logos/66lwm91cOwagUoXpYsb1MN6VCSwM4icz7tBVqZuV.png", "telefono": "", "whatsapp": "", "direccion": "Av 20 de octubre 2608 Sopocachi", "logo_main": "", "logo_login": "logos/9R3aF271r2vqL7a3Zcd4TxZN8vIfu5Sa72FrJYTv.png", "footer_text": "desarrollado por Xweb Ingenieria", "system_name": "INMOLIDER CRM", "company_name": "INMOLIDER LV", "departamento": "La Paz", "razon_social": "INMOLIDER LV", "primary_color": "#40726e", "secondary_color": "#182f0f", "system_subtitle": "Sistema Integral de Terrenos"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-05 07:10:55'),
	(4, 1, 'SystemSetting', 1, 'cambiar_configuracion_sistema', 'Configuracion general del sistema actualizada.', '{"nit": "", "email": "xwebia6@gmail.com", "ciudad": "La Paz", "celular": "75865765", "website": "", "logo_pdf": "logos/66lwm91cOwagUoXpYsb1MN6VCSwM4icz7tBVqZuV.png", "telefono": "", "whatsapp": "", "direccion": "Av 20 de octubre 2608 Sopocachi", "logo_main": "", "logo_login": "logos/9R3aF271r2vqL7a3Zcd4TxZN8vIfu5Sa72FrJYTv.png", "footer_text": "desarrollado por Xweb Ingenieria", "system_name": "INMOLIDER CRM", "company_name": "INMOLIDER LV", "departamento": "La Paz", "razon_social": "INMOLIDER LV", "primary_color": "#40726e", "secondary_color": "#182f0f", "system_subtitle": "Sistema Integral de Terrenos", "login_background": ""}', '{"nit": "", "email": "xwebia6@gmail.com", "ciudad": "La Paz", "celular": "75865765", "website": "", "logo_pdf": "logos/66lwm91cOwagUoXpYsb1MN6VCSwM4icz7tBVqZuV.png", "telefono": "", "whatsapp": "", "direccion": "Av 20 de octubre 2608 Sopocachi", "logo_main": "", "logo_login": "logos/9R3aF271r2vqL7a3Zcd4TxZN8vIfu5Sa72FrJYTv.png", "footer_text": "desarrollado por Xweb Ingenieria", "system_name": "INMOLIDER CRM", "company_name": "INMOLIDER LV", "departamento": "La Paz", "razon_social": "INMOLIDER LV", "primary_color": "#40726e", "secondary_color": "#182f0f", "system_subtitle": "Sistema Integral de Terrenos", "login_background": "login-backgrounds/zbcTuVc5WSk10WFu1kplAkeagPt2kwjQMvMZTrZc.png"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-05 08:27:54'),
	(5, 1, 'Reserva', 4, 'crear_reserva', 'Reserva creada.', NULL, '{"id": 4, "estado": "activa", "lote_id": "8", "cliente_id": "3", "created_at": "2026-06-05T05:00:29.000000Z", "updated_at": "2026-06-05T05:00:29.000000Z", "usuario_id": 1, "fecha_reserva": "2026-06-05T00:00:00.000000Z", "monto_reserva": "100", "observaciones": null, "tipo_operacion": "credito", "fecha_vencimiento": "2026-06-12T00:00:00.000000Z"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-05 09:00:29'),
	(6, 1, 'Reserva', 4, 'editar_reserva', 'Reserva actualizada.', '{"id": 4, "lote": {"id": 8, "fila": 2, "codigo": "08", "estado": "reservado", "precio": "23000.00", "columna": 3, "coord_x": "35.72", "coord_y": "21.79", "manzano": {"id": 1, "orden": 1, "codigo": "A", "nombre": "Manzano A", "created_at": "2026-06-05T02:54:31.000000Z", "updated_at": "2026-06-05T02:54:31.000000Z", "urbanizacion_id": 1}, "created_at": "2026-06-05T02:54:31.000000Z", "manzano_id": 1, "superficie": "344.00", "updated_at": "2026-06-05T05:00:29.000000Z", "observaciones": null}, "estado": "activa", "lote_id": 8, "cliente_id": 3, "created_at": "2026-06-05T05:00:29.000000Z", "updated_at": "2026-06-05T05:00:29.000000Z", "usuario_id": 1, "vendedor_id": null, "fecha_reserva": "2026-06-05T00:00:00.000000Z", "monto_reserva": "100.00", "observaciones": null, "tipo_operacion": "credito", "urbanizacion_id": null, "fecha_vencimiento": "2026-06-12T00:00:00.000000Z", "grupo_comercial_id": null, "supervisor_ventas_id": null, "supervisor_comercial_id": null}', '{"id": 4, "estado": "vencida", "lote_id": 8, "cliente_id": 3, "created_at": "2026-06-05T05:00:29.000000Z", "updated_at": "2026-06-05T05:42:20.000000Z", "usuario_id": 1, "vendedor_id": null, "fecha_reserva": "2026-06-05T00:00:00.000000Z", "monto_reserva": "100.00", "observaciones": null, "tipo_operacion": "contado", "urbanizacion_id": null, "fecha_vencimiento": "2026-06-12T00:00:00.000000Z", "grupo_comercial_id": null, "supervisor_ventas_id": null, "supervisor_comercial_id": null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-05 09:42:20'),
	(7, 1, 'Venta', 5, 'crear_venta', 'Venta registrada.', NULL, '{"id": 5, "estado": "activa", "lote_id": "8", "user_id": 1, "cliente_id": "1", "created_at": "2026-06-05T05:49:20.000000Z", "updated_at": "2026-06-05T05:49:20.000000Z", "fecha_venta": "2026-06-05T00:00:00.000000Z", "precio_final": "24000", "cuota_inicial": "5000", "numero_cuotas": "36", "observaciones": null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-05 09:49:20'),
	(8, 1, 'CashMovement', 1, 'movimiento_inicial_venta_actualizado', 'ACTUALIZACION', '{"id": 1, "tipo": "ingreso", "fecha": "2026-06-02T00:00:00.000000Z", "monto": "17750.00", "estado": "confirmado", "sale_id": 1, "user_id": 1, "concepto": "contado", "cliente_id": 1, "created_at": "2026-06-05T02:54:31.000000Z", "referencia": "TRF-00045", "updated_at": "2026-06-05T02:54:31.000000Z", "metodo_pago": "transferencia", "observaciones": null, "installment_id": null, "reservation_id": null}', '{"id": 1, "tipo": "ingreso", "fecha": "2026-06-02T00:00:00.000000Z", "monto": "5000.00", "estado": "confirmado", "sale_id": 1, "user_id": 1, "concepto": "anticipo", "cliente_id": 1, "created_at": "2026-06-05T02:54:31.000000Z", "referencia": "TRF-00045", "updated_at": "2026-06-05T06:16:04.000000Z", "metodo_pago": "transferencia", "observaciones": null, "installment_id": null, "reservation_id": null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-05 10:16:04'),
	(9, 1, 'Venta', 1, 'venta_actualizada', 'ACTUALIZACION', '{"id": 1, "lote": {"id": 1, "fila": 1, "codigo": "01", "estado": "vendido", "precio": "17750.00", "columna": 1, "coord_x": "28.04", "coord_y": "16.47", "created_at": "2026-06-05T02:54:31.000000Z", "manzano_id": 1, "superficie": "288.00", "updated_at": "2026-06-05T03:28:58.000000Z", "observaciones": null}, "cuotas": [], "estado": "completada", "lote_id": 1, "user_id": 1, "cliente_id": 1, "created_at": "2026-06-05T02:54:31.000000Z", "reserva_id": null, "tipo_venta": "contado", "updated_at": "2026-06-05T02:54:31.000000Z", "fecha_venta": "2026-06-02T00:00:00.000000Z", "monto_total": "17750.00", "vendedor_id": null, "cuotas_antes": [], "precio_final": "17750.00", "cuota_inicial": "0.00", "numero_cuotas": 0, "observaciones": "Venta al contado de demostracion.", "cash_movements": [{"id": 1, "tipo": "ingreso", "fecha": "2026-06-02T00:00:00.000000Z", "monto": "17750.00", "estado": "confirmado", "sale_id": 1, "user_id": 1, "concepto": "contado", "cliente_id": 1, "created_at": "2026-06-05T02:54:31.000000Z", "referencia": "TRF-00045", "updated_at": "2026-06-05T02:54:31.000000Z", "metodo_pago": "transferencia", "observaciones": null, "installment_id": null, "reservation_id": null}], "saldo_financiar": "0.00", "urbanizacion_id": 1, "grupo_comercial_id": null, "usuario_creador_id": 1, "supervisor_ventas_id": null, "supervisor_comercial_id": null, "usuario_actualizador_id": 1}', '{"id": 1, "estado": "completada", "lote_id": 1, "user_id": 1, "venta_id": 1, "cliente_id": 1, "created_at": "2026-06-05T02:54:31.000000Z", "reserva_id": null, "tipo_venta": "contado", "updated_at": "2026-06-05T06:16:04.000000Z", "fecha_venta": "2026-06-02T00:00:00.000000Z", "monto_total": "17750.00", "vendedor_id": null, "precio_final": "17750.00", "cuota_inicial": "5000.00", "motivo_cambio": "ACTUALIZACION", "numero_cuotas": 9, "observaciones": "Venta al contado de demostracion.", "cuotas_creadas": [{"id": 52, "monto": 1416.67, "estado": "pendiente", "numero": 1, "venta_id": 1, "created_at": "2026-06-05T06:16:04.000000Z", "updated_at": "2026-06-05T06:16:04.000000Z", "monto_pagado": 0, "saldo_pendiente": 1416.67, "fecha_programada": "2026-07-02T00:00:00.000000Z", "fecha_vencimiento": "2026-07-02T00:00:00.000000Z"}, {"id": 53, "monto": 1416.67, "estado": "pendiente", "numero": 2, "venta_id": 1, "created_at": "2026-06-05T06:16:04.000000Z", "updated_at": "2026-06-05T06:16:04.000000Z", "monto_pagado": 0, "saldo_pendiente": 1416.67, "fecha_programada": "2026-08-02T00:00:00.000000Z", "fecha_vencimiento": "2026-08-02T00:00:00.000000Z"}, {"id": 54, "monto": 1416.67, "estado": "pendiente", "numero": 3, "venta_id": 1, "created_at": "2026-06-05T06:16:04.000000Z", "updated_at": "2026-06-05T06:16:04.000000Z", "monto_pagado": 0, "saldo_pendiente": 1416.67, "fecha_programada": "2026-09-02T00:00:00.000000Z", "fecha_vencimiento": "2026-09-02T00:00:00.000000Z"}, {"id": 55, "monto": 1416.67, "estado": "pendiente", "numero": 4, "venta_id": 1, "created_at": "2026-06-05T06:16:04.000000Z", "updated_at": "2026-06-05T06:16:04.000000Z", "monto_pagado": 0, "saldo_pendiente": 1416.67, "fecha_programada": "2026-10-02T00:00:00.000000Z", "fecha_vencimiento": "2026-10-02T00:00:00.000000Z"}, {"id": 56, "monto": 1416.67, "estado": "pendiente", "numero": 5, "venta_id": 1, "created_at": "2026-06-05T06:16:04.000000Z", "updated_at": "2026-06-05T06:16:04.000000Z", "monto_pagado": 0, "saldo_pendiente": 1416.67, "fecha_programada": "2026-11-02T00:00:00.000000Z", "fecha_vencimiento": "2026-11-02T00:00:00.000000Z"}, {"id": 57, "monto": 1416.67, "estado": "pendiente", "numero": 6, "venta_id": 1, "created_at": "2026-06-05T06:16:04.000000Z", "updated_at": "2026-06-05T06:16:04.000000Z", "monto_pagado": 0, "saldo_pendiente": 1416.67, "fecha_programada": "2026-12-02T00:00:00.000000Z", "fecha_vencimiento": "2026-12-02T00:00:00.000000Z"}, {"id": 58, "monto": 1416.67, "estado": "pendiente", "numero": 7, "venta_id": 1, "created_at": "2026-06-05T06:16:04.000000Z", "updated_at": "2026-06-05T06:16:04.000000Z", "monto_pagado": 0, "saldo_pendiente": 1416.67, "fecha_programada": "2027-01-02T00:00:00.000000Z", "fecha_vencimiento": "2027-01-02T00:00:00.000000Z"}, {"id": 59, "monto": 1416.67, "estado": "pendiente", "numero": 8, "venta_id": 1, "created_at": "2026-06-05T06:16:04.000000Z", "updated_at": "2026-06-05T06:16:04.000000Z", "monto_pagado": 0, "saldo_pendiente": 1416.67, "fecha_programada": "2027-02-02T00:00:00.000000Z", "fecha_vencimiento": "2027-02-02T00:00:00.000000Z"}, {"id": 60, "monto": 1416.64, "estado": "pendiente", "numero": 9, "venta_id": 1, "created_at": "2026-06-05T06:16:04.000000Z", "updated_at": "2026-06-05T06:16:04.000000Z", "monto_pagado": 0, "saldo_pendiente": 1416.64, "fecha_programada": "2027-03-02T00:00:00.000000Z", "fecha_vencimiento": "2027-03-02T00:00:00.000000Z"}], "saldo_financiar": "12750.00", "urbanizacion_id": 1, "cuotas_eliminadas": [], "cuotas_conservadas": [], "grupo_comercial_id": null, "usuario_creador_id": 1, "supervisor_ventas_id": null, "supervisor_comercial_id": null, "usuario_actualizador_id": 1, "movimientos_caja_actualizados": [{"antes": {"id": 1, "tipo": "ingreso", "fecha": "2026-06-02T00:00:00.000000Z", "monto": "17750.00", "estado": "confirmado", "sale_id": 1, "user_id": 1, "concepto": "contado", "cliente_id": 1, "created_at": "2026-06-05T02:54:31.000000Z", "referencia": "TRF-00045", "updated_at": "2026-06-05T02:54:31.000000Z", "metodo_pago": "transferencia", "observaciones": null, "installment_id": null, "reservation_id": null}, "accion": "actualizado", "despues": {"id": 1, "tipo": "ingreso", "fecha": "2026-06-02T00:00:00.000000Z", "monto": "5000.00", "estado": "confirmado", "sale_id": 1, "user_id": 1, "concepto": "anticipo", "cliente_id": 1, "created_at": "2026-06-05T02:54:31.000000Z", "referencia": "TRF-00045", "updated_at": "2026-06-05T06:16:04.000000Z", "metodo_pago": "transferencia", "observaciones": null, "installment_id": null, "reservation_id": null}}]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-05 10:16:04');

-- Volcando estructura para tabla impacto_urbanizaciones.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.cache: ~2 rows (aproximadamente)
DELETE FROM `cache`;
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('impacto-urbanizaciones-cache-spatie.permission.cache', 'a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:53:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:20:"ver reporte reservas";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:4;i:2;i:5;i:3;i:6;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:25:"exportar reporte reservas";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:4;i:2;i:5;i:3;i:6;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:26:"ver reporte mejor vendedor";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:31:"exportar reporte mejor vendedor";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:18:"crear supervisores";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:19:"editar supervisores";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:6;a:4:{s:1:"a";i:7;s:1:"b";s:23:"desactivar supervisores";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:7;a:4:{s:1:"a";i:8;s:1:"b";s:24:"crear grupos comerciales";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:8;a:4:{s:1:"a";i:9;s:1:"b";s:25:"editar grupos comerciales";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:9;a:4:{s:1:"a";i:10;s:1:"b";s:31:"asignar urbanizaciones a grupos";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:10;a:4:{s:1:"a";i:11;s:1:"b";s:21:"ver reporte comercial";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:11;a:4:{s:1:"a";i:12;s:1:"b";s:26:"exportar reporte comercial";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:"a";i:13;s:1:"b";s:34:"gestionar supervisores comerciales";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:32:"gestionar supervisores de ventas";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:13:"ver dashboard";s:1:"c";s:3:"web";s:1:"r";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:9:"ver lotes";s:1:"c";s:3:"web";s:1:"r";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:12:"ver clientes";s:1:"c";s:3:"web";s:1:"r";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:10:"ver ventas";s:1:"c";s:3:"web";s:1:"r";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:18;a:4:{s:1:"a";i:19;s:1:"b";s:12:"ver reservas";s:1:"c";s:3:"web";s:1:"r";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:20:"crear urbanizaciones";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:21:"editar urbanizaciones";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:23:"eliminar urbanizaciones";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:14:"crear manzanos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:15:"editar manzanos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:17:"eliminar manzanos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:11:"crear lotes";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:12:"editar lotes";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:14:"eliminar lotes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:14:"crear clientes";s:1:"c";s:3:"web";s:1:"r";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:29;a:4:{s:1:"a";i:30;s:1:"b";s:15:"editar clientes";s:1:"c";s:3:"web";s:1:"r";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:30;a:4:{s:1:"a";i:31;s:1:"b";s:17:"eliminar clientes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:31;a:4:{s:1:"a";i:32;s:1:"b";s:12:"crear ventas";s:1:"c";s:3:"web";s:1:"r";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:32;a:4:{s:1:"a";i:33;s:1:"b";s:13:"editar ventas";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:5;}}i:33;a:4:{s:1:"a";i:34;s:1:"b";s:13:"anular ventas";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:34;a:4:{s:1:"a";i:35;s:1:"b";s:14:"crear reservas";s:1:"c";s:3:"web";s:1:"r";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:35;a:4:{s:1:"a";i:36;s:1:"b";s:15:"editar reservas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:36;a:4:{s:1:"a";i:37;s:1:"b";s:17:"cancelar reservas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:37;a:4:{s:1:"a";i:38;s:1:"b";s:13:"cobrar cuotas";s:1:"c";s:3:"web";s:1:"r";a:5:{i:0;i:1;i:1;i:4;i:2;i:5;i:3;i:6;i:4;i:7;}}i:38;a:4:{s:1:"a";i:39;s:1:"b";s:18:"convertir reservas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:39;a:4:{s:1:"a";i:40;s:1:"b";s:19:"ver reservas equipo";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:3;i:2;i:4;i:3;i:6;}}i:40;a:4:{s:1:"a";i:41;s:1:"b";s:11:"anular caja";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:41;a:4:{s:1:"a";i:42;s:1:"b";s:12:"ver reportes";s:1:"c";s:3:"web";s:1:"r";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:42;a:4:{s:1:"a";i:43;s:1:"b";s:17:"exportar reportes";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}}i:43;a:4:{s:1:"a";i:44;s:1:"b";s:20:"administrar usuarios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:44;a:4:{s:1:"a";i:45;s:1:"b";s:14:"crear asesores";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:6;}}i:45;a:4:{s:1:"a";i:46;s:1:"b";s:15:"editar asesores";s:1:"c";s:3:"web";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:6;}}i:46;a:4:{s:1:"a";i:47;s:1:"b";s:19:"desactivar asesores";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:6;}}i:47;a:4:{s:1:"a";i:48;s:1:"b";s:33:"asignar urbanizaciones a asesores";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:6;}}i:48;a:4:{s:1:"a";i:49;s:1:"b";s:27:"resetear contraseña asesor";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:4;i:2;i:6;}}i:49;a:4:{s:1:"a";i:50;s:1:"b";s:18:"ver recibo reserva";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:4;i:1;i:6;i:2;i:7;}}i:50;a:4:{s:1:"a";i:51;s:1:"b";s:24:"descargar recibo reserva";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:4;i:1;i:6;i:2;i:7;}}i:51;a:4:{s:1:"a";i:52;s:1:"b";s:23:"imprimir recibo reserva";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:4;i:1;i:6;i:2;i:7;}}i:52;a:4:{s:1:"a";i:53;s:1:"b";s:22:"editar ventas anuladas";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:4;}}}s:5:"roles";a:7:{i:0;a:3:{s:1:"a";i:1;s:1:"b";s:19:"super administrador";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:4;s:1:"b";s:13:"administrador";s:1:"c";s:3:"web";}i:2;a:3:{s:1:"a";i:5;s:1:"b";s:7:"gerente";s:1:"c";s:3:"web";}i:3;a:3:{s:1:"a";i:6;s:1:"b";s:10:"supervisor";s:1:"c";s:3:"web";}i:4;a:3:{s:1:"a";i:2;s:1:"b";s:20:"supervisor comercial";s:1:"c";s:3:"web";}i:5;a:3:{s:1:"a";i:3;s:1:"b";s:17:"supervisor ventas";s:1:"c";s:3:"web";}i:6;a:3:{s:1:"a";i:7;s:1:"b";s:8:"vendedor";s:1:"c";s:3:"web";}}}', 1780900423),
	('impacto-urbanizaciones-cache-system_settings.all', 'a:21:{s:11:"system_name";s:13:"INMOLIDER CRM";s:15:"system_subtitle";s:28:"Sistema Integral de Terrenos";s:15:"public_base_url";s:0:"";s:9:"logo_main";s:0:"";s:10:"logo_login";s:50:"logos/9R3aF271r2vqL7a3Zcd4TxZN8vIfu5Sa72FrJYTv.png";s:16:"login_background";s:62:"login-backgrounds/zbcTuVc5WSk10WFu1kplAkeagPt2kwjQMvMZTrZc.png";s:8:"logo_pdf";s:50:"logos/66lwm91cOwagUoXpYsb1MN6VCSwM4icz7tBVqZuV.png";s:12:"company_name";s:12:"INMOLIDER LV";s:12:"razon_social";s:12:"INMOLIDER LV";s:3:"nit";s:0:"";s:9:"direccion";s:31:"Av 20 de octubre 2608 Sopocachi";s:6:"ciudad";s:6:"La Paz";s:12:"departamento";s:6:"La Paz";s:8:"telefono";s:0:"";s:7:"celular";s:8:"75865765";s:8:"whatsapp";s:0:"";s:5:"email";s:17:"xwebia6@gmail.com";s:7:"website";s:0:"";s:11:"footer_text";s:32:"desarrollado por Xweb Ingenieria";s:13:"primary_color";s:7:"#40726e";s:15:"secondary_color";s:7:"#182f0f";}', 1780845636);

-- Volcando estructura para tabla impacto_urbanizaciones.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.cache_locks: ~0 rows (aproximadamente)
DELETE FROM `cache_locks`;

-- Volcando estructura para tabla impacto_urbanizaciones.cash_movements
CREATE TABLE IF NOT EXISTS `cash_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `sale_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `installment_id` bigint unsigned DEFAULT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `concepto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metodo_pago` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `monto` decimal(12,2) NOT NULL,
  `fecha` date NOT NULL,
  `referencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_movements_user_id_foreign` (`user_id`),
  KEY `cash_movements_fecha_estado_index` (`fecha`,`estado`),
  KEY `cash_movements_cliente_id_estado_index` (`cliente_id`,`estado`),
  KEY `cash_movements_sale_id_concepto_index` (`sale_id`,`concepto`),
  KEY `cash_movements_reservation_id_concepto_index` (`reservation_id`,`concepto`),
  KEY `cash_movements_installment_id_concepto_index` (`installment_id`,`concepto`),
  CONSTRAINT `cash_movements_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_movements_installment_id_foreign` FOREIGN KEY (`installment_id`) REFERENCES `cuotas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_movements_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_movements_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `ventas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.cash_movements: ~11 rows (aproximadamente)
DELETE FROM `cash_movements`;
INSERT INTO `cash_movements` (`id`, `user_id`, `cliente_id`, `sale_id`, `reservation_id`, `installment_id`, `tipo`, `concepto`, `metodo_pago`, `monto`, `fecha`, `referencia`, `observaciones`, `estado`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, NULL, NULL, 'ingreso', 'anticipo', 'transferencia', 5000.00, '2026-06-02', 'TRF-00045', NULL, 'confirmado', '2026-06-05 06:54:31', '2026-06-05 10:16:04'),
	(2, 1, 2, 2, NULL, NULL, 'ingreso', 'anticipo', 'QR', 3000.00, '2026-04-05', 'QR-ANT-102', NULL, 'confirmado', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(3, 1, 2, 2, NULL, 1, 'ingreso', 'cuota', 'banco', 2583.33, '2026-05-25', 'REC-CUOTA-1', NULL, 'confirmado', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(4, 1, 2, 2, NULL, 2, 'ingreso', 'cuota', 'efectivo', 1291.67, '2026-05-24', 'REC-CUOTA-2', NULL, 'confirmado', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(5, 1, 3, NULL, 1, NULL, 'ingreso', 'reserva', 'efectivo', 800.00, '2026-06-04', 'RES-ACT-001', NULL, 'confirmado', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(6, 1, 4, NULL, 2, NULL, 'ingreso', 'reserva', 'otro', 500.00, '2026-05-24', 'RES-VEN-001', NULL, 'confirmado', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(7, 1, 5, NULL, 3, NULL, 'ingreso', 'reserva', 'QR', 1000.00, '2026-05-18', 'RES-CONV-001', NULL, 'confirmado', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(8, 1, 5, 3, NULL, NULL, 'ingreso', 'anticipo', 'transferencia', 4500.00, '2026-05-26', 'ANT-CONV-001', NULL, 'confirmado', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(9, 1, 6, 4, NULL, NULL, 'ingreso', 'anticipo', 'banco', 2500.00, '2026-06-03', 'ANT-MES-001', NULL, 'confirmado', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(10, 1, 3, NULL, 4, NULL, 'ingreso', 'reserva', 'QR', 100.00, '2026-06-05', NULL, NULL, 'confirmado', '2026-06-05 09:00:29', '2026-06-05 09:00:29'),
	(11, 1, 1, 5, NULL, NULL, 'ingreso', 'anticipo', 'efectivo', 5000.00, '2026-06-05', NULL, NULL, 'confirmado', '2026-06-05 09:49:20', '2026-06-05 09:49:20');

-- Volcando estructura para tabla impacto_urbanizaciones.clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `urbanizacion_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_documento_urbanizacion_unique` (`documento`,`urbanizacion_id`),
  KEY `clientes_documento_index` (`documento`),
  KEY `clientes_urbanizacion_nombre_index` (`urbanizacion_id`,`nombre`),
  KEY `clientes_created_by_index` (`created_by`),
  CONSTRAINT `clientes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clientes_urbanizacion_id_foreign` FOREIGN KEY (`urbanizacion_id`) REFERENCES `urbanizaciones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.clientes: ~7 rows (aproximadamente)
DELETE FROM `clientes`;
INSERT INTO `clientes` (`id`, `urbanizacion_id`, `created_by`, `nombre`, `documento`, `telefono`, `email`, `direccion`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 'Mariela Fernandez Rojas', 'CI-4829137 SC', '77012345', 'mariela.fernandez@example.com', 'Av. Banzer 5to anillo, Santa Cruz', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(2, 1, 1, 'Carlos Alberto Rojas Perez', 'CI-6392841 SC', '72100455', 'carlos.rojas@example.com', 'Barrio Las Palmas, calle 4 #28', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(3, 1, 1, 'Ana Lucia Vargas Medina', 'CI-7519283 CB', '69033412', 'ana.vargas@example.com', 'Condominio Sevilla Norte, bloque B', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(4, 1, 1, 'Luis Fernando Mercado Salvatierra', 'CI-5849302 SC', '73188220', 'luis.mercado@example.com', 'Av. Virgen de Cotoca km 7', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(5, 1, 1, 'Patricia Suarez Aguilera', 'CI-8263145 SC', '75611908', 'patricia.suarez@example.com', 'Zona Equipetrol, calle Los Tajibos #17', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(6, 1, 1, 'Jorge Andres Quiroga Mendez', 'CI-4927610 LP', '70844591', 'jorge.quiroga@example.com', 'Av. Mutualista, condominio El Portal', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(7, 1, 1, 'Natalia Rivero Chavez', 'CI-9182736 SC', '67753122', 'natalia.rivero@example.com', 'Plan 3000, barrio Primavera', '2026-06-05 06:54:31', '2026-06-05 06:54:31');

-- Volcando estructura para tabla impacto_urbanizaciones.commercial_settings
CREATE TABLE IF NOT EXISTS `commercial_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `commercial_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.commercial_settings: ~1 rows (aproximadamente)
DELETE FROM `commercial_settings`;
INSERT INTO `commercial_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
	(1, 'reserva_dias_habiles_asesor', '5', '2026-06-05 06:54:28', '2026-06-05 06:54:28');

-- Volcando estructura para tabla impacto_urbanizaciones.cuotas
CREATE TABLE IF NOT EXISTS `cuotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venta_id` bigint unsigned NOT NULL,
  `numero` int unsigned NOT NULL,
  `fecha_programada` date DEFAULT NULL,
  `fecha_vencimiento` date NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo_pendiente` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuotas_venta_id_numero_unique` (`venta_id`,`numero`),
  KEY `cuotas_estado_fecha_programada_index` (`estado`,`fecha_programada`),
  KEY `cuotas_venta_id_estado_index` (`venta_id`,`estado`),
  CONSTRAINT `cuotas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.cuotas: ~60 rows (aproximadamente)
DELETE FROM `cuotas`;
INSERT INTO `cuotas` (`id`, `venta_id`, `numero`, `fecha_programada`, `fecha_vencimiento`, `monto`, `fecha_pago`, `monto_pagado`, `saldo_pendiente`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 2, 1, '2026-05-05', '2026-05-05', 2583.33, '2026-05-03', 2583.33, 0.00, 'pagada', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(2, 2, 2, '2026-06-05', '2026-06-05', 2583.33, NULL, 1291.67, 1291.66, 'vencida', NULL, '2026-06-05 06:54:31', '2026-06-07 05:04:49'),
	(3, 2, 3, '2026-07-05', '2026-07-05', 2583.33, NULL, 0.00, 2583.33, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(4, 2, 4, '2026-08-05', '2026-08-05', 2583.33, NULL, 0.00, 2583.33, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(5, 2, 5, '2026-09-05', '2026-09-05', 2583.33, NULL, 0.00, 2583.33, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(6, 2, 6, '2026-10-05', '2026-10-05', 2583.33, NULL, 0.00, 2583.33, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(7, 3, 1, '2026-07-05', '2026-07-05', 3875.00, NULL, 0.00, 3875.00, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(8, 3, 2, '2026-08-05', '2026-08-05', 3875.00, NULL, 0.00, 3875.00, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(9, 3, 3, '2026-09-05', '2026-09-05', 3875.00, NULL, 0.00, 3875.00, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(10, 3, 4, '2026-10-05', '2026-10-05', 3875.00, NULL, 0.00, 3875.00, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(11, 4, 1, '2026-07-05', '2026-07-05', 3650.00, NULL, 0.00, 3650.00, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(12, 4, 2, '2026-08-05', '2026-08-05', 3650.00, NULL, 0.00, 3650.00, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(13, 4, 3, '2026-09-05', '2026-09-05', 3650.00, NULL, 0.00, 3650.00, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(14, 4, 4, '2026-10-05', '2026-10-05', 3650.00, NULL, 0.00, 3650.00, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(15, 4, 5, '2026-11-05', '2026-11-05', 3650.00, NULL, 0.00, 3650.00, 'pendiente', NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(16, 5, 1, '2026-07-05', '2026-07-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(17, 5, 2, '2026-08-05', '2026-08-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(18, 5, 3, '2026-09-05', '2026-09-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(19, 5, 4, '2026-10-05', '2026-10-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(20, 5, 5, '2026-11-05', '2026-11-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(21, 5, 6, '2026-12-05', '2026-12-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(22, 5, 7, '2027-01-05', '2027-01-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(23, 5, 8, '2027-02-05', '2027-02-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(24, 5, 9, '2027-03-05', '2027-03-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(25, 5, 10, '2027-04-05', '2027-04-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(26, 5, 11, '2027-05-05', '2027-05-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(27, 5, 12, '2027-06-05', '2027-06-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(28, 5, 13, '2027-07-05', '2027-07-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(29, 5, 14, '2027-08-05', '2027-08-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(30, 5, 15, '2027-09-05', '2027-09-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(31, 5, 16, '2027-10-05', '2027-10-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(32, 5, 17, '2027-11-05', '2027-11-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(33, 5, 18, '2027-12-05', '2027-12-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(34, 5, 19, '2028-01-05', '2028-01-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(35, 5, 20, '2028-02-05', '2028-02-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(36, 5, 21, '2028-03-05', '2028-03-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(37, 5, 22, '2028-04-05', '2028-04-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(38, 5, 23, '2028-05-05', '2028-05-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(39, 5, 24, '2028-06-05', '2028-06-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(40, 5, 25, '2028-07-05', '2028-07-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(41, 5, 26, '2028-08-05', '2028-08-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(42, 5, 27, '2028-09-05', '2028-09-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(43, 5, 28, '2028-10-05', '2028-10-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(44, 5, 29, '2028-11-05', '2028-11-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(45, 5, 30, '2028-12-05', '2028-12-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(46, 5, 31, '2029-01-05', '2029-01-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(47, 5, 32, '2029-02-05', '2029-02-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(48, 5, 33, '2029-03-05', '2029-03-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(49, 5, 34, '2029-04-05', '2029-04-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(50, 5, 35, '2029-05-05', '2029-05-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(51, 5, 36, '2029-06-05', '2029-06-05', 527.78, NULL, 0.00, 527.78, 'pendiente', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(52, 1, 1, '2026-07-02', '2026-07-02', 1416.67, NULL, 0.00, 1416.67, 'pendiente', NULL, '2026-06-05 10:16:04', '2026-06-05 10:16:04'),
	(53, 1, 2, '2026-08-02', '2026-08-02', 1416.67, NULL, 0.00, 1416.67, 'pendiente', NULL, '2026-06-05 10:16:04', '2026-06-05 10:16:04'),
	(54, 1, 3, '2026-09-02', '2026-09-02', 1416.67, NULL, 0.00, 1416.67, 'pendiente', NULL, '2026-06-05 10:16:04', '2026-06-05 10:16:04'),
	(55, 1, 4, '2026-10-02', '2026-10-02', 1416.67, NULL, 0.00, 1416.67, 'pendiente', NULL, '2026-06-05 10:16:04', '2026-06-05 10:16:04'),
	(56, 1, 5, '2026-11-02', '2026-11-02', 1416.67, NULL, 0.00, 1416.67, 'pendiente', NULL, '2026-06-05 10:16:04', '2026-06-05 10:16:04'),
	(57, 1, 6, '2026-12-02', '2026-12-02', 1416.67, NULL, 0.00, 1416.67, 'pendiente', NULL, '2026-06-05 10:16:04', '2026-06-05 10:16:04'),
	(58, 1, 7, '2027-01-02', '2027-01-02', 1416.67, NULL, 0.00, 1416.67, 'pendiente', NULL, '2026-06-05 10:16:04', '2026-06-05 10:16:04'),
	(59, 1, 8, '2027-02-02', '2027-02-02', 1416.67, NULL, 0.00, 1416.67, 'pendiente', NULL, '2026-06-05 10:16:04', '2026-06-05 10:16:04'),
	(60, 1, 9, '2027-03-02', '2027-03-02', 1416.64, NULL, 0.00, 1416.64, 'pendiente', NULL, '2026-06-05 10:16:04', '2026-06-05 10:16:04');

-- Volcando estructura para tabla impacto_urbanizaciones.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.failed_jobs: ~0 rows (aproximadamente)
DELETE FROM `failed_jobs`;

-- Volcando estructura para tabla impacto_urbanizaciones.grupos_comerciales
CREATE TABLE IF NOT EXISTS `grupos_comerciales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `supervisor_id` bigint unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grupos_comerciales_supervisor_id_activo_index` (`supervisor_id`,`activo`),
  CONSTRAINT `grupos_comerciales_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.grupos_comerciales: ~3 rows (aproximadamente)
DELETE FROM `grupos_comerciales`;
INSERT INTO `grupos_comerciales` (`id`, `nombre`, `descripcion`, `observaciones`, `supervisor_id`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Grupo Norte', 'Equipo comercial zona norte.', NULL, 3, 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(2, 'Grupo Sur', 'Equipo comercial zona sur.', NULL, 3, 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(3, 'Grupo Centro', 'Equipo comercial zona centro.', NULL, 3, 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31');

-- Volcando estructura para tabla impacto_urbanizaciones.grupo_comercial_urbanizacion
CREATE TABLE IF NOT EXISTS `grupo_comercial_urbanizacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grupo_comercial_id` bigint unsigned NOT NULL,
  `urbanizacion_id` bigint unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grupo_urbanizacion_unique` (`grupo_comercial_id`,`urbanizacion_id`),
  KEY `grupo_comercial_urbanizacion_urbanizacion_id_activo_index` (`urbanizacion_id`,`activo`),
  CONSTRAINT `grupo_comercial_urbanizacion_grupo_comercial_id_foreign` FOREIGN KEY (`grupo_comercial_id`) REFERENCES `grupos_comerciales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grupo_comercial_urbanizacion_urbanizacion_id_foreign` FOREIGN KEY (`urbanizacion_id`) REFERENCES `urbanizaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.grupo_comercial_urbanizacion: ~1 rows (aproximadamente)
DELETE FROM `grupo_comercial_urbanizacion`;
INSERT INTO `grupo_comercial_urbanizacion` (`id`, `grupo_comercial_id`, `urbanizacion_id`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31');

-- Volcando estructura para tabla impacto_urbanizaciones.grupo_comercial_user
CREATE TABLE IF NOT EXISTS `grupo_comercial_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grupo_comercial_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vendedor',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grupo_user_unique` (`grupo_comercial_id`,`user_id`),
  KEY `grupo_comercial_user_user_id_tipo_activo_index` (`user_id`,`tipo`,`activo`),
  CONSTRAINT `grupo_comercial_user_grupo_comercial_id_foreign` FOREIGN KEY (`grupo_comercial_id`) REFERENCES `grupos_comerciales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grupo_comercial_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.grupo_comercial_user: ~2 rows (aproximadamente)
DELETE FROM `grupo_comercial_user`;
INSERT INTO `grupo_comercial_user` (`id`, `grupo_comercial_id`, `user_id`, `tipo`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 1, 3, 'supervisor_comercial', 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(2, 1, 4, 'vendedor', 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31');

-- Volcando estructura para tabla impacto_urbanizaciones.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.jobs: ~0 rows (aproximadamente)
DELETE FROM `jobs`;

-- Volcando estructura para tabla impacto_urbanizaciones.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.job_batches: ~0 rows (aproximadamente)
DELETE FROM `job_batches`;

-- Volcando estructura para tabla impacto_urbanizaciones.lotes
CREATE TABLE IF NOT EXISTS `lotes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `manzano_id` bigint unsigned NOT NULL,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `superficie` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disponible',
  `fila` int unsigned NOT NULL DEFAULT '1',
  `columna` int unsigned NOT NULL DEFAULT '1',
  `coord_x` decimal(6,2) DEFAULT NULL,
  `coord_y` decimal(6,2) DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lotes_manzano_id_codigo_unique` (`manzano_id`,`codigo`),
  KEY `lotes_estado_manzano_id_index` (`estado`,`manzano_id`),
  KEY `lotes_coord_x_coord_y_index` (`coord_x`,`coord_y`),
  CONSTRAINT `lotes_manzano_id_foreign` FOREIGN KEY (`manzano_id`) REFERENCES `manzanos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.lotes: ~120 rows (aproximadamente)
DELETE FROM `lotes`;
INSERT INTO `lotes` (`id`, `manzano_id`, `codigo`, `superficie`, `precio`, `estado`, `fila`, `columna`, `coord_x`, `coord_y`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 1, '01', 288.00, 17750.00, 'vendido', 1, 1, 28.04, 16.47, NULL, '2026-06-05 06:54:31', '2026-06-05 07:28:58'),
	(2, 1, '02', 296.00, 18500.00, 'vendido', 1, 2, 31.74, 19.30, NULL, '2026-06-05 06:54:31', '2026-06-05 07:29:03'),
	(3, 1, '03', 304.00, 19250.00, 'reservado', 1, 3, 34.08, 20.59, NULL, '2026-06-05 06:54:31', '2026-06-05 07:29:09'),
	(4, 1, '04', 312.00, 20000.00, 'vendido', 1, 4, 78.23, 51.01, NULL, '2026-06-05 06:54:31', '2026-06-05 07:30:36'),
	(5, 1, '05', 320.00, 20750.00, 'vendido', 1, 5, 40.92, 25.41, NULL, '2026-06-05 06:54:31', '2026-06-05 08:30:06'),
	(6, 1, '06', 328.00, 21500.00, 'disponible', 2, 1, 38.42, 24.01, NULL, '2026-06-05 06:54:31', '2026-06-05 08:29:56'),
	(7, 1, '07', 336.00, 22250.00, 'disponible', 2, 2, 37.20, 22.75, NULL, '2026-06-05 06:54:31', '2026-06-05 08:29:48'),
	(8, 1, '08', 344.00, 23000.00, 'vendido', 2, 3, 35.72, 21.79, NULL, '2026-06-05 06:54:31', '2026-06-05 09:49:20'),
	(9, 1, '09', 352.00, 23750.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(10, 1, '10', 360.00, 24500.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(11, 1, '11', 368.00, 25250.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(12, 1, '12', 376.00, 26000.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(13, 1, '13', 384.00, 26750.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(14, 1, '14', 392.00, 27500.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(15, 1, '15', 400.00, 28250.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(16, 2, '01', 288.00, 17750.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(17, 2, '02', 296.00, 18500.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(18, 2, '03', 304.00, 19250.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(19, 2, '04', 312.00, 20000.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(20, 2, '05', 320.00, 20750.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(21, 2, '06', 328.00, 21500.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(22, 2, '07', 336.00, 22250.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(23, 2, '08', 344.00, 23000.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(24, 2, '09', 352.00, 23750.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(25, 2, '10', 360.00, 24500.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(26, 2, '11', 368.00, 25250.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(27, 2, '12', 376.00, 26000.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(28, 2, '13', 384.00, 26750.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(29, 2, '14', 392.00, 27500.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(30, 2, '15', 400.00, 28250.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(31, 3, '01', 288.00, 17750.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(32, 3, '02', 296.00, 18500.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(33, 3, '03', 304.00, 19250.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(34, 3, '04', 312.00, 20000.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(35, 3, '05', 320.00, 20750.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(36, 3, '06', 328.00, 21500.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(37, 3, '07', 336.00, 22250.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(38, 3, '08', 344.00, 23000.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(39, 3, '09', 352.00, 23750.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(40, 3, '10', 360.00, 24500.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(41, 3, '11', 368.00, 25250.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(42, 3, '12', 376.00, 26000.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(43, 3, '13', 384.00, 26750.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(44, 3, '14', 392.00, 27500.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(45, 3, '15', 400.00, 28250.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(46, 4, '01', 288.00, 17750.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(47, 4, '02', 296.00, 18500.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(48, 4, '03', 304.00, 19250.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(49, 4, '04', 312.00, 20000.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(50, 4, '05', 320.00, 20750.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(51, 4, '06', 328.00, 21500.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(52, 4, '07', 336.00, 22250.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(53, 4, '08', 344.00, 23000.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(54, 4, '09', 352.00, 23750.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(55, 4, '10', 360.00, 24500.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(56, 4, '11', 368.00, 25250.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(57, 4, '12', 376.00, 26000.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(58, 4, '13', 384.00, 26750.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(59, 4, '14', 392.00, 27500.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(60, 4, '15', 400.00, 28250.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(61, 5, '01', 300.00, 18950.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(62, 5, '02', 308.00, 19700.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(63, 5, '03', 316.00, 20450.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(64, 5, '04', 324.00, 21200.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(65, 5, '05', 332.00, 21950.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(66, 5, '06', 340.00, 22700.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(67, 5, '07', 348.00, 23450.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(68, 5, '08', 356.00, 24200.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(69, 5, '09', 364.00, 24950.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(70, 5, '10', 372.00, 25700.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(71, 5, '11', 380.00, 26450.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(72, 5, '12', 388.00, 27200.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(73, 5, '13', 396.00, 27950.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(74, 5, '14', 404.00, 28700.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(75, 5, '15', 412.00, 29450.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(76, 6, '01', 300.00, 18950.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(77, 6, '02', 308.00, 19700.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(78, 6, '03', 316.00, 20450.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(79, 6, '04', 324.00, 21200.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(80, 6, '05', 332.00, 21950.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(81, 6, '06', 340.00, 22700.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(82, 6, '07', 348.00, 23450.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(83, 6, '08', 356.00, 24200.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(84, 6, '09', 364.00, 24950.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(85, 6, '10', 372.00, 25700.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(86, 6, '11', 380.00, 26450.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(87, 6, '12', 388.00, 27200.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(88, 6, '13', 396.00, 27950.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(89, 6, '14', 404.00, 28700.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(90, 6, '15', 412.00, 29450.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(91, 7, '01', 300.00, 18950.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(92, 7, '02', 308.00, 19700.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(93, 7, '03', 316.00, 20450.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(94, 7, '04', 324.00, 21200.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(95, 7, '05', 332.00, 21950.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(96, 7, '06', 340.00, 22700.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(97, 7, '07', 348.00, 23450.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(98, 7, '08', 356.00, 24200.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(99, 7, '09', 364.00, 24950.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(100, 7, '10', 372.00, 25700.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(101, 7, '11', 380.00, 26450.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(102, 7, '12', 388.00, 27200.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(103, 7, '13', 396.00, 27950.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(104, 7, '14', 404.00, 28700.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(105, 7, '15', 412.00, 29450.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(106, 8, '01', 300.00, 18950.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(107, 8, '02', 308.00, 19700.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(108, 8, '03', 316.00, 20450.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(109, 8, '04', 324.00, 21200.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(110, 8, '05', 332.00, 21950.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(111, 8, '06', 340.00, 22700.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(112, 8, '07', 348.00, 23450.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(113, 8, '08', 356.00, 24200.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(114, 8, '09', 364.00, 24950.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(115, 8, '10', 372.00, 25700.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(116, 8, '11', 380.00, 26450.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(117, 8, '12', 388.00, 27200.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(118, 8, '13', 396.00, 27950.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(119, 8, '14', 404.00, 28700.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(120, 8, '15', 412.00, 29450.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-06-05 06:54:31', '2026-06-05 06:54:31');

-- Volcando estructura para tabla impacto_urbanizaciones.lot_histories
CREATE TABLE IF NOT EXISTS `lot_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lote_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `accion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `estado_anterior` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_nuevo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lot_histories_lote_id_created_at_index` (`lote_id`,`created_at`),
  KEY `lot_histories_user_id_accion_index` (`user_id`,`accion`),
  CONSTRAINT `lot_histories_lote_id_foreign` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lot_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.lot_histories: ~10 rows (aproximadamente)
DELETE FROM `lot_histories`;
INSERT INTO `lot_histories` (`id`, `lote_id`, `user_id`, `accion`, `descripcion`, `estado_anterior`, `estado_nuevo`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 'lote_vendido', 'Venta al contado.', 'disponible', 'vendido', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(2, 2, 1, 'lote_vendido', 'Venta a credito.', 'disponible', 'vendido', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(3, 3, 1, 'reserva_creada', 'Reserva activa.', 'disponible', 'reservado', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(4, 4, 1, 'reserva_vencida', 'Reserva vencida y lote liberado.', 'reservado', 'disponible', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(5, 4, 1, 'reserva_convertida', 'La reserva fue convertida en venta.', 'reservado', 'vendido', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(6, 5, 1, 'lote_vendido', 'Venta a credito reciente.', 'disponible', 'vendido', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(7, 8, 1, 'reserva_creada', 'Reserva creada para cliente #3', 'disponible', 'reservado', '2026-06-05 09:00:29', '2026-06-05 09:00:29'),
	(8, 8, 1, 'reserva_actualizada', 'Reserva actualizada.', 'reservado', 'disponible', '2026-06-05 09:42:20', '2026-06-05 09:42:20'),
	(9, 8, 1, 'lote_vendido', 'Lote vendido al cliente #1', 'disponible', 'vendido', '2026-06-05 09:49:20', '2026-06-05 09:49:20'),
	(10, 1, 1, 'venta_actualizada', 'Lote asociado a venta actualizada.', 'vendido', 'vendido', '2026-06-05 10:16:04', '2026-06-05 10:16:04');

-- Volcando estructura para tabla impacto_urbanizaciones.manzanos
CREATE TABLE IF NOT EXISTS `manzanos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `urbanizacion_id` bigint unsigned NOT NULL,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `manzanos_urbanizacion_id_codigo_unique` (`urbanizacion_id`,`codigo`),
  CONSTRAINT `manzanos_urbanizacion_id_foreign` FOREIGN KEY (`urbanizacion_id`) REFERENCES `urbanizaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.manzanos: ~8 rows (aproximadamente)
DELETE FROM `manzanos`;
INSERT INTO `manzanos` (`id`, `urbanizacion_id`, `codigo`, `nombre`, `orden`, `created_at`, `updated_at`) VALUES
	(1, 1, 'A', 'Manzano A', 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(2, 1, 'B', 'Manzano B', 2, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(3, 1, 'C', 'Manzano C', 3, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(4, 1, 'D', 'Manzano D', 4, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(5, 2, 'A', 'Manzano A', 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(6, 2, 'B', 'Manzano B', 2, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(7, 2, 'C', 'Manzano C', 3, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(8, 2, 'D', 'Manzano D', 4, '2026-06-05 06:54:31', '2026-06-05 06:54:31');

-- Volcando estructura para tabla impacto_urbanizaciones.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.migrations: ~38 rows (aproximadamente)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_05_26_025641_create_urbanizaciones_table', 1),
	(5, '2026_05_26_025642_create_manzanos_table', 1),
	(6, '2026_05_26_025643_create_clientes_table', 1),
	(7, '2026_05_26_025643_create_lotes_table', 1),
	(8, '2026_05_26_025644_create_ventas_table', 1),
	(9, '2026_05_26_025645_create_cuotas_table', 1),
	(10, '2026_05_26_031310_create_permission_tables', 1),
	(11, '2026_05_26_031319_create_reservas_table', 1),
	(12, '2026_05_26_031320_create_cash_movements_table', 1),
	(13, '2026_05_26_031320_create_lot_histories_table', 1),
	(14, '2026_05_26_031328_add_professional_fields_to_lotes_ventas_cuotas_tables', 1),
	(15, '2026_05_26_034931_add_audit_indexes_and_client_link', 1),
	(16, '2026_05_26_035924_create_audit_logs_table', 1),
	(17, '2026_05_26_043725_add_plano_imagen_to_urbanizaciones_table', 1),
	(18, '2026_05_26_050439_make_lote_coordinates_nullable', 1),
	(19, '2026_05_26_125354_add_urbanizacion_context_fields', 1),
	(20, '2026_05_27_174629_create_asesors_table', 1),
	(21, '2026_05_27_174633_add_must_change_password_to_users_table', 1),
	(22, '2026_05_29_000001_add_urbanizacion_and_created_by_to_clientes_table', 1),
	(23, '2026_05_29_000002_add_tipo_operacion_to_reservas_table', 1),
	(24, '2026_05_29_000003_create_commercial_settings_table', 1),
	(25, '2026_05_29_000004_add_commercial_report_permissions', 1),
	(26, '2026_05_30_000001_create_system_settings_table', 1),
	(27, '2026_05_30_000002_create_supervisor_profiles_table', 1),
	(28, '2026_05_30_000003_create_grupos_comerciales_table', 1),
	(29, '2026_05_30_000004_add_grupo_comercial_id_and_direccion_to_asesores_table', 1),
	(30, '2026_05_30_000005_add_structure_permissions', 1),
	(31, '2026_06_04_000001_add_commercial_hierarchy', 1),
	(32, '2026_06_04_000002_add_commercial_hierarchy_permissions', 1),
	(33, '2026_06_05_000001_add_propietario_to_urbanizaciones_table', 2),
	(34, '2026_06_05_000002_add_login_background_system_setting', 3),
	(35, '2026_06_05_000003_add_reservation_receipt_permissions', 4),
	(36, '2026_06_05_000004_add_edit_annulled_sales_permission', 5),
	(37, '2026_06_05_000005_add_saldo_financiar_to_ventas_table', 6),
	(38, '2026_06_06_230000_add_public_url_and_urbanizacion_slug', 7);

-- Volcando estructura para tabla impacto_urbanizaciones.model_has_permissions
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.model_has_permissions: ~0 rows (aproximadamente)
DELETE FROM `model_has_permissions`;

-- Volcando estructura para tabla impacto_urbanizaciones.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.model_has_roles: ~7 rows (aproximadamente)
DELETE FROM `model_has_roles`;
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
	(1, 'App\\Models\\User', 1),
	(4, 'App\\Models\\User', 1),
	(5, 'App\\Models\\User', 2),
	(2, 'App\\Models\\User', 3),
	(6, 'App\\Models\\User', 3),
	(7, 'App\\Models\\User', 4),
	(8, 'App\\Models\\User', 5);

-- Volcando estructura para tabla impacto_urbanizaciones.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.password_reset_tokens: ~0 rows (aproximadamente)
DELETE FROM `password_reset_tokens`;

-- Volcando estructura para tabla impacto_urbanizaciones.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.permissions: ~53 rows (aproximadamente)
DELETE FROM `permissions`;
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'ver reporte reservas', 'web', '2026-06-05 06:54:28', '2026-06-05 06:54:28'),
	(2, 'exportar reporte reservas', 'web', '2026-06-05 06:54:28', '2026-06-05 06:54:28'),
	(3, 'ver reporte mejor vendedor', 'web', '2026-06-05 06:54:28', '2026-06-05 06:54:28'),
	(4, 'exportar reporte mejor vendedor', 'web', '2026-06-05 06:54:28', '2026-06-05 06:54:28'),
	(5, 'crear supervisores', 'web', '2026-06-05 06:54:28', '2026-06-05 06:54:28'),
	(6, 'editar supervisores', 'web', '2026-06-05 06:54:28', '2026-06-05 06:54:28'),
	(7, 'desactivar supervisores', 'web', '2026-06-05 06:54:28', '2026-06-05 06:54:28'),
	(8, 'crear grupos comerciales', 'web', '2026-06-05 06:54:28', '2026-06-05 06:54:28'),
	(9, 'editar grupos comerciales', 'web', '2026-06-05 06:54:29', '2026-06-05 06:54:29'),
	(10, 'asignar urbanizaciones a grupos', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(11, 'ver reporte comercial', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(12, 'exportar reporte comercial', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(13, 'gestionar supervisores comerciales', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(14, 'gestionar supervisores de ventas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(15, 'ver dashboard', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(16, 'ver lotes', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(17, 'ver clientes', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(18, 'ver ventas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(19, 'ver reservas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(20, 'crear urbanizaciones', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(21, 'editar urbanizaciones', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(22, 'eliminar urbanizaciones', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(23, 'crear manzanos', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(24, 'editar manzanos', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(25, 'eliminar manzanos', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(26, 'crear lotes', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(27, 'editar lotes', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(28, 'eliminar lotes', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(29, 'crear clientes', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(30, 'editar clientes', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(31, 'eliminar clientes', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(32, 'crear ventas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(33, 'editar ventas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(34, 'anular ventas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(35, 'crear reservas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(36, 'editar reservas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(37, 'cancelar reservas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(38, 'cobrar cuotas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(39, 'convertir reservas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(40, 'ver reservas equipo', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(41, 'anular caja', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(42, 'ver reportes', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(43, 'exportar reportes', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(44, 'administrar usuarios', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(45, 'crear asesores', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(46, 'editar asesores', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(47, 'desactivar asesores', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(48, 'asignar urbanizaciones a asesores', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(49, 'resetear contraseña asesor', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(50, 'ver recibo reserva', 'web', '2026-06-05 09:22:39', '2026-06-05 09:22:39'),
	(51, 'descargar recibo reserva', 'web', '2026-06-05 09:22:39', '2026-06-05 09:22:39'),
	(52, 'imprimir recibo reserva', 'web', '2026-06-05 09:22:39', '2026-06-05 09:22:39'),
	(53, 'editar ventas anuladas', 'web', '2026-06-05 09:58:14', '2026-06-05 09:58:14');

-- Volcando estructura para tabla impacto_urbanizaciones.reservas
CREATE TABLE IF NOT EXISTS `reservas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `urbanizacion_id` bigint unsigned DEFAULT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `lote_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `vendedor_id` bigint unsigned DEFAULT NULL,
  `supervisor_ventas_id` bigint unsigned DEFAULT NULL,
  `supervisor_comercial_id` bigint unsigned DEFAULT NULL,
  `grupo_comercial_id` bigint unsigned DEFAULT NULL,
  `fecha_reserva` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `monto_reserva` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `tipo_operacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contado',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservas_estado_fecha_vencimiento_index` (`estado`,`fecha_vencimiento`),
  KEY `reservas_cliente_id_estado_index` (`cliente_id`,`estado`),
  KEY `reservas_lote_id_estado_index` (`lote_id`,`estado`),
  KEY `reservas_tipo_estado_index` (`tipo_operacion`,`estado`),
  KEY `reservas_usuario_fecha_index` (`usuario_id`,`fecha_reserva`),
  KEY `reservas_supervisor_ventas_id_foreign` (`supervisor_ventas_id`),
  KEY `reservas_supervisor_comercial_id_foreign` (`supervisor_comercial_id`),
  KEY `reservas_grupo_comercial_id_foreign` (`grupo_comercial_id`),
  KEY `reservas_commercial_index` (`urbanizacion_id`,`grupo_comercial_id`,`fecha_reserva`),
  KEY `reservas_team_index` (`vendedor_id`,`supervisor_ventas_id`),
  CONSTRAINT `reservas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reservas_grupo_comercial_id_foreign` FOREIGN KEY (`grupo_comercial_id`) REFERENCES `grupos_comerciales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservas_lote_id_foreign` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reservas_supervisor_comercial_id_foreign` FOREIGN KEY (`supervisor_comercial_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservas_supervisor_ventas_id_foreign` FOREIGN KEY (`supervisor_ventas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservas_urbanizacion_id_foreign` FOREIGN KEY (`urbanizacion_id`) REFERENCES `urbanizaciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservas_vendedor_id_foreign` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.reservas: ~4 rows (aproximadamente)
DELETE FROM `reservas`;
INSERT INTO `reservas` (`id`, `urbanizacion_id`, `cliente_id`, `lote_id`, `usuario_id`, `vendedor_id`, `supervisor_ventas_id`, `supervisor_comercial_id`, `grupo_comercial_id`, `fecha_reserva`, `fecha_vencimiento`, `monto_reserva`, `estado`, `tipo_operacion`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 1, 3, 3, 1, NULL, NULL, NULL, NULL, '2026-06-04', '2026-06-11', 800.00, 'activa', 'credito', 'Reserva activa de demostracion.', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(2, 1, 4, 4, 1, NULL, NULL, NULL, NULL, '2026-05-24', '2026-05-31', 500.00, 'vencida', 'contado', 'Reserva vencida de demostracion; lote liberado.', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(3, 1, 5, 4, 1, NULL, NULL, NULL, NULL, '2026-05-18', '2026-05-25', 1000.00, 'convertida', 'semicontado', 'Reserva convertida en venta durante la demo.', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(4, NULL, 3, 8, 1, NULL, NULL, NULL, NULL, '2026-06-05', '2026-06-12', 100.00, 'vencida', 'contado', NULL, '2026-06-05 09:00:29', '2026-06-05 09:42:20');

-- Volcando estructura para tabla impacto_urbanizaciones.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.roles: ~8 rows (aproximadamente)
DELETE FROM `roles`;
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'super administrador', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(2, 'supervisor comercial', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(3, 'supervisor ventas', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(4, 'administrador', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(5, 'gerente', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(6, 'supervisor', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(7, 'vendedor', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30'),
	(8, 'cliente', 'web', '2026-06-05 06:54:30', '2026-06-05 06:54:30');

-- Volcando estructura para tabla impacto_urbanizaciones.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.role_has_permissions: ~178 rows (aproximadamente)
DELETE FROM `role_has_permissions`;
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
	(1, 1),
	(2, 1),
	(3, 1),
	(4, 1),
	(5, 1),
	(6, 1),
	(7, 1),
	(8, 1),
	(9, 1),
	(10, 1),
	(11, 1),
	(12, 1),
	(13, 1),
	(14, 1),
	(15, 1),
	(16, 1),
	(17, 1),
	(18, 1),
	(19, 1),
	(20, 1),
	(21, 1),
	(22, 1),
	(23, 1),
	(24, 1),
	(25, 1),
	(26, 1),
	(27, 1),
	(28, 1),
	(29, 1),
	(30, 1),
	(31, 1),
	(32, 1),
	(33, 1),
	(34, 1),
	(35, 1),
	(36, 1),
	(37, 1),
	(38, 1),
	(39, 1),
	(40, 1),
	(41, 1),
	(42, 1),
	(43, 1),
	(44, 1),
	(45, 1),
	(46, 1),
	(47, 1),
	(48, 1),
	(49, 1),
	(11, 2),
	(12, 2),
	(15, 2),
	(16, 2),
	(17, 2),
	(18, 2),
	(19, 2),
	(29, 2),
	(30, 2),
	(32, 2),
	(33, 2),
	(35, 2),
	(42, 2),
	(46, 2),
	(11, 3),
	(15, 3),
	(16, 3),
	(17, 3),
	(18, 3),
	(19, 3),
	(29, 3),
	(30, 3),
	(32, 3),
	(35, 3),
	(40, 3),
	(42, 3),
	(46, 3),
	(1, 4),
	(2, 4),
	(3, 4),
	(4, 4),
	(5, 4),
	(6, 4),
	(7, 4),
	(8, 4),
	(9, 4),
	(15, 4),
	(16, 4),
	(17, 4),
	(18, 4),
	(19, 4),
	(20, 4),
	(21, 4),
	(22, 4),
	(23, 4),
	(24, 4),
	(25, 4),
	(26, 4),
	(27, 4),
	(28, 4),
	(29, 4),
	(30, 4),
	(31, 4),
	(32, 4),
	(33, 4),
	(34, 4),
	(35, 4),
	(36, 4),
	(37, 4),
	(38, 4),
	(39, 4),
	(40, 4),
	(41, 4),
	(42, 4),
	(43, 4),
	(44, 4),
	(45, 4),
	(46, 4),
	(47, 4),
	(48, 4),
	(49, 4),
	(50, 4),
	(51, 4),
	(52, 4),
	(53, 4),
	(1, 5),
	(2, 5),
	(3, 5),
	(4, 5),
	(15, 5),
	(16, 5),
	(17, 5),
	(18, 5),
	(19, 5),
	(26, 5),
	(27, 5),
	(29, 5),
	(30, 5),
	(32, 5),
	(33, 5),
	(34, 5),
	(35, 5),
	(38, 5),
	(41, 5),
	(42, 5),
	(43, 5),
	(1, 6),
	(2, 6),
	(15, 6),
	(16, 6),
	(17, 6),
	(18, 6),
	(19, 6),
	(29, 6),
	(30, 6),
	(32, 6),
	(35, 6),
	(38, 6),
	(40, 6),
	(42, 6),
	(45, 6),
	(46, 6),
	(47, 6),
	(48, 6),
	(49, 6),
	(50, 6),
	(51, 6),
	(52, 6),
	(15, 7),
	(16, 7),
	(17, 7),
	(19, 7),
	(29, 7),
	(30, 7),
	(35, 7),
	(38, 7),
	(50, 7),
	(51, 7),
	(52, 7);

-- Volcando estructura para tabla impacto_urbanizaciones.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.sessions: ~4 rows (aproximadamente)
DELETE FROM `sessions`;
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('5QqK3MmOQvOzCR0PxhrdCzKthXcY1TfdmOol55Ho', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJtR2VvZ3oxYVdoV1IyR0ZyaThXTVBvT2Jrd1lpd2FWMlIxTmdDcTExIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluaXN0cmFjaW9uXC91c3VhcmlvcyIsInJvdXRlIjoiYWRtaW4udXN1YXJpb3MifSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInVyYmFuaXphY2lvbl9pZCI6MX0=', 1780814347),
	('LyJ4rGek8cIlPAeTfYYPKW3zMn2jnHV0TWTRbRmG', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIxMWVzZkRoa2NLTU5SZlJxaWJobXJsTXRIc1NjNVZUOG9VSGUyVmRxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zZWxlY2Npb25hci11cmJhbml6YWNpb24iLCJyb3V0ZSI6InVyYmFuaXphY2lvbmVzLnNlbGVjdCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==', 1780845584),
	('rVU2YtyyYIJI3iApsbT94ykjDytWUIQtQzNkCMJ1', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJYb0dQcU1lZmJlRlk4RDdCdEVrOEZKRXl1SUxuaHRiY3NRcE9tOEtFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pbmlzdHJhY2lvblwvdXN1YXJpb3MiLCJyb3V0ZSI6ImFkbWluLnVzdWFyaW9zIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInVyYmFuaXphY2lvbl9pZCI6Mn0=', 1780808147),
	('TK34Rc6fN00ui0uPjb6BIlxPrd7lQibTsWEe1pMa', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJONkZkbmJ5STFPWnJ5NTFsaUlGUGI4U0lUNXJGNDNTSjJEZEpTNThEIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluaXN0cmFjaW9uXC9iYWNrdXBzIiwicm91dGUiOiJhZG1pbi5iYWNrdXBzIn0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJ1cmJhbml6YWNpb25faWQiOjF9', 1780815245);

-- Volcando estructura para tabla impacto_urbanizaciones.supervisor_profiles
CREATE TABLE IF NOT EXISTS `supervisor_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'supervisor_comercial',
  `supervisor_comercial_id` bigint unsigned DEFAULT NULL,
  `grupo_comercial_id` bigint unsigned DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ci` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `celular` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supervisor_profiles_ci_unique` (`ci`),
  UNIQUE KEY `supervisor_profiles_email_unique` (`email`),
  KEY `supervisor_profiles_user_id_foreign` (`user_id`),
  KEY `supervisor_profiles_supervisor_comercial_id_foreign` (`supervisor_comercial_id`),
  KEY `supervisor_profiles_grupo_comercial_id_foreign` (`grupo_comercial_id`),
  KEY `supervisor_profiles_tipo_activo_index` (`tipo`,`activo`),
  CONSTRAINT `supervisor_profiles_grupo_comercial_id_foreign` FOREIGN KEY (`grupo_comercial_id`) REFERENCES `grupos_comerciales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supervisor_profiles_supervisor_comercial_id_foreign` FOREIGN KEY (`supervisor_comercial_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supervisor_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.supervisor_profiles: ~1 rows (aproximadamente)
DELETE FROM `supervisor_profiles`;
INSERT INTO `supervisor_profiles` (`id`, `user_id`, `tipo`, `supervisor_comercial_id`, `grupo_comercial_id`, `nombre`, `ci`, `celular`, `email`, `direccion`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 3, 'supervisor_comercial', NULL, NULL, 'Supervisor Comercial', 'SUP-100', '70000001', 'supervisor@impacto.test', 'Oficina central', 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31');

-- Volcando estructura para tabla impacto_urbanizaciones.system_settings
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.system_settings: ~21 rows (aproximadamente)
DELETE FROM `system_settings`;
INSERT INTO `system_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
	(1, 'system_name', 'INMOLIDER CRM', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(2, 'system_subtitle', 'Sistema Integral de Terrenos', '2026-06-05 06:54:28', '2026-06-05 06:54:28'),
	(3, 'company_name', 'INMOLIDER LV', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(4, 'razon_social', 'INMOLIDER LV', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(5, 'nit', NULL, '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(6, 'direccion', 'Av 20 de octubre 2608 Sopocachi', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(7, 'ciudad', 'La Paz', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(8, 'departamento', 'La Paz', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(9, 'telefono', NULL, '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(10, 'celular', '75865765', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(11, 'whatsapp', NULL, '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(12, 'email', 'xwebia6@gmail.com', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(13, 'website', NULL, '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(14, 'footer_text', 'desarrollado por Xweb Ingenieria', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(15, 'primary_color', '#40726e', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(16, 'secondary_color', '#182f0f', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(17, 'logo_main', '', '2026-06-05 06:54:28', '2026-06-05 06:54:28'),
	(18, 'logo_login', 'logos/9R3aF271r2vqL7a3Zcd4TxZN8vIfu5Sa72FrJYTv.png', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(19, 'logo_pdf', 'logos/66lwm91cOwagUoXpYsb1MN6VCSwM4icz7tBVqZuV.png', '2026-06-05 06:54:28', '2026-06-05 07:10:50'),
	(20, 'login_background', 'login-backgrounds/zbcTuVc5WSk10WFu1kplAkeagPt2kwjQMvMZTrZc.png', '2026-06-05 08:13:28', '2026-06-05 08:27:54'),
	(21, 'public_base_url', NULL, '2026-06-07 07:05:58', '2026-06-07 07:05:58');

-- Volcando estructura para tabla impacto_urbanizaciones.urbanizaciones
CREATE TABLE IF NOT EXISTS `urbanizaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `propietario` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ubicacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `plano_imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `superficie_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `mostrar_precio_publico` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `urbanizaciones_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.urbanizaciones: ~2 rows (aproximadamente)
DELETE FROM `urbanizaciones`;
INSERT INTO `urbanizaciones` (`id`, `nombre`, `slug`, `propietario`, `ubicacion`, `descripcion`, `plano_imagen`, `superficie_total`, `estado`, `mostrar_precio_publico`, `created_at`, `updated_at`) VALUES
	(1, 'Colinas del Norte  Zona 1', 'colinas-del-norte-zona-1', 'Alfredo Merubia', 'Zona Norte La Paz', 'Proyecto residencial de demostracion comercial para el Sistema Integral de Terrenos.', 'planos/DLNBHsmysAXZU3rJuNvpHLMOm8zGHEZAY37ukEqf.jpg', 165000.00, 'activa', 1, '2026-06-05 06:54:31', '2026-06-05 08:19:04'),
	(2, 'Colinas del Norte Zona 2', 'colinas-del-norte-zona-2', 'Alfredo Merubia', 'Zona Norte La Paz', 'Proyecto residencial de demostracion comercial para el Sistema Integral de Terrenos.', 'planos/G64pBpLlk3vpu4R39QYfUUhAyvZZSO264Y8tshQd.jpg', 148000.00, 'activa', 1, '2026-06-05 06:54:31', '2026-06-05 08:07:55');

-- Volcando estructura para tabla impacto_urbanizaciones.urbanizacion_user
CREATE TABLE IF NOT EXISTS `urbanizacion_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `urbanizacion_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `urbanizacion_user_urbanizacion_id_user_id_unique` (`urbanizacion_id`,`user_id`),
  KEY `urbanizacion_user_user_id_activo_index` (`user_id`,`activo`),
  CONSTRAINT `urbanizacion_user_urbanizacion_id_foreign` FOREIGN KEY (`urbanizacion_id`) REFERENCES `urbanizaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `urbanizacion_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.urbanizacion_user: ~2 rows (aproximadamente)
DELETE FROM `urbanizacion_user`;
INSERT INTO `urbanizacion_user` (`id`, `urbanizacion_id`, `user_id`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 1, 3, 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(2, 1, 4, 1, '2026-06-05 06:54:31', '2026-06-05 06:54:31');

-- Volcando estructura para tabla impacto_urbanizaciones.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_cliente_id_foreign` (`cliente_id`),
  CONSTRAINT `users_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.users: ~5 rows (aproximadamente)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `cliente_id`, `name`, `email`, `email_verified_at`, `password`, `must_change_password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, NULL, 'Administrador Impacto', 'admin@impacto.test', '2026-06-05 06:54:31', '$2y$12$pFHeUIKbDWP46ur3AefPyuh7hrrI5lfsgryaAZpiOnooMmEfBiYcC', 0, '1p1yTMMfNl1MJYsk4ESyu4fLdxM7Z6A1MGlq4npaMrmazIEtsBPkXlmbtKep', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(2, NULL, 'Gerente Comercial', 'gerente@impacto.test', '2026-06-05 06:54:31', '$2y$12$pFHeUIKbDWP46ur3AefPyuh7hrrI5lfsgryaAZpiOnooMmEfBiYcC', 0, '66g10Kqhyd', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(3, NULL, 'Supervisor Comercial', 'supervisor@impacto.test', '2026-06-05 06:54:31', '$2y$12$pFHeUIKbDWP46ur3AefPyuh7hrrI5lfsgryaAZpiOnooMmEfBiYcC', 0, 'mkxKaENHHB', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(4, NULL, 'Asesor de Ventas', 'vendedor@impacto.test', '2026-06-05 06:54:31', '$2y$12$pFHeUIKbDWP46ur3AefPyuh7hrrI5lfsgryaAZpiOnooMmEfBiYcC', 0, 'lbB8JUzuvT', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(5, 1, 'Cliente Maria Fernandez', 'cliente@impacto.test', '2026-06-05 06:54:31', '$2y$12$pFHeUIKbDWP46ur3AefPyuh7hrrI5lfsgryaAZpiOnooMmEfBiYcC', 0, 'A8TkCEBStQ', '2026-06-05 06:54:31', '2026-06-05 06:54:31');

-- Volcando estructura para tabla impacto_urbanizaciones.ventas
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `urbanizacion_id` bigint unsigned DEFAULT NULL,
  `lote_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `reserva_id` bigint unsigned DEFAULT NULL,
  `vendedor_id` bigint unsigned DEFAULT NULL,
  `supervisor_ventas_id` bigint unsigned DEFAULT NULL,
  `supervisor_comercial_id` bigint unsigned DEFAULT NULL,
  `grupo_comercial_id` bigint unsigned DEFAULT NULL,
  `usuario_creador_id` bigint unsigned DEFAULT NULL,
  `usuario_actualizador_id` bigint unsigned DEFAULT NULL,
  `fecha_venta` date NOT NULL,
  `precio_final` decimal(12,2) NOT NULL,
  `monto_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cuota_inicial` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo_financiar` decimal(12,2) NOT NULL DEFAULT '0.00',
  `numero_cuotas` int NOT NULL DEFAULT '0',
  `tipo_venta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contado',
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ventas_reserva_id_foreign` (`reserva_id`),
  KEY `ventas_cliente_id_fecha_venta_index` (`cliente_id`,`fecha_venta`),
  KEY `ventas_lote_id_estado_index` (`lote_id`,`estado`),
  KEY `ventas_user_id_index` (`user_id`),
  KEY `ventas_supervisor_ventas_id_foreign` (`supervisor_ventas_id`),
  KEY `ventas_supervisor_comercial_id_foreign` (`supervisor_comercial_id`),
  KEY `ventas_grupo_comercial_id_foreign` (`grupo_comercial_id`),
  KEY `ventas_usuario_creador_id_foreign` (`usuario_creador_id`),
  KEY `ventas_usuario_actualizador_id_foreign` (`usuario_actualizador_id`),
  KEY `ventas_commercial_index` (`urbanizacion_id`,`grupo_comercial_id`,`fecha_venta`),
  KEY `ventas_team_index` (`vendedor_id`,`supervisor_ventas_id`),
  CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_grupo_comercial_id_foreign` FOREIGN KEY (`grupo_comercial_id`) REFERENCES `grupos_comerciales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_lote_id_foreign` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_reserva_id_foreign` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_supervisor_comercial_id_foreign` FOREIGN KEY (`supervisor_comercial_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_supervisor_ventas_id_foreign` FOREIGN KEY (`supervisor_ventas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_urbanizacion_id_foreign` FOREIGN KEY (`urbanizacion_id`) REFERENCES `urbanizaciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_usuario_actualizador_id_foreign` FOREIGN KEY (`usuario_actualizador_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_usuario_creador_id_foreign` FOREIGN KEY (`usuario_creador_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_vendedor_id_foreign` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.ventas: ~5 rows (aproximadamente)
DELETE FROM `ventas`;
INSERT INTO `ventas` (`id`, `urbanizacion_id`, `lote_id`, `cliente_id`, `user_id`, `reserva_id`, `vendedor_id`, `supervisor_ventas_id`, `supervisor_comercial_id`, `grupo_comercial_id`, `usuario_creador_id`, `usuario_actualizador_id`, `fecha_venta`, `precio_final`, `monto_total`, `cuota_inicial`, `saldo_financiar`, `numero_cuotas`, `tipo_venta`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 1, 1, '2026-06-02', 17750.00, 17750.00, 5000.00, 12750.00, 9, 'contado', 'completada', 'Venta al contado de demostracion.', '2026-06-05 06:54:31', '2026-06-05 10:16:04'),
	(2, 1, 2, 2, 1, NULL, NULL, NULL, NULL, NULL, 1, 1, '2026-04-05', 18500.00, 18500.00, 3000.00, 11625.00, 6, 'credito', 'activa', 'Venta a credito de demostracion.', '2026-06-05 06:54:31', '2026-06-05 06:54:31'),
	(3, 1, 4, 5, 1, 3, NULL, NULL, NULL, NULL, 1, 1, '2026-05-26', 20000.00, 20000.00, 5000.00, 15000.00, 36, 'credito', 'activa', 'Venta originada desde reserva convertida.', '2026-06-05 06:54:31', '2026-06-05 09:46:41'),
	(4, 1, 5, 6, 1, NULL, NULL, NULL, NULL, NULL, 1, 1, '2026-06-03', 23000.00, 20750.00, 5000.00, 18000.00, 36, 'credito', 'activa', 'Venta a credito reciente para grafico mensual.', '2026-06-05 06:54:31', '2026-06-05 09:47:56'),
	(5, NULL, 8, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-05', 24000.00, 0.00, 5000.00, 19000.00, 24, 'contado', 'activa', NULL, '2026-06-05 09:49:20', '2026-06-05 09:49:36');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
