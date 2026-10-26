-- ---------------------------------------------------------------------------
-- Gestión documental de pasantías
--
-- 1. Ejecutar primero las verificaciones. Todas deben devolver 0 filas
--    (o los valores esperados indicados) antes de aplicar los cambios.
-- 2. Ejecutar luego los cambios de la sección "Cambios".
-- ---------------------------------------------------------------------------


-- ===========================================================================
-- Verificaciones
-- ===========================================================================

-- Nombres reales de las tablas involucradas (comprobar mayúsculas/minúsculas).
SELECT TABLE_NAME, ENGINE, TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND LOWER(TABLE_NAME) IN ('documentos', 'coordinacion_academica', 'documento_historial');

-- Tipos de las columnas que participan en las claves foráneas.
-- Esperado: documentos.idDocumento = int unsigned,
--           coordinacion_academica.idCoordinador = varchar(30) con utf8mb4_0900_ai_ci.
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, CHARACTER_SET_NAME, COLLATION_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
      (LOWER(TABLE_NAME) = 'documentos' AND COLUMN_NAME IN ('idDocumento', 'idPasantia', 'idFormato'))
      OR (LOWER(TABLE_NAME) = 'coordinacion_academica' AND COLUMN_NAME = 'idCoordinador')
  );

-- Duplicados de pasantía + formato (deben resolverse antes del UNIQUE).
SELECT idPasantia, idFormato, COUNT(*) AS cantidad, GROUP_CONCAT(idDocumento) AS documentos
FROM documentos
GROUP BY idPasantia, idFormato
HAVING COUNT(*) > 1;

-- Documentos sin formato. MySQL permite varios NULL en un índice UNIQUE,
-- así que estas filas no quedarían protegidas contra duplicados.
SELECT idDocumento, idPasantia
FROM documentos
WHERE idFormato IS NULL;

-- Estados fuera de los valores que usará el módulo.
SELECT idDocumento, estadoArchivo
FROM documentos
WHERE estadoArchivo NOT IN ('Pendiente', 'A corregir', 'Rechazado', 'Aprobado');


-- ===========================================================================
-- Cambios
-- ===========================================================================

ALTER TABLE `documentos`
ADD UNIQUE KEY `UQ_Documentos_Pasantia_Formato`
    (`idPasantia`, `idFormato`);

CREATE TABLE `documento_historial` (
    `idHistorial` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `idDocumento` INT UNSIGNED NOT NULL,
    `archivo` VARCHAR(255) NOT NULL,
    `version` INT UNSIGNED NOT NULL DEFAULT 1,
    `estadoArchivo` ENUM(
        'Pendiente',
        'A corregir',
        'Rechazado',
        'Aprobado'
    ) NOT NULL DEFAULT 'Pendiente',
    `observacion` VARCHAR(200) DEFAULT NULL,
    `idCoordinador` VARCHAR(30) DEFAULT NULL,
    `fechaEnvio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fechaRevision` DATETIME DEFAULT NULL,

    PRIMARY KEY (`idHistorial`),

    UNIQUE KEY `UQ_Historial_Documento_Version`
        (`idDocumento`, `version`),

    KEY `IDX_Historial_Coordinador` (`idCoordinador`),

    CONSTRAINT `FK_Historial_Documento`
        FOREIGN KEY (`idDocumento`)
        REFERENCES `documentos` (`idDocumento`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT `FK_Historial_Coordinador`
        FOREIGN KEY (`idCoordinador`)
        REFERENCES `coordinacion_academica` (`idCoordinador`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;

-- Opcional (recomendado si la consulta de "Documentos sin formato" devolvió 0 filas):
-- obliga a que todo documento tenga formato, para que el UNIQUE lo cubra siempre.
-- ALTER TABLE `documentos` MODIFY `idFormato` INT UNSIGNED NOT NULL;
