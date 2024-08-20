<?php
// Base URL for the web server

// default web url destination
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

// current web url destination
// $web = "http://your_website.com";
?>