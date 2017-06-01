<?php 
if ($wo['loggedin'] == false) {
  header("Location: " . $wo['config']['site_url']);
  exit();
}
if (Wo_CanBlog() == false) {
	header("Location: " . $wo['config']['site_url']);
    exit();
}

$wo['description'] = '';
$wo['keywords']    = '';
$wo['page']        = 'My-Blogs';
$wo['title']       = 'My Blogs';
$wo['content']     = Wo_LoadPage('blog/my-blogs');