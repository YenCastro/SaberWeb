-- =====================================================================
-- SaberWeb - Migración del Día 1
-- Ejecutar UNA SOLA VEZ en phpMyAdmin, sobre la base de datos `saberweb`
-- (pestaña SQL). Haz una copia de respaldo antes (Exportar).
-- =====================================================================

-- 1) resultados: apuntar al simulacro (ahí está la duración), no a la prueba
ALTER TABLE `resultados` DROP FOREIGN KEY `fk_resultado_prueba`;
ALTER TABLE `resultados` DROP INDEX `fk_resultado_prueba`;
ALTER TABLE `resultados`
  CHANGE `id_prueba` `id_simulacro` int(11) NOT NULL,
  MODIFY `puntaje` int(11) NOT NULL DEFAULT 0,
  ADD `estado` enum('En curso','Finalizado') NOT NULL DEFAULT 'En curso' AFTER `respuestas_incorrectas`,
  ADD `fecha_fin` timestamp NULL DEFAULT NULL AFTER `fecha_realizacion`,
  ADD CONSTRAINT `fk_resultado_simulacro`
      FOREIGN KEY (`id_simulacro`) REFERENCES `simulacro` (`id_simulacro`);

-- 2) Permitir eliminar una cuenta que ya tiene resultados
ALTER TABLE `resultados` DROP FOREIGN KEY `fk_resultado_cuenta`;
ALTER TABLE `resultados`
  ADD CONSTRAINT `fk_resultado_cuenta`
      FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE CASCADE;

-- 3) Una sola respuesta por pregunta en cada intento
ALTER TABLE `respuestas_estudiante`
  ADD UNIQUE KEY `uq_resultado_pregunta` (`id_resultado`, `id_pregunta`);

-- 4) Una sola opción con la misma letra por pregunta
ALTER TABLE `opciones_rta`
  ADD UNIQUE KEY `uq_pregunta_letra` (`id_pregunta`, `letra`);

-- 5) Una cuenta administradora para probar el panel
UPDATE `cuentas` SET `rol` = 'Administrador' WHERE `correo` = 'castroalexandra@gmail.com';
