<?php
/**
 * app/views/layouts/head.php
 * <head> comum a todas as páginas internas do SafraWise.
 *
 * Variáveis aceitas (todas opcionais):
 *   $pageTitle  string  — vira "SafraWise — {título}"
 *   $extraCss   array   — hrefs de CSS adicionais (ex.: paginacao_tabelas.css)
 *   $headExtra  string  — HTML solto antes de </head> (ex.: um <style> da página)
 *
 * Uso:
 *   <?php $pageTitle = 'Insumos'; include 'layouts/head.php'; ?>
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise<?= !empty($pageTitle) ? ' — ' . htmlspecialchars($pageTitle) : '' ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">

  <?php foreach ($extraCss ?? [] as $css): ?>
  <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
  <?php endforeach; ?>

  <?= $headExtra ?? '' ?>
</head>