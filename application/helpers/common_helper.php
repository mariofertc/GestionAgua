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

function line($text, $opt = NULL) {
    $CI = & get_instance();
    return $CI->lang->line($text, $opt);
}

function config($var) {
    $CI = & get_instance();
    //echo $CI->config->item($var);
    //echo($var);
    return $CI->config->item($var);
}

/**
 * 
 * @param type $result
 * @param type $options
 * @param type $encode_json
 * @return type
 * @example array_to_htmlcombo($cll_data, array('blank_text' => 'Select Option', 'id' => 'ID_TABLA', 'name' => array('NOMBRE1', 'NOMBRE2'), 'separador'=>' | ')) Llamada a la función
 */
function array_to_htmlcombo($result, $options, $encode_json = false) {
    extract($options);
    $result_array = array();
    if ($blank_text !== null)
        $result_array[] = $blank_text;
    foreach ($result as $r) {
        $texto = array();
        if (is_array($name)) {
            foreach ($name as $n)
                $texto[] = $r[$n];
        }else
            $texto[] = $r[$name];
            
        $result_array[$r[$id]] = implode(isset($separador) ? $separador : " ", $texto);
    }
    return $result_array;
}

/* End of file common.php */
/* Location: ./helpers/common.php */