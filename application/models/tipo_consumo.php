<?php

class Tipo_consumo extends CI_Model {
    /**
     * Return all the rows
     * @param type $num
     * @param type $offset
     * @param type $where
     * @param string $order
     * @return type
     */
    function get_all($num = 10, $offset = 0, $where = "", $order = null,$select = null) {
        if ($order == null)
            $order = "id";
        if ($select != null)
            $this->db->select($select);
        $this->db->from('tipo_consumo');
        if ($where != "")
            $this->db->where($where);
        $this->db->where('deleted', 0);
        $this->db->order_by($order);
        $this->db->limit($num, $offset);

        return $this->db->get()->result_array();
    }
    
    
}