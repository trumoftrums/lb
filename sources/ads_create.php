<?php
/**
 * ads create
 * Plugin for Wowonder
 * @author Pepe Galvan  - LdrMx
 */
$wo['plugin_page'] = 'ads_create';
if (Wo_IsLogged() === false) { header("Location: " . Wo_SeoLink('index.php?tab1=welcome')); exit(); }

$wo['description'] = $wo['config']['siteDesc'];
$wo['keywords']    = $wo['config']['siteKeywords'];
$wo['page']        = 'ads create';
$wo['title']       = $wo['lang']['plugin_ads_create_campaign'] . ' | ' . $wo['config']['siteTitle'];
$wo['content']     = Wo_LoadPage('plugins/ads_create');
?>