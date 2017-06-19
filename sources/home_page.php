<?php 
if ($wo['config']['blogs'] == 0) {
	header("Location: " . $wo['config']['site_url']);
    exit();
}
$wo['description'] = $wo['config']['siteDesc'];
$wo['keywords']    = $wo['config']['siteKeywords'];
$wo['page']        = 'home-page';
$wo['title']       = 'Home page';
$wo['content']     = Wo_LoadPage('blog/home-page');