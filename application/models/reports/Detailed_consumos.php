<?php
require_once("report.php");
class Detailed_consumos extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
        return array('summary' => array($this->lang->line('reports_receiving_id'), $this->lang->line('reports_date'), $this->lang->line('reports_ci'), $this->lang->line('reports_meses_mora'), $this->lang->line('reports_sold_to'), $this->lang->line('reports_consumo_medidor'), $this->lang->line('reports_valor_cuota')),
            'details' => array($this->lang->line('reports_name'), $this->lang->line('reports_category'), $this->lang->line('reports_serial_number'), $this->lang->line('reports_description'), $this->lang->line('reports_quantity_purchased'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'), $this->lang->line('reports_discount'))
        );
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('c.id as id, c.fecha_consumo, count(*) as items_purchased, CONCAT(customer.first_name," ",customer.last_name) as customer_name, sum(consumo_medidor) as consumo_medidor,  sum(valor_cuota) as valor_cuota,  sum(cargo) as cargo,sum(interes_generado) as interes, customer.zip as ci', false);
		$this->db->from('consumo as c');
		$this->db->join('people as customer', 'c.id_cliente = customer.person_id', 'left');
		$this->db->where('fecha_consumo BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
        $this->db->where("estado='generado'");
        $this->db->where("deleted=0");
		$this->db->group_by('c.id');
		//$this->db->order_by('c.id');
		$this->db->order_by('ci, c.fecha_consumo');


		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		
		return $data;
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(consumo_medidor) as consumo_medidor, sum(valor_a_pagar) as total');
		$this->db->from('consumo');
		$this->db->where('fecha_consumo BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where("estado='generado'");
		return $this->db->get()->row_array();
	}
}