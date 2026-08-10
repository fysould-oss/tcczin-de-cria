<?php
require_once __DIR__ . '/functions.php';

if (!isset($pageTitle)) {
    $pageTitle = 'ConectaPrédio';
}
if (!isset($metaDescription)) {
    $metaDescription = 'Plataforma para solicitação e acompanhamento de serviços em condomínios.';
}
if (!isset($bodyClass)) {
    $bodyClass = '';
}

$basePath = getBasePath();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> | ConectaPrédio</title>
    <meta name="description" content="<?= sanitize($metaDescription) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css?v=20260810">
    <style>
        body { margin: 0; font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f8fc; color: #172433; line-height: 1.65; }
        .container { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }
        .site-header { position: sticky; top: 0; z-index: 30; background: rgba(255,255,255,0.96); backdrop-filter: blur(8px); border-bottom: 1px solid #e7eef6; }
        .button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 13px 20px; border-radius: 14px; font-weight: 700; text-decoration: none; }
        .button-primary { background: #1c5fcc; color: #fff; }
        .button-secondary { background: #edf4ff; color: #163d7a; }
        .hero { background: linear-gradient(180deg, #f6faff 0%, #ffffff 100%); padding: 92px 0 80px; }
        .service-card, .card, .stats-card, .panel-card, .auth-card, .form-card { background: #fff; border: 1px solid #dce5ef; border-radius: 24px; box-shadow: 0 22px 48px rgba(19, 53, 108, 0.08); padding: 24px; }
    </style>
</head>
<body class="<?= sanitize($bodyClass) ?>">
<?php include __DIR__ . '/navbar.php'; ?>
