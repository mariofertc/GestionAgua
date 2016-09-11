<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
  |==========================================================
  | Language
  |==========================================================
  |
 */
function host() {
    return $_SERVER['HTTP_HOST'];
}

function line($text,$opt = NULL) {
    $CI = & get_instance();
    return $CI->lang->line($text, $opt);
}

function config($var){
    $CI = & get_instance();
    return $CI->config->item($var);
}

/* End of file common.php */
/* Location: ./helpers/common.php */