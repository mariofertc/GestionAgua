<?php
require_once("report.php");
class Summary_categories extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
//		return array($this->lang->line('reports_category'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'));
//		return array($this->lang->line('reports_category'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'));
                return array($this->lang->line('reports_category'), $this->lang->line('reports_valor_cuota'), $this->lang->line('reports_cargo'), $this->lang->line('reports_total'), $this->lang->line('reports_consumo_medidor'));
	}
	
	public function getData(array $inputs)
	{
//		$this->db->select('category, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit');
		$this->db->select('nombre_tipo_consumo, sum(consumo_medidor) as consumo_medidor, sum(valor_cuota) as valor_cuota,  sum(cargo) as cargo, sum(total) as total');
		$this->db->from('sales_items_temp');
		//$this->db->join('items', 'sales_items_temp.item_id = items.item_id');
//                $this->db->join('consumo', 'sales_items_temp.consumo_id = consumo.id');
		$this->db->where('sale_date BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
//		$this->db->group_by('category');
//		$this->db->order_by('category');
		$this->db->group_by('nombre_tipo_consumo');
		$this->db->order_by('nombre_tipo_consumo');

		return $this->db->get()->result_array();		
	}
	
	public function getSummaryData(array $inputs)
	{
//		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit');
                $this->db->select('sum(consumo_medidor) as consumo_medidor, sum(valor_cuota) as valor_cuota,  sum(cargo) as cargo, sum(total) as total');
		$this->db->from('sales_items_temp');
//		$this->db->join('items', 'sales_items_temp.item_id = items.item_id');
//                $this->db->join('consumo', 'sales_items_temp.consumo_id = consumo.id');
		$this->db->where('sale_date BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');

		return $this->db->get()->row_array();
	}
}
?>