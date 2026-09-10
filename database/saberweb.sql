-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-09-2026 a las 21:56:36
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `saberweb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas`
--

CREATE TABLE `cuentas` (
  `id_cuenta` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `correo` varchar(200) NOT NULL,
  `contrasenia` varchar(200) NOT NULL,
  `rol` enum('Estudiante','Administrador') NOT NULL DEFAULT 'Estudiante'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cuentas`
--

INSERT INTO `cuentas` (`id_cuenta`, `nombre_completo`, `correo`, `contrasenia`, `rol`) VALUES
(6, 'Daniel Casas', 'danielsteven360@gmail.com', '$2y$10$fVxsP2mzk1RXVBOahPFP4.kS0sdtf3eEz3IIRPPbMSUQ.2IrZQVlW', 'Estudiante'),
(7, 'Alexandra Castro', 'castroalexandra@gmail.com', '$2y$10$jx4AgogVERA2QKI1YgztK.YVhrMsJ2r5JHYk7M7e2Uvtj9nWHQJ4a', 'Estudiante');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_cuenta` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `visto` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_notificacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opciones_rta`
--

CREATE TABLE `opciones_rta` (
  `id_opcion` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `letra` char(1) NOT NULL,
  `texto` text NOT NULL,
  `es_correcta` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id_pregunta` int(11) NOT NULL,
  `id_simulacro` int(11) NOT NULL,
  `enunciado` text NOT NULL,
  `area` varchar(100) NOT NULL,
  `numero_pregunta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pruebas`
--

CREATE TABLE `pruebas` (
  `id_prueba` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `tipo` enum('ICFES','UNAL') NOT NULL,
  `anio` year(4) NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('Prueba','Publicado') NOT NULL DEFAULT 'Prueba',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `id_cuenta` int(11) NOT NULL,
  `id_pregunta` int(11) DEFAULT NULL,
  `tipo` enum('Pregunta','Sugerencia','Error') NOT NULL DEFAULT 'Sugerencia',
  `descripcion` text NOT NULL,
  `estado` enum('Pendiente','Visto','Resuelto') NOT NULL DEFAULT 'Pendiente',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_estudiante`
--

CREATE TABLE `respuestas_estudiante` (
  `id_respuesta` int(11) NOT NULL,
  `id_resultado` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `id_opcion` int(11) NOT NULL,
  `es_correcta` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultados`
--

CREATE TABLE `resultados` (
  `id_resultado` int(11) NOT NULL,
  `id_cuenta` int(11) NOT NULL,
  `id_prueba` int(11) NOT NULL,
  `puntaje` int(11) NOT NULL,
  `respuestas_correctas` int(11) NOT NULL DEFAULT 0,
  `respuestas_incorrectas` int(11) NOT NULL DEFAULT 0,
  `fecha_realizacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `simulacro`
--

CREATE TABLE `simulacro` (
  `id_simulacro` int(11) NOT NULL,
  `id_prueba` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `duracion_minutos` int(11) NOT NULL DEFAULT 270
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`id_cuenta`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_notificacion_cuenta` (`id_cuenta`);

--
-- Indices de la tabla `opciones_rta`
--
ALTER TABLE `opciones_rta`
  ADD PRIMARY KEY (`id_opcion`),
  ADD KEY `fk_opcion_pregunta` (`id_pregunta`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id_pregunta`),
  ADD KEY `fk_pregunta_simulacro` (`id_simulacro`);

--
-- Indices de la tabla `pruebas`
--
ALTER TABLE `pruebas`
  ADD PRIMARY KEY (`id_prueba`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reporte_cuenta` (`id_cuenta`),
  ADD KEY `id_pregunta` (`id_pregunta`);

--
-- Indices de la tabla `respuestas_estudiante`
--
ALTER TABLE `respuestas_estudiante`
  ADD PRIMARY KEY (`id_respuesta`),
  ADD KEY `fk_respuesta_resultado` (`id_resultado`),
  ADD KEY `fk_respuesta_pregunta` (`id_pregunta`),
  ADD KEY `fk_respuesta_opcion` (`id_opcion`);

--
-- Indices de la tabla `resultados`
--
ALTER TABLE `resultados`
  ADD PRIMARY KEY (`id_resultado`),
  ADD KEY `fk_resultado_cuenta` (`id_cuenta`),
  ADD KEY `fk_resultado_prueba` (`id_prueba`);

--
-- Indices de la tabla `simulacro`
--
ALTER TABLE `simulacro`
  ADD PRIMARY KEY (`id_simulacro`),
  ADD KEY `fk_simulacro_prueba` (`id_prueba`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  MODIFY `id_cuenta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `opciones_rta`
--
ALTER TABLE `opciones_rta`
  MODIFY `id_opcion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id_pregunta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pruebas`
--
ALTER TABLE `pruebas`
  MODIFY `id_prueba` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuestas_estudiante`
--
ALTER TABLE `respuestas_estudiante`
  MODIFY `id_respuesta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resultados`
--
ALTER TABLE `resultados`
  MODIFY `id_resultado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `simulacro`
--
ALTER TABLE `simulacro`
  MODIFY `id_simulacro` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificacion_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `opciones_rta`
--
ALTER TABLE `opciones_rta`
  ADD CONSTRAINT `fk_opcion_pregunta` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `fk_pregunta_simulacro` FOREIGN KEY (`id_simulacro`) REFERENCES `simulacro` (`id_simulacro`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `fk_reporte_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reporte_pregunta` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`) ON DELETE SET NULL;

--
-- Filtros para la tabla `respuestas_estudiante`
--
ALTER TABLE `respuestas_estudiante`
  ADD CONSTRAINT `fk_respuesta_opcion` FOREIGN KEY (`id_opcion`) REFERENCES `opciones_rta` (`id_opcion`),
  ADD CONSTRAINT `fk_respuesta_pregunta` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`),
  ADD CONSTRAINT `fk_respuesta_resultado` FOREIGN KEY (`id_resultado`) REFERENCES `resultados` (`id_resultado`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resultados`
--
ALTER TABLE `resultados`
  ADD CONSTRAINT `fk_resultado_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`),
  ADD CONSTRAINT `fk_resultado_prueba` FOREIGN KEY (`id_prueba`) REFERENCES `pruebas` (`id_prueba`);

--
-- Filtros para la tabla `simulacro`
--
ALTER TABLE `simulacro`
  ADD CONSTRAINT `fk_simulacro_prueba` FOREIGN KEY (`id_prueba`) REFERENCES `pruebas` (`id_prueba`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
