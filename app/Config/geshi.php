<?php
/*
 * Setup for plugin cakephp_geshi
 */

$geshi->set_header_type(GESHI_HEADER_DIV);
$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
$geshi->set_line_style('background: white;');
// $geshi->enable_classes();
$geshi->set_tab_width(2);
$geshi->set_code_style('line-height: inherit;', true);
