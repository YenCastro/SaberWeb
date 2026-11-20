<?php
// app/views/admin/preguntas.php
//   sin parámetros              -> elegir la sesión cuyas preguntas se quieren gestionar
//   ?id_simulacro=ID            -> lista de preguntas + formulario para crear
//   ?id_simulacro=ID&editar=ID  -> el mismo formulario cargado con una pregunta existente
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Administrador');
require_once __DIR__ . '/../../models/Prueba.php';
require_once __DIR__ . '/../../models/Simulacro.php';
require_once __DIR__ . '/../../models/Pregunta.php';
require_once __DIR__ . '/../../models/Opcion.php';

$tituloPagina = 'Preguntas - Administración SaberWeb';
$hojasCss     = ['admin/admin.css'];
$ctrl         = BASE_URL . 'app/controllers/AdminController.php';
$idSimulacro  = (int)($_GET['id_simulacro'] ?? 0);

// ---------------- Vista 1: elegir sesión ----------------
if ($idSimulacro === 0) {
    $simulacroModel = new Simulacro();
    $pruebas = (new Prueba())->listar();
    include ROOT_PATH . 'app/views/layouts/header.php';
    ?>
    <main class="admin-main">
        <div class="admin-titulo">
            <div>
                <h1>Preguntas</h1>
                <p>Elige la sesión a la que quieres agregar o editar preguntas.</p>
            </div>
        </div>
        <?php include __DIR__ . '/_mensajes.php'; ?>

        <?php if (count($pruebas) === 0): ?>
            <div class="card vacio"><i class="fa-solid fa-folder-open"></i>
                Primero crea un simulacro. <br><br>
                <a class="btn btn-azul" href="<?php echo BASE_URL; ?>app/views/admin/crear_simulacro.php">Crear simulacro</a></div>
        <?php endif; ?>

        <?php foreach ($pruebas as $p): ?>
            <div class="card">
                <h2><?php echo e($p['titulo']); ?>
                    <span class="badge badge-<?php echo strtolower(e($p['tipo'])); ?>"><?php echo e($p['tipo']); ?></span>
                </h2>
                <?php foreach ($simulacroModel->listarPorPrueba($p['id_prueba']) as $s): ?>
                    <div class="sesion-item">
                        <div>
                            <strong><?php echo e($s['titulo']); ?></strong>
                            <div class="meta"><span><?php echo (int)$s['num_preguntas']; ?> preguntas</span>
                                <span><i class="fa-regular fa-clock"></i> <?php echo (int)$s['duracion_minutos']; ?> min</span></div>
                        </div>
                        <a class="btn btn-azul btn-pequeno" href="<?php echo BASE_URL; ?>app/views/admin/preguntas.php?id_simulacro=<?php echo (int)$s['id_simulacro']; ?>">
                            Gestionar preguntas</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </main>
    <?php
    include ROOT_PATH . 'app/views/layouts/footer.php';
    echo '<script src="' . BASE_URL . 'public/assets/js/admin/admin.js"></script></body></html>';
    exit();
}

// ---------------- Vista 2: preguntas de una sesión ----------------
$sesion = (new Simulacro())->obtener($idSimulacro);
if (!$sesion) {
    header("Location: " . BASE_URL . "app/views/admin/preguntas.php?error=no_encontrado");
    exit();
}

$preguntaModel = new Pregunta();
$preguntas     = $preguntaModel->listarPorSimulacro($idSimulacro);
$opcionesPorPregunta = (new Opcion())->listarPorPreguntas(array_column($preguntas, 'id_pregunta'));

// ¿Se está editando una pregunta?
$editando = null;
$opcEdit  = [];
if (!empty($_GET['editar'])) {
    $candidata = $preguntaModel->obtener((int)$_GET['editar']);
    if ($candidata && (int)$candidata['id_simulacro'] === $idSimulacro) {
        $editando = $candidata;
        $opcEdit  = (new Opcion())->listarPorPregunta($candidata['id_pregunta']);
    }
}
$correctaEdit = '';
foreach ($opcEdit as $letra => $o) {
    if ((int)$o['es_correcta'] === 1) $correctaEdit = $letra;
}

