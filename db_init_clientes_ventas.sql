-- Script de inicialización: tablas `clientes` y `ventas`
-- Nota: Este script usa funciones JSON_TABLE (MySQL 8.0+). Si usas una versión anterior, necesitas adaptar el cálculo del total (por ejemplo con triggers manuales en PHP o usando columnas adicionales).

SET @OLD_SQL_MODE = @@sql_mode;
SET sql_mode = 'TRADITIONAL';

-- Tabla de clientes
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(150) NOT NULL,
	`email` VARCHAR(150) DEFAULT NULL,
	`telefono` VARCHAR(50) DEFAULT NULL,
	`direccion` VARCHAR(255) DEFAULT NULL,
	`fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Tabla de ventas
-- La columna `detalles` guarda un array JSON con objetos: { producto_id, cantidad, precio_unitario }
DROP TABLE IF EXISTS `ventas`;
CREATE TABLE `ventas` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`cliente_id` INT NOT NULL,
	`detalles` JSON NOT NULL,
	`total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
	`fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `idx_cliente` (`cliente_id`),
	CONSTRAINT `fk_ventas_clientes` FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Triggers para calcular automáticamente el total a partir del JSON `detalles`
DELIMITER $$
CREATE TRIGGER `ventas_before_insert`
BEFORE INSERT ON `ventas`
FOR EACH ROW
BEGIN
	DECLARE _tot DECIMAL(18,4) DEFAULT 0.00;
	-- JSON_TABLE requiere MySQL 8.0+
	SELECT SUM(jt.cantidad * jt.precio_unitario) INTO _tot
	FROM JSON_TABLE(NEW.detalles, '$[*]'
			COLUMNS(
				producto_id INT PATH '$.producto_id',
				cantidad INT PATH '$.cantidad',
				precio_unitario DECIMAL(12,2) PATH '$.precio_unitario'
			)
	) AS jt;

	IF _tot IS NULL THEN
		SET NEW.total = 0.00;
	ELSE
		SET NEW.total = ROUND(_tot,2);
	END IF;
END$$

CREATE TRIGGER `ventas_before_update`
BEFORE UPDATE ON `ventas`
FOR EACH ROW
BEGIN
	DECLARE _tot DECIMAL(18,4) DEFAULT 0.00;
	SELECT SUM(jt.cantidad * jt.precio_unitario) INTO _tot
	FROM JSON_TABLE(NEW.detalles, '$[*]'
			COLUMNS(
				producto_id INT PATH '$.producto_id',
				cantidad INT PATH '$.cantidad',
				precio_unitario DECIMAL(12,2) PATH '$.precio_unitario'
			)
	) AS jt;

	IF _tot IS NULL THEN
		SET NEW.total = 0.00;
	ELSE
		SET NEW.total = ROUND(_tot,2);
	END IF;
END$$
DELIMITER ;

-- Inserts de ejemplo en español
INSERT INTO `clientes` (`nombre`, `email`, `telefono`, `direccion`) VALUES
('Daniel Figueroa', 'daniel.figueroa@example.com', '555-1234', 'Av. Principal 123'),
('Camila Herrera', 'camila.herrera@example.com', '555-5678', 'Calle Secundaria 45');

-- Ejemplo de venta con múltiples productos (producto_id referencian IDs externos de productos)
INSERT INTO `ventas` (`cliente_id`, `detalles`) VALUES
(1, JSON_ARRAY(
		JSON_OBJECT('producto_id', 1, 'cantidad', 2, 'precio_unitario', 150.00),
		JSON_OBJECT('producto_id', 3, 'cantidad', 1, 'precio_unitario', 75.50)
)),
(2, JSON_ARRAY(
		JSON_OBJECT('producto_id', 2, 'cantidad', 5, 'precio_unitario', 20.00),
		JSON_OBJECT('producto_id', 4, 'cantidad', 2, 'precio_unitario', 45.25)
));

-- Mostrar contenido para verificación
SELECT * FROM `clientes`;
SELECT id, cliente_id, total, fecha, detalles FROM `ventas`;

SET sql_mode = @OLD_SQL_MODE;

