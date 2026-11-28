<?php
// app/views/estudiante/reportar.php
//   sin parámetros                          -> enviar una sugerencia general
//   ?id_pregunta=ID[&id_resultado=ID]       -> reportar un error o una duda de una pregunta que ya presentó
// Abajo siempre aparece la lista de reportes que ha enviado.
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Estudiante');
require_once __DIR__ . '/../../models/Reporte.php';
require_once __DIR__ . '/../../models/Resultado.php';

$reporteModel = new Reporte();
$idCuenta     = (int)$_SESSION['usuario_id'];

$idPregunta = (int)($_GET['id_pregunta'] ?? 0);
$pregunta   = null;
if ($idPregunta) {
    $pregunta = $reporteModel->preguntaParaReporte($idCuenta, $idPregunta);
    if (!$pregunta) {
        header("Location: " . BASE_URL . "app/views/estudiante/reportar.php?error=pregunta_invalida");
        exit();
    }
}

// Enlace para volver al resultado (solo si ese intento es suyo)
$idResultado = (int)($_GET['id_resultado'] ?? 0);
if ($idResultado && !(new Resultado())->obtener($idResultado, $idCuenta)) {
    $idResultado = 0;
}

$misReportes = $reporteModel->listarPorCuenta($idCuenta);

$textosOk = ['enviado' => '¡Gracias! Recibimos tu mensaje. Cuando lo revisemos, te avisaremos en tus notificaciones.'];
$textosError = [
    'solicitud_invalida'  => 'La solicitud no es válida. Intenta de nuevo.',
    'pregunta_invalida'   => 'No puedes reportar esa pregunta. Solo puedes reportar preguntas de simulacros que ya terminaste.',
    'descripcion_invalida' => 'Cuéntanos qué pasó con al menos 10 caracteres (máximo 1000).',
    'limite_diario'       => 'Llegaste al máximo de ' . Reporte::MAX_POR_DIA . ' mensajes por día. Inténtalo mañana.',
    'duplicado'           => 'Ya enviaste un reporte pendiente sobre esta pregunta. Espera a que lo revisemos.',
    'no_se_pudo_guardar'  => 'No se pudo enviar. Intenta de nuevo.',
];
$mensaje = $_GET['mensaje'] ?? '';
$error   = $_GET['error'] ?? '';

$etiquetaTipo = ['Error' => 'Error en una pregunta', 'Pregunta' => 'Duda sobre una pregunta', 'Sugerencia' => 'Sugerencia'];

$tituloPagina = 'Reportes y sugerencias - SaberWeb';
$hojasCss     = ['estudiante/reportar.css'];
include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="est-main">
    <?php if ($idResultado): ?>
        <p class="migas"><a href="<?php echo BASE_URL; ?>app/views/estudiante/resultado.php?id=<?php echo $idResultado; ?>">← Volver a mi resultado</a></p>
    <?php endif; ?>

    <div class="admin-titulo">
        <div>
            <h1><?php echo $pregunta ? 'Reportar una pregunta' : 'Sugerencias y reportes'; ?></h1>
            <p><?php echo $pregunta
                ? 'Cuéntanos qué viste en esta pregunta para que podamos revisarla.'
                : 'Cuéntanos cómo mejorar SaberWeb. Si encontraste un error en una pregunta, repórtalo desde la revisión de tu resultado.'; ?></p>
        </div>
    </div>

    <?php if ($mensaje !== '' && isset($textosOk[$mensaje])): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo e($textosOk[$mensaje]); ?></div>
    <?php endif; ?>
    <?php if ($error !== '' && isset($textosError[$error])): ?>
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($textosError[$error]); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>app/controllers/ReporteController.php?action=crear" class="card" id="form-reporte">
        <?php echo csrf_campo(); ?>
        <input type="hidden" name="id_resultado" value="<?php echo $idResultado; ?>">

        <?php if ($pregunta): ?>
            <input type="hidden" name="id_pregunta" value="<?php echo (int)$pregunta['id_pregunta']; ?>">
            <div class="pregunta-ref">
                <div class="meta">
                    <strong>Pregunta <?php echo (int)$pregunta['numero_pregunta']; ?></strong>
                    <span class="badge badge-area"><?php echo e($pregunta['area']); ?></span>
                    <span><?php echo e($pregunta['prueba_titulo']); ?> · <?php echo e($pregunta['sesion_titulo']); ?></span>
                </div>
                <p><?php echo e($pregunta['enunciado']); ?></p>
            </div>

            <div class="form-grupo">
                <label>¿Qué quieres hacer?</label>
                <div class="tipo-opciones">
                    <label class="tipo-opcion"><input type="radio" name="tipo" value="Error" checked>
                        <span><i class="fa-solid fa-triangle-exclamation"></i> Reportar un error<small>El enunciado, las opciones o la respuesta correcta están mal.</small></span></label>
                    <label class="tipo-opcion"><input type="radio" name="tipo" value="Pregunta">
                        <span><i class="fa-solid fa-circle-question"></i> Tengo una duda<small>No entiendo por qué esta es la respuesta correcta.</small></span></label>
                </div>
            </div>
        <?php else: ?>
            <input type="hidden" name="tipo" value="Sugerencia">
        <?php endif; ?>

        <div class="form-grupo">
            <label for="descripcion"><?php echo $pregunta ? 'Cuéntanos qué pasó' : 'Tu sugerencia'; ?></label>
            <textarea id="descripcion" name="descripcion" minlength="10" maxlength="1000" required
                      placeholder="<?php echo $pregunta ? 'Ej: La opción B y la C dicen lo mismo…' : 'Ej: Me gustaría poder pausar el examen…'; ?>"></textarea>
            <span class="ayuda"><span id="contador">0</span> / 1000 caracteres (mínimo 10)</span>
        </div>

        <button type="submit" class="btn btn-azul" id="btn-enviar"><i class="fa-solid fa-paper-plane"></i> Enviar</button>
    </form>

    <div class="card">
        <h2>Mis envíos</h2>
        <?php if (count($misReportes) === 0): ?>
            <div class="vacio"><i class="fa-regular fa-comment-dots"></i>Todavía no has enviado nada.</div>
        <?php endif; ?>
        <?php foreach ($misReportes as $r): ?>
            <div class="envio-fila">
                <div class="envio-cab">
                    <span class="badge badge-<?php echo strtolower(e($r['tipo'])); ?>"><?php echo e($etiquetaTipo[$r['tipo']]); ?></span>
                    <span class="badge badge-<?php echo strtolower(e($r['estado'])); ?>"><?php echo e($r['estado']); ?></span>
                    <span class="ayuda"><?php echo e(date('d/m/Y H:i', strtotime($r['fecha']))); ?></span>
                </div>
                <?php if ($r['id_pregunta'] !== null): ?>
                    <div class="ayuda">Pregunta <?php echo (int)$r['numero_pregunta']; ?> · <?php echo e($r['area']); ?> · <?php echo e($r['prueba_titulo']); ?></div>
                <?php elseif ($r['tipo'] !== 'Sugerencia'): ?>
                    <div class="ayuda">La pregunta ya no existe.</div>
                <?php endif; ?>
                <p><?php echo e($r['descripcion']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>public/assets/js/estudiante/reportar.js"></script>
</body>
</html>
