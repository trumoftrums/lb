<?php
/* admin plugins
 * Plugins for Wowonder
 * @author Pepe Galvan - LdrMx
 */

$wo['plugin_page'] = 'admin_plugins';

if (Wo_IsAdmin($wo['user']['user_id']) === false) {
    header("Location: " . $wo['config']['site_url']);
    exit();
}

$wo['description'] = $wo['config']['siteDesc'];
$wo['keywords']    = $wo['config']['siteKeywords'];
$wo['page']        = 'admin';
$wo['title']       = $wo['lang']['admin_area'] . '/ plugins | ' . $wo['config']['siteTitle'];
$wo['content']     = Wo_LoadPage('plugins/admin/content');

?>