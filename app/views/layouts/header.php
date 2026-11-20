<?php
// app/views/layouts/header.php
// Cabecera común (head + navbar). Variables que la página puede definir antes de incluirla:
//   $tituloPagina  (string)  -> título de la pestaña
//   $hojasCss      (array)   -> hojas de estilo extra, relativas a public/assets/css/
$tituloPagina = $tituloPagina ?? 'SaberWeb';
$hojasCss     = $hojasCss ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($tituloPagina); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/footer.css">
<?php foreach ($hojasCss as $hoja): ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/<?php echo e($hoja); ?>">
<?php endforeach; ?>
</head>
<body>
<?php include ROOT_PATH . 'app/views/layouts/navbar.php'; ?>
