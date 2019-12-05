<?php

require_once("report.php");

class Summary_consumos extends Report {

    function __construct() {
        parent::__construct();
    }

    public function getDataColumns() {
        return array($this->lang->line('reports_ci'), $this->lang->line('reports_customer'), $this->lang->line('reports_meses_mora'), $this->lang->line('reports_consumo_medidor'), $this->lang->line('reports_valor_cuota'), $this->lang->line('reports_total'));
    }

    public function getData(array $inputs) {
//        $this->db->select('sale_date, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax,sum(profit) as profit');
        $this->db->select('id_cliente, zip as ci, CONCAT(customer.first_name," ",customer.last_name) as customer_name,  count(id) as meses,sum(consumo_medidor) as consumo_medidor, sum(valor_cuota) as valor_cuota,  sum(cargo) as cargo,sum(interes_generado) as interes, sum(valor_a_pagar) as total');
        $this->db->from('consumo c');
        $this->db->join('people as customer', 'c.id_cliente = customer.person_id', 'left');
        //$this->db->having('fecha_consumo BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
        $this->db->where('fecha_consumo BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
        $this->db->where('estado','generado');
		$this->db->where("deleted=0");
        $this->db->group_by('id_cliente');
        $this->db->order_by('id_cliente');
        return $this->db->get()->result_array();
    }

    public function getSummaryData(array $inputs) {
//        $this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax,sum(profit) as profit');
        $this->db->select('sum(consumo_medidor) as consumo_medidor, sum(valor_cuota) as valor_cuota, sum(cargo) as cargo, sum(interes_generado) as interes, sum(valor_a_pagar) as total');
        $this->db->from('consumo');
        $this->db->where('fecha_consumo BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
        $this->db->where('estado','generado');
        return $this->db->get()->row_array();
    }

    /*public function getAlmacenes(array $inputs) {
        $this->db->select('sale_date, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax,sum(profit) as profit, almacen');
        $this->db->from('sales_items_temp');
        $this->db->group_by('sale_date', 'almacen');
        $this->db->having('sale_date BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
        $this->db->order_by('almacen', 'sale_date');
        return $this->db->get()->result_array();
    }

    public function getSummaryAlmacenes(array $inputs) {
        $this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax,sum(profit) as profit, almacen');
        $this->db->from('sales_items_temp');
        $this->db->group_by('almacen');
        $this->db->where('sale_date BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
        $this->db->order_by('almacen');
        return $this->db->get()->result_array();
    }*/

}
