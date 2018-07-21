<?php
require_once("report.php");
class Summary_customers extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
//		return array($this->lang->line('reports_customer'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'));
            return array($this->lang->line('customers_ci'), $this->lang->line('reports_customer'), $this->lang->line('customers_account_number'), $this->lang->line('reports_type'), $this->lang->line('reports_valor_cuota'), $this->lang->line('reports_cargo'), $this->lang->line('reports_total'), $this->lang->line('reports_consumo_medidor'), $this->lang->line('reports_consumo_medidor_avg'));
	}
	
	public function getData(array $inputs)
	{
//		$this->db->select('CONCAT(first_name, " ",last_name) as customer, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax,sum(profit) as profit', false);
                $this->db->select('CONCAT(first_name," ",last_name) as customer, zip, account_number, sum(consumo_medidor) as consumo_medidor,avg(consumo_medidor) as promedio_consumo_medidor,  sum(valor_cuota) as valor_cuota,  sum(cargo) as cargo, sum(total) as total, sales_items_temp.payment_type, comment, tipo_consumo.nombre as tipo_consumo', false);
		$this->db->from('sales_items_temp');
		$this->db->join('customers', 'customers.person_id = sales_items_temp.customer_id');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->join('tipo_consumo', 'customers.id_tipo_consumo = tipo_consumo.id');
		$this->db->where('sale_date BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->group_by('customer_id');
		$this->db->order_by('last_name');

		return $this->db->get()->result_array();		
	}
	
	public function getSummaryData(array $inputs)
	{
//		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit');
		$this->db->select('sum(consumo_medidor), sum(valor_cuota) as valor_cuota, sum(cargo) as cargo, sum(total) as total');
		$this->db->from('sales_items_temp');
		$this->db->join('customers', 'customers.person_id = sales_items_temp.customer_id');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->where('sale_date BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');

		return $this->db->get()->row_array();
	}
}