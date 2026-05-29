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
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ci` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `celular` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grupo_comercial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asesores_ci_unique` (`ci`),
  UNIQUE KEY `asesores_email_unique` (`email`),
  KEY `asesores_user_id_foreign` (`user_id`),
  KEY `asesores_supervisor_id_activo_index` (`supervisor_id`,`activo`),
  CONSTRAINT `asesores_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asesores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.asesores: ~0 rows (aproximadamente)

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.audit_logs: ~1 rows (aproximadamente)
INSERT INTO `audit_logs` (`id`, `user_id`, `modelo`, `modelo_id`, `accion`, `descripcion`, `datos_anteriores`, `datos_nuevos`, `ip`, `user_agent`, `created_at`) VALUES
	(1, 1, 'Role', NULL, 'configurar_roles_permisos', 'Roles y permisos iniciales configurados por seeder.', NULL, '{"permissions": ["ver dashboard", "ver lotes", "ver clientes", "ver ventas", "ver reservas", "crear urbanizaciones", "editar urbanizaciones", "eliminar urbanizaciones", "crear manzanos", "editar manzanos", "eliminar manzanos", "crear lotes", "editar lotes", "eliminar lotes", "crear clientes", "editar clientes", "eliminar clientes", "crear ventas", "editar ventas", "anular ventas", "crear reservas", "editar reservas", "cancelar reservas", "cobrar cuotas", "anular caja", "ver reportes", "exportar reportes", "administrar usuarios", "crear asesores", "editar asesores", "desactivar asesores", "asignar urbanizaciones a asesores", "resetear contraseña asesor"]}', NULL, NULL, '2026-05-27 23:17:14');

-- Volcando estructura para tabla impacto_urbanizaciones.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.cache: ~1 rows (aproximadamente)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('impacto-urbanizaciones-cache-spatie.permission.cache', 'a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:33:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:13:"ver dashboard";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:9:"ver lotes";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:12:"ver clientes";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:10:"ver ventas";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:12:"ver reservas";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:20:"crear urbanizaciones";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:6;a:4:{s:1:"a";i:7;s:1:"b";s:21:"editar urbanizaciones";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:7;a:4:{s:1:"a";i:8;s:1:"b";s:23:"eliminar urbanizaciones";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:8;a:4:{s:1:"a";i:9;s:1:"b";s:14:"crear manzanos";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:9;a:4:{s:1:"a";i:10;s:1:"b";s:15:"editar manzanos";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:10;a:4:{s:1:"a";i:11;s:1:"b";s:17:"eliminar manzanos";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:11;a:4:{s:1:"a";i:12;s:1:"b";s:11:"crear lotes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:"a";i:13;s:1:"b";s:12:"editar lotes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:14:"eliminar lotes";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:14:"crear clientes";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:15:"editar clientes";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:17:"eliminar clientes";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:12:"crear ventas";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:18;a:4:{s:1:"a";i:19;s:1:"b";s:13:"editar ventas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:13:"anular ventas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:14:"crear reservas";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:15:"editar reservas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:17:"cancelar reservas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:13:"cobrar cuotas";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:11:"anular caja";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:12:"ver reportes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:17:"exportar reportes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:20:"administrar usuarios";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:14:"crear asesores";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:29;a:4:{s:1:"a";i:30;s:1:"b";s:15:"editar asesores";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:30;a:4:{s:1:"a";i:31;s:1:"b";s:19:"desactivar asesores";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:31;a:4:{s:1:"a";i:32;s:1:"b";s:33:"asignar urbanizaciones a asesores";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:32;a:4:{s:1:"a";i:33;s:1:"b";s:27:"resetear contraseña asesor";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}}s:5:"roles";a:4:{i:0;a:3:{s:1:"a";i:1;s:1:"b";s:13:"administrador";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:2;s:1:"b";s:7:"gerente";s:1:"c";s:3:"web";}i:2;a:3:{s:1:"a";i:3;s:1:"b";s:10:"supervisor";s:1:"c";s:3:"web";}i:3;a:3:{s:1:"a";i:4;s:1:"b";s:8:"vendedor";s:1:"c";s:3:"web";}}}', 1779995854);

-- Volcando estructura para tabla impacto_urbanizaciones.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.cache_locks: ~0 rows (aproximadamente)

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.cash_movements: ~9 rows (aproximadamente)
INSERT INTO `cash_movements` (`id`, `user_id`, `cliente_id`, `sale_id`, `reservation_id`, `installment_id`, `tipo`, `concepto`, `metodo_pago`, `monto`, `fecha`, `referencia`, `observaciones`, `estado`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, NULL, NULL, 'ingreso', 'contado', 'transferencia', 17750.00, '2026-05-24', 'TRF-00045', NULL, 'confirmado', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(2, 1, 2, 2, NULL, NULL, 'ingreso', 'anticipo', 'QR', 3000.00, '2026-03-27', 'QR-ANT-102', NULL, 'confirmado', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(3, 1, 2, 2, NULL, 1, 'ingreso', 'cuota', 'banco', 2583.33, '2026-05-16', 'REC-CUOTA-1', NULL, 'confirmado', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(4, 1, 2, 2, NULL, 2, 'ingreso', 'cuota', 'efectivo', 1291.67, '2026-05-15', 'REC-CUOTA-2', NULL, 'confirmado', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(5, 1, 3, NULL, 1, NULL, 'ingreso', 'reserva', 'efectivo', 800.00, '2026-05-26', 'RES-ACT-001', NULL, 'confirmado', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(6, 1, 4, NULL, 2, NULL, 'ingreso', 'reserva', 'otro', 500.00, '2026-05-15', 'RES-VEN-001', NULL, 'confirmado', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(7, 1, 5, NULL, 3, NULL, 'ingreso', 'reserva', 'QR', 1000.00, '2026-05-09', 'RES-CONV-001', NULL, 'confirmado', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(8, 1, 5, 3, NULL, NULL, 'ingreso', 'anticipo', 'transferencia', 4500.00, '2026-05-17', 'ANT-CONV-001', NULL, 'confirmado', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(9, 1, 6, 4, NULL, NULL, 'ingreso', 'anticipo', 'banco', 2500.00, '2026-05-03', 'ANT-MES-001', NULL, 'confirmado', '2026-05-27 23:17:15', '2026-05-27 23:17:15');

-- Volcando estructura para tabla impacto_urbanizaciones.clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clientes_documento_index` (`documento`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.clientes: ~7 rows (aproximadamente)
INSERT INTO `clientes` (`id`, `nombre`, `documento`, `telefono`, `email`, `direccion`, `created_at`, `updated_at`) VALUES
	(1, 'Mariela Fernandez Rojas', 'CI-4829137 SC', '77012345', 'mariela.fernandez@example.com', 'Av. Banzer 5to anillo, Santa Cruz', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(2, 'Carlos Alberto Rojas Perez', 'CI-6392841 SC', '72100455', 'carlos.rojas@example.com', 'Barrio Las Palmas, calle 4 #28', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(3, 'Ana Lucia Vargas Medina', 'CI-7519283 CB', '69033412', 'ana.vargas@example.com', 'Condominio Sevilla Norte, bloque B', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(4, 'Luis Fernando Mercado Salvatierra', 'CI-5849302 SC', '73188220', 'luis.mercado@example.com', 'Av. Virgen de Cotoca km 7', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(5, 'Patricia Suarez Aguilera', 'CI-8263145 SC', '75611908', 'patricia.suarez@example.com', 'Zona Equipetrol, calle Los Tajibos #17', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(6, 'Jorge Andres Quiroga Mendez', 'CI-4927610 LP', '70844591', 'jorge.quiroga@example.com', 'Av. Mutualista, condominio El Portal', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(7, 'Natalia Rivero Chavez', 'CI-9182736 SC', '67753122', 'natalia.rivero@example.com', 'Plan 3000, barrio Primavera', '2026-05-27 23:17:14', '2026-05-27 23:17:14');

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.cuotas: ~15 rows (aproximadamente)
INSERT INTO `cuotas` (`id`, `venta_id`, `numero`, `fecha_programada`, `fecha_vencimiento`, `monto`, `fecha_pago`, `monto_pagado`, `saldo_pendiente`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 2, 1, '2026-04-27', '2026-04-27', 2583.33, '2026-04-25', 2583.33, 0.00, 'pagada', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(2, 2, 2, '2026-05-27', '2026-05-27', 2583.33, NULL, 1291.67, 1291.66, 'parcial', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(3, 2, 3, '2026-06-27', '2026-06-27', 2583.33, NULL, 0.00, 2583.33, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(4, 2, 4, '2026-07-27', '2026-07-27', 2583.33, NULL, 0.00, 2583.33, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(5, 2, 5, '2026-08-27', '2026-08-27', 2583.33, NULL, 0.00, 2583.33, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(6, 2, 6, '2026-09-27', '2026-09-27', 2583.33, NULL, 0.00, 2583.33, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(7, 3, 1, '2026-06-27', '2026-06-27', 3875.00, NULL, 0.00, 3875.00, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(8, 3, 2, '2026-07-27', '2026-07-27', 3875.00, NULL, 0.00, 3875.00, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(9, 3, 3, '2026-08-27', '2026-08-27', 3875.00, NULL, 0.00, 3875.00, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(10, 3, 4, '2026-09-27', '2026-09-27', 3875.00, NULL, 0.00, 3875.00, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(11, 4, 1, '2026-06-27', '2026-06-27', 3650.00, NULL, 0.00, 3650.00, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(12, 4, 2, '2026-07-27', '2026-07-27', 3650.00, NULL, 0.00, 3650.00, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(13, 4, 3, '2026-08-27', '2026-08-27', 3650.00, NULL, 0.00, 3650.00, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(14, 4, 4, '2026-09-27', '2026-09-27', 3650.00, NULL, 0.00, 3650.00, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(15, 4, 5, '2026-10-27', '2026-10-27', 3650.00, NULL, 0.00, 3650.00, 'pendiente', NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15');

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
INSERT INTO `lotes` (`id`, `manzano_id`, `codigo`, `superficie`, `precio`, `estado`, `fila`, `columna`, `coord_x`, `coord_y`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 1, '01', 288.00, 17750.00, 'vendido', 1, 1, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:15'),
	(2, 1, '02', 296.00, 18500.00, 'vendido', 1, 2, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:15'),
	(3, 1, '03', 304.00, 19250.00, 'reservado', 1, 3, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:15'),
	(4, 1, '04', 312.00, 20000.00, 'vendido', 1, 4, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:15'),
	(5, 1, '05', 320.00, 20750.00, 'vendido', 1, 5, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:15'),
	(6, 1, '06', 328.00, 21500.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(7, 1, '07', 336.00, 22250.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(8, 1, '08', 344.00, 23000.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(9, 1, '09', 352.00, 23750.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(10, 1, '10', 360.00, 24500.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(11, 1, '11', 368.00, 25250.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(12, 1, '12', 376.00, 26000.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(13, 1, '13', 384.00, 26750.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(14, 1, '14', 392.00, 27500.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(15, 1, '15', 400.00, 28250.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(16, 2, '01', 288.00, 17750.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(17, 2, '02', 296.00, 18500.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(18, 2, '03', 304.00, 19250.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(19, 2, '04', 312.00, 20000.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(20, 2, '05', 320.00, 20750.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(21, 2, '06', 328.00, 21500.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(22, 2, '07', 336.00, 22250.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(23, 2, '08', 344.00, 23000.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(24, 2, '09', 352.00, 23750.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(25, 2, '10', 360.00, 24500.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(26, 2, '11', 368.00, 25250.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(27, 2, '12', 376.00, 26000.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(28, 2, '13', 384.00, 26750.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(29, 2, '14', 392.00, 27500.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(30, 2, '15', 400.00, 28250.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(31, 3, '01', 288.00, 17750.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(32, 3, '02', 296.00, 18500.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(33, 3, '03', 304.00, 19250.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(34, 3, '04', 312.00, 20000.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(35, 3, '05', 320.00, 20750.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(36, 3, '06', 328.00, 21500.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(37, 3, '07', 336.00, 22250.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(38, 3, '08', 344.00, 23000.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(39, 3, '09', 352.00, 23750.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(40, 3, '10', 360.00, 24500.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(41, 3, '11', 368.00, 25250.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(42, 3, '12', 376.00, 26000.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(43, 3, '13', 384.00, 26750.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(44, 3, '14', 392.00, 27500.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(45, 3, '15', 400.00, 28250.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(46, 4, '01', 288.00, 17750.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(47, 4, '02', 296.00, 18500.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(48, 4, '03', 304.00, 19250.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(49, 4, '04', 312.00, 20000.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(50, 4, '05', 320.00, 20750.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(51, 4, '06', 328.00, 21500.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(52, 4, '07', 336.00, 22250.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(53, 4, '08', 344.00, 23000.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(54, 4, '09', 352.00, 23750.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(55, 4, '10', 360.00, 24500.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(56, 4, '11', 368.00, 25250.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(57, 4, '12', 376.00, 26000.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(58, 4, '13', 384.00, 26750.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(59, 4, '14', 392.00, 27500.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(60, 4, '15', 400.00, 28250.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(61, 5, '01', 300.00, 18950.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(62, 5, '02', 308.00, 19700.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(63, 5, '03', 316.00, 20450.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(64, 5, '04', 324.00, 21200.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(65, 5, '05', 332.00, 21950.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(66, 5, '06', 340.00, 22700.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(67, 5, '07', 348.00, 23450.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(68, 5, '08', 356.00, 24200.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(69, 5, '09', 364.00, 24950.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(70, 5, '10', 372.00, 25700.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(71, 5, '11', 380.00, 26450.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(72, 5, '12', 388.00, 27200.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(73, 5, '13', 396.00, 27950.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(74, 5, '14', 404.00, 28700.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(75, 5, '15', 412.00, 29450.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(76, 6, '01', 300.00, 18950.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(77, 6, '02', 308.00, 19700.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(78, 6, '03', 316.00, 20450.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(79, 6, '04', 324.00, 21200.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(80, 6, '05', 332.00, 21950.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(81, 6, '06', 340.00, 22700.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(82, 6, '07', 348.00, 23450.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(83, 6, '08', 356.00, 24200.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(84, 6, '09', 364.00, 24950.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(85, 6, '10', 372.00, 25700.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(86, 6, '11', 380.00, 26450.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(87, 6, '12', 388.00, 27200.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(88, 6, '13', 396.00, 27950.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(89, 6, '14', 404.00, 28700.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(90, 6, '15', 412.00, 29450.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(91, 7, '01', 300.00, 18950.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(92, 7, '02', 308.00, 19700.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(93, 7, '03', 316.00, 20450.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(94, 7, '04', 324.00, 21200.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(95, 7, '05', 332.00, 21950.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(96, 7, '06', 340.00, 22700.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(97, 7, '07', 348.00, 23450.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(98, 7, '08', 356.00, 24200.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(99, 7, '09', 364.00, 24950.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(100, 7, '10', 372.00, 25700.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(101, 7, '11', 380.00, 26450.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(102, 7, '12', 388.00, 27200.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(103, 7, '13', 396.00, 27950.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(104, 7, '14', 404.00, 28700.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(105, 7, '15', 412.00, 29450.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(106, 8, '01', 300.00, 18950.00, 'disponible', 1, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(107, 8, '02', 308.00, 19700.00, 'disponible', 1, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(108, 8, '03', 316.00, 20450.00, 'disponible', 1, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(109, 8, '04', 324.00, 21200.00, 'disponible', 1, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(110, 8, '05', 332.00, 21950.00, 'disponible', 1, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(111, 8, '06', 340.00, 22700.00, 'disponible', 2, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(112, 8, '07', 348.00, 23450.00, 'disponible', 2, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(113, 8, '08', 356.00, 24200.00, 'disponible', 2, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(114, 8, '09', 364.00, 24950.00, 'disponible', 2, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(115, 8, '10', 372.00, 25700.00, 'disponible', 2, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(116, 8, '11', 380.00, 26450.00, 'disponible', 3, 1, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(117, 8, '12', 388.00, 27200.00, 'disponible', 3, 2, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(118, 8, '13', 396.00, 27950.00, 'disponible', 3, 3, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(119, 8, '14', 404.00, 28700.00, 'disponible', 3, 4, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(120, 8, '15', 412.00, 29450.00, 'bloqueado', 3, 5, NULL, NULL, NULL, '2026-05-27 23:17:15', '2026-05-27 23:17:15');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.lot_histories: ~6 rows (aproximadamente)
INSERT INTO `lot_histories` (`id`, `lote_id`, `user_id`, `accion`, `descripcion`, `estado_anterior`, `estado_nuevo`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 'lote_vendido', 'Venta al contado.', 'disponible', 'vendido', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(2, 2, 1, 'lote_vendido', 'Venta a credito.', 'disponible', 'vendido', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(3, 3, 1, 'reserva_creada', 'Reserva activa.', 'disponible', 'reservado', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(4, 4, 1, 'reserva_vencida', 'Reserva vencida y lote liberado.', 'reservado', 'disponible', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(5, 4, 1, 'reserva_convertida', 'La reserva fue convertida en venta.', 'reservado', 'vendido', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(6, 5, 1, 'lote_vendido', 'Venta a credito reciente.', 'disponible', 'vendido', '2026-05-27 23:17:15', '2026-05-27 23:17:15');

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
INSERT INTO `manzanos` (`id`, `urbanizacion_id`, `codigo`, `nombre`, `orden`, `created_at`, `updated_at`) VALUES
	(1, 1, 'A', 'Manzano A', 1, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(2, 1, 'B', 'Manzano B', 2, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(3, 1, 'C', 'Manzano C', 3, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(4, 1, 'D', 'Manzano D', 4, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(5, 2, 'A', 'Manzano A', 1, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(6, 2, 'B', 'Manzano B', 2, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(7, 2, 'C', 'Manzano C', 3, '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(8, 2, 'D', 'Manzano D', 4, '2026-05-27 23:17:15', '2026-05-27 23:17:15');

-- Volcando estructura para tabla impacto_urbanizaciones.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.migrations: ~21 rows (aproximadamente)
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
	(21, '2026_05_27_174633_add_must_change_password_to_users_table', 1);

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

-- Volcando estructura para tabla impacto_urbanizaciones.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.model_has_roles: ~5 rows (aproximadamente)
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
	(1, 'App\\Models\\User', 1),
	(2, 'App\\Models\\User', 2),
	(3, 'App\\Models\\User', 3),
	(4, 'App\\Models\\User', 4),
	(5, 'App\\Models\\User', 5);

-- Volcando estructura para tabla impacto_urbanizaciones.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.password_reset_tokens: ~0 rows (aproximadamente)

-- Volcando estructura para tabla impacto_urbanizaciones.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.permissions: ~33 rows (aproximadamente)
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'ver dashboard', 'web', '2026-05-27 23:17:13', '2026-05-27 23:17:13'),
	(2, 'ver lotes', 'web', '2026-05-27 23:17:13', '2026-05-27 23:17:13'),
	(3, 'ver clientes', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(4, 'ver ventas', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(5, 'ver reservas', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(6, 'crear urbanizaciones', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(7, 'editar urbanizaciones', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(8, 'eliminar urbanizaciones', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(9, 'crear manzanos', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(10, 'editar manzanos', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(11, 'eliminar manzanos', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(12, 'crear lotes', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(13, 'editar lotes', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(14, 'eliminar lotes', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(15, 'crear clientes', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(16, 'editar clientes', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(17, 'eliminar clientes', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(18, 'crear ventas', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(19, 'editar ventas', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(20, 'anular ventas', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(21, 'crear reservas', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(22, 'editar reservas', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(23, 'cancelar reservas', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(24, 'cobrar cuotas', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(25, 'anular caja', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(26, 'ver reportes', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(27, 'exportar reportes', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(28, 'administrar usuarios', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(29, 'crear asesores', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(30, 'editar asesores', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(31, 'desactivar asesores', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(32, 'asignar urbanizaciones a asesores', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(33, 'resetear contraseña asesor', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14');

-- Volcando estructura para tabla impacto_urbanizaciones.reservas
CREATE TABLE IF NOT EXISTS `reservas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned NOT NULL,
  `lote_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `fecha_reserva` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `monto_reserva` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservas_usuario_id_foreign` (`usuario_id`),
  KEY `reservas_estado_fecha_vencimiento_index` (`estado`,`fecha_vencimiento`),
  KEY `reservas_cliente_id_estado_index` (`cliente_id`,`estado`),
  KEY `reservas_lote_id_estado_index` (`lote_id`,`estado`),
  CONSTRAINT `reservas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reservas_lote_id_foreign` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reservas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.reservas: ~3 rows (aproximadamente)
INSERT INTO `reservas` (`id`, `cliente_id`, `lote_id`, `usuario_id`, `fecha_reserva`, `fecha_vencimiento`, `monto_reserva`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 3, 3, 1, '2026-05-26', '2026-06-02', 800.00, 'activa', 'Reserva activa de demostracion.', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(2, 4, 4, 1, '2026-05-15', '2026-05-22', 500.00, 'vencida', 'Reserva vencida de demostracion; lote liberado.', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(3, 5, 4, 1, '2026-05-09', '2026-05-16', 1000.00, 'convertida', 'Reserva convertida en venta durante la demo.', '2026-05-27 23:17:15', '2026-05-27 23:17:15');

-- Volcando estructura para tabla impacto_urbanizaciones.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.roles: ~5 rows (aproximadamente)
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'administrador', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(2, 'gerente', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(3, 'supervisor', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(4, 'vendedor', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(5, 'cliente', 'web', '2026-05-27 23:17:14', '2026-05-27 23:17:14');

-- Volcando estructura para tabla impacto_urbanizaciones.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.role_has_permissions: ~77 rows (aproximadamente)
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
	(1, 2),
	(2, 2),
	(3, 2),
	(4, 2),
	(5, 2),
	(12, 2),
	(13, 2),
	(15, 2),
	(16, 2),
	(18, 2),
	(19, 2),
	(20, 2),
	(21, 2),
	(22, 2),
	(23, 2),
	(24, 2),
	(25, 2),
	(26, 2),
	(27, 2),
	(1, 3),
	(2, 3),
	(3, 3),
	(4, 3),
	(5, 3),
	(15, 3),
	(16, 3),
	(18, 3),
	(21, 3),
	(24, 3),
	(29, 3),
	(30, 3),
	(31, 3),
	(32, 3),
	(33, 3),
	(1, 4),
	(2, 4),
	(3, 4),
	(4, 4),
	(5, 4),
	(15, 4),
	(16, 4),
	(18, 4),
	(21, 4),
	(24, 4);

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

-- Volcando datos para la tabla impacto_urbanizaciones.sessions: ~1 rows (aproximadamente)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('PXDDmYK4OuI5CFBcsCj6q84MmmmETSDmlWuNUXJN', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIwU1ZGRjJ3U2dEM0UxYW9tMllRRVMzODJKV0NubFJaS1RzaFl6WGg5IiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9yZXBvcnRlc1wvcmVzZXJ2YXMiLCJyb3V0ZSI6InJlcG9ydGVzLnJlc2VydmFzIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInVyYmFuaXphY2lvbl9pZCI6MX0=', 1779913034);

-- Volcando estructura para tabla impacto_urbanizaciones.urbanizaciones
CREATE TABLE IF NOT EXISTS `urbanizaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `plano_imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `superficie_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `mostrar_precio_publico` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.urbanizaciones: ~2 rows (aproximadamente)
INSERT INTO `urbanizaciones` (`id`, `nombre`, `ubicacion`, `descripcion`, `plano_imagen`, `superficie_total`, `estado`, `mostrar_precio_publico`, `created_at`, `updated_at`) VALUES
	(1, 'Colinas del Urubo', 'Zona Urubo, Santa Cruz', 'Proyecto residencial de demostracion comercial para el Sistema Integral de Terrenos.', NULL, 165000.00, 'activa', 1, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(2, 'Jardines del Norte', 'Carretera al Norte, km 12', 'Proyecto residencial de demostracion comercial para el Sistema Integral de Terrenos.', NULL, 148000.00, 'activa', 1, '2026-05-27 23:17:15', '2026-05-27 23:17:15');

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
INSERT INTO `urbanizacion_user` (`id`, `urbanizacion_id`, `user_id`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 1, 3, 1, '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(2, 1, 4, 1, '2026-05-27 23:17:14', '2026-05-27 23:17:14');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.users: ~5 rows (aproximadamente)
INSERT INTO `users` (`id`, `cliente_id`, `name`, `email`, `email_verified_at`, `password`, `must_change_password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, NULL, 'Administrador Impacto', 'admin@impacto.test', '2026-05-27 23:17:14', '$2y$12$exsD/VHNpQr7ziXq3elzSOUDj0ks8Sduj7GzkVLGVhqc4mgbqqkrm', 0, 'qIqGcbJzjL', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(2, NULL, 'Gerente Comercial', 'gerente@impacto.test', '2026-05-27 23:17:14', '$2y$12$exsD/VHNpQr7ziXq3elzSOUDj0ks8Sduj7GzkVLGVhqc4mgbqqkrm', 0, 'KG1drGwOWT', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(3, NULL, 'Supervisor Comercial', 'supervisor@impacto.test', '2026-05-27 23:17:14', '$2y$12$exsD/VHNpQr7ziXq3elzSOUDj0ks8Sduj7GzkVLGVhqc4mgbqqkrm', 0, 'CC9n5HoC2f', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(4, NULL, 'Asesor de Ventas', 'vendedor@impacto.test', '2026-05-27 23:17:14', '$2y$12$exsD/VHNpQr7ziXq3elzSOUDj0ks8Sduj7GzkVLGVhqc4mgbqqkrm', 0, 'VOdbTD7R3s', '2026-05-27 23:17:14', '2026-05-27 23:17:14'),
	(5, 1, 'Cliente Maria Fernandez', 'cliente@impacto.test', '2026-05-27 23:17:14', '$2y$12$exsD/VHNpQr7ziXq3elzSOUDj0ks8Sduj7GzkVLGVhqc4mgbqqkrm', 0, 'TnqZTRHDPb', '2026-05-27 23:17:14', '2026-05-27 23:17:14');

-- Volcando estructura para tabla impacto_urbanizaciones.ventas
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lote_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `reserva_id` bigint unsigned DEFAULT NULL,
  `fecha_venta` date NOT NULL,
  `precio_final` decimal(12,2) NOT NULL,
  `cuota_inicial` decimal(12,2) NOT NULL DEFAULT '0.00',
  `numero_cuotas` int NOT NULL DEFAULT '0',
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ventas_reserva_id_foreign` (`reserva_id`),
  KEY `ventas_cliente_id_fecha_venta_index` (`cliente_id`,`fecha_venta`),
  KEY `ventas_lote_id_estado_index` (`lote_id`,`estado`),
  KEY `ventas_user_id_index` (`user_id`),
  CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_lote_id_foreign` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_reserva_id_foreign` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla impacto_urbanizaciones.ventas: ~4 rows (aproximadamente)
INSERT INTO `ventas` (`id`, `lote_id`, `cliente_id`, `user_id`, `reserva_id`, `fecha_venta`, `precio_final`, `cuota_inicial`, `numero_cuotas`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, NULL, '2026-05-24', 17750.00, 0.00, 0, 'completada', 'Venta al contado de demostracion.', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(2, 2, 2, 1, NULL, '2026-03-27', 18500.00, 3000.00, 6, 'activa', 'Venta a credito de demostracion.', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(3, 4, 5, 1, 3, '2026-05-17', 20000.00, 4500.00, 4, 'activa', 'Venta originada desde reserva convertida.', '2026-05-27 23:17:15', '2026-05-27 23:17:15'),
	(4, 5, 6, 1, NULL, '2026-05-03', 20750.00, 2500.00, 5, 'activa', 'Venta a credito reciente para grafico mensual.', '2026-05-27 23:17:15', '2026-05-27 23:17:15');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