include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="admin-main">
    <p class="migas">
        <a href="<?php echo BASE_URL; ?>app/views/admin/simulacros.php">Simulacros</a> /
        <a href="<?php echo BASE_URL; ?>app/views/admin/editar_simulacro.php?id=<?php echo (int)$sesion['id_prueba']; ?>"><?php echo e($sesion['prueba_titulo']); ?></a> /
        <?php echo e($sesion['titulo']); ?>
    </p>
    <div class="admin-titulo">
        <div>
            <h1><?php echo e($sesion['titulo']); ?></h1>
            <p><?php echo count($preguntas); ?> preguntas · <?php echo (int)$sesion['duracion_minutos']; ?> minutos</p>
        </div>
        <a class="btn btn-borde" href="<?php echo BASE_URL; ?>app/views/admin/preguntas.php">
            <i class="fa-solid fa-arrow-left"></i> Otras sesiones</a>
    </div>

    <?php include __DIR__ . '/_mensajes.php'; ?>

    <div class="pregunta-layout">

        <!-- Lista de preguntas -->
        <section>
            <?php if (count($preguntas) === 0): ?>
                <div class="card vacio"><i class="fa-solid fa-circle-question"></i>
                    Esta sesión aún no tiene preguntas. Crea la primera con el formulario.</div>
            <?php endif; ?>

            <?php foreach ($preguntas as $q):
                $esta = $editando && (int)$editando['id_pregunta'] === (int)$q['id_pregunta']; ?>
                <article class="pregunta-item <?php echo $esta ? 'editando' : ''; ?>">
                    <div class="cab">
                        <div class="meta">
                            <strong>Pregunta <?php echo (int)$q['numero_pregunta']; ?></strong>
                            <span class="badge badge-area"><?php echo e($q['area']); ?></span>
                        </div>
                        <div class="fila-botones">
                            <a class="btn btn-borde btn-pequeno" href="?id_simulacro=<?php echo $idSimulacro; ?>&editar=<?php echo (int)$q['id_pregunta']; ?>">
                                <i class="fa-solid fa-pen"></i> Editar</a>
                            <form method="POST" action="<?php echo $ctrl; ?>?action=eliminarPregunta"
                                  data-confirm="¿Eliminar la pregunta <?php echo (int)$q['numero_pregunta']; ?>?">
                                <?php echo csrf_campo(); ?>
                                <input type="hidden" name="id_pregunta" value="<?php echo (int)$q['id_pregunta']; ?>">
                                <button type="submit" class="btn btn-peligro btn-pequeno"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    <p class="enunciado"><?php echo e($q['enunciado']); ?></p>
                    <ul>
                        <?php foreach ($opcionesPorPregunta[$q['id_pregunta']] ?? [] as $o): ?>
                            <li class="<?php echo (int)$o['es_correcta'] === 1 ? 'correcta' : ''; ?>">
                                <?php echo e($o['letra']); ?>. <?php echo e($o['texto']); ?>
                                <?php echo (int)$o['es_correcta'] === 1 ? ' ✓' : ''; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- Formulario crear / editar -->
        <section class="card">
            <h2><?php echo $editando ? 'Editar pregunta ' . (int)$editando['numero_pregunta'] : 'Nueva pregunta'; ?></h2>
            <form method="POST" action="<?php echo $ctrl; ?>?action=guardarPregunta">
                <?php echo csrf_campo(); ?>
                <input type="hidden" name="id_simulacro" value="<?php echo $idSimulacro; ?>">
                <input type="hidden" name="id_pregunta" value="<?php echo $editando ? (int)$editando['id_pregunta'] : 0; ?>">

                <div class="form-grupo">
                    <label for="area">Área</label>
                    <input type="text" id="area" name="area" maxlength="100" list="areas" required
                           value="<?php echo e($editando['area'] ?? ''); ?>" placeholder="Ej: Matemáticas">
                    <datalist id="areas">
                        <option value="Lectura Crítica"><option value="Matemáticas"><option value="Sociales y Ciudadanas">
                        <option value="Ciencias Naturales"><option value="Inglés"><option value="Análisis Textual">
                        <option value="Análisis de Imagen"><option value="Ciencias Sociales">
                    </datalist>
                    <span class="ayuda">Puedes escoger una sugerida o escribir otra.</span>
                </div>

                <div class="form-grupo">
                    <label for="enunciado">Enunciado</label>
                    <textarea id="enunciado" name="enunciado" maxlength="5000" required><?php echo e($editando['enunciado'] ?? ''); ?></textarea>
                </div>

                <label style="font-weight:600;font-size:.9rem;color:var(--azul-oscuro);">Opciones de respuesta</label>
                <p class="ayuda" style="margin-bottom:10px;">Obligatorias A y B. Deja vacías las que no uses y marca la correcta.</p>
                <?php foreach (Pregunta::LETRAS as $letra): ?>
                    <div class="opcion-fila">
                        <span class="opcion-letra"><?php echo $letra; ?></span>
                        <input type="text" name="opcion_<?php echo $letra; ?>" maxlength="1000"
                               value="<?php echo e($opcEdit[$letra]['texto'] ?? ''); ?>"
                               <?php echo in_array($letra, ['A', 'B']) ? 'required' : ''; ?> aria-label="Opción <?php echo $letra; ?>">
                        <label class="opcion-correcta">
                            <input type="radio" name="correcta" value="<?php echo $letra; ?>" required
                                   <?php echo $correctaEdit === $letra ? 'checked' : ''; ?>> Correcta
                        </label>
                    </div>
                <?php endforeach; ?>

                <div class="fila-botones" style="margin-top:14px;">
                    <button type="submit" class="btn btn-azul"><i class="fa-solid fa-floppy-disk"></i>
                        <?php echo $editando ? 'Guardar cambios' : 'Agregar pregunta'; ?></button>
                    <?php if ($editando): ?>
                        <a class="btn btn-borde" href="?id_simulacro=<?php echo $idSimulacro; ?>">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>
    </div>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>public/assets/js/admin/admin.js"></script>
</body>
</html>
