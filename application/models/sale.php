<?php

class Sale extends CI_Model {

    public function get_info($sale_id) {
        $this->db->from('sales');
        $this->db->where('sale_id', $sale_id);
        return $this->db->get();
    }

    function exists($sale_id) {
        $this->db->from('sales');
        $this->db->where('sale_id', $sale_id);
        $query = $this->db->get();

        return ($query->num_rows() == 1);
    }

    function update($sale_data, $sale_id) {
        $this->db->where('sale_id', $sale_id);
        $success = $this->db->update('sales', $sale_data);

        return $success;
    }

    function save($items, $customer_id, $employee_id, $comment, $payments, $sale_id = false, $data = false) {
        if (count($items) == 0)
            return -1;

        //Alain Multiple payments
        //Build payment types string
        $payment_types = '';
        foreach ($payments as $payment_id => $payment) {
            //$payment_types=$payment_types.$payment['payment_type'].': '.to_currency($payment['payment_amount']).'<br>';
            $payment_types = $payment_types . $this->Payment->get_info($payment['payment_id'])->payment_type . ': ' . to_currency($payment['payment_amount']) . '<br>';
        }

        $sales_data = array(
            'sale_time' => date('Y-m-d H:i:s'),
            'customer_id' => $this->Customer->exists($customer_id) ? $customer_id : null,
            'employee_id' => $employee_id,
            //'payment_id'=>$payment['payment_id'],
            'payment_type' => $payment_types,
            'comment' => $comment,
                //'almacen_id'=>$data['almacen_id']
        );


        //Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->trans_start();

        $this->db->insert('sales', $sales_data);
        $sale_id = $this->db->insert_id();

        foreach ($payments as $payment_id => $payment) {
            $sales_payments_data = array
                (
                'sale_id' => $sale_id,
                'payment_id' => $payment['payment_id'],
                'payment_amount' => str_replace(',', '.', $payment['payment_amount'])
            );
            $this->db->insert('sales_payments', $sales_payments_data);
        }

        foreach ($items as $line => $item) {
//			$cur_item_info = $this->Item->get_info($item['id']);
            $cur_item_info = $this->consumo->get_info($item['id']);

            $sales_items_data = array
                (
                'sale_id' => $sale_id,
                'consumo_id' => $item['id'],
                'consumo_medidor' => $item['consumo_medidor'],
                'line' => $item['line'],
                'fecha_consumo' => $item['fecha_consumo'],
                'cargo' => $item['cargo'],
                'valor_cuota' => $item['valor_cuota'],
                'valor_a_pagar' => $item['valor_a_pagar'],
                'detalle_cargo' => $item['detalle_cargo'],
                'interes' => $item['interes_generado'],
                'tipo_consumo' => $item['tipo_consumo'],
            );

            if($item['tipo_consumo'] == "acometida" || $item['tipo_consumo'] == "medidor"){
                $sales_items_data['valor_a_pagar'] = $item['acometida'];
            }

            $this->db->insert('sales_items', $sales_items_data);

            //Update stock quantity
            $item_data = array('estado' => 'pagado');
            
            if($item['tipo_consumo'] == "acometida" || $item['tipo_consumo'] == "medidor"){
                //Get all acometidas y actualiza estado a pagado, solo si esta cubierto el total del valor de la acometida.
                //$valor_acometidas = $this->get_acometidas($item['id']) + $item['acometida'];
                $valor_acometidas = $item['acometida'];
                if($valor_acometidas >= $item['valor_a_pagar'])
                    $this->consumo->save($item_data, $item['id']);
            }else
                $this->consumo->save($item_data, $item['id']);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return -1;
        }

        return $sale_id;
    }

    function delete($sale_id) {
        //Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->trans_start();

        $this->db->delete('sales_payments', array('sale_id' => $sale_id));
        $this->db->delete('sales_items_taxes', array('sale_id' => $sale_id));
        $this->db->delete('sales_items', array('sale_id' => $sale_id));
        $this->db->delete('sales', array('sale_id' => $sale_id));

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    function get_sale_items($sale_id, $consumo_id = null) {
        $this->db->from('sales_items');
        $this->db->where('sale_id', $sale_id);
        if($consumo_id != null){
            $this->db->where('consumo_id', $consumo_id);
        }
        return $this->db->get();
    }
    /**
     * Obtiene los valores abonados por concepto de acometidas.
     * @param  [type] $consumo_id [description]
     * @return [type]          [description]
     */
    function get_acometidas($consumo_id) {
        $this->db->select_sum('valor_a_pagar','Valor')
        ->from('sales_items')
        ->where('consumo_id', $consumo_id)->group_start()
        ->where('tipo_consumo', 'acometida')
        ->or_where('tipo_consumo', 'medidor')
        ->group_end();;
        $query = $this->db->get();
        $result = $query->result();
        //print_r($this->db->last_query());
        return $result[0]->Valor;
    }

    function get_sale_payments_old($sale_id) {
        $this->db->from('sales_payments');
        $this->db->where('sale_id', $sale_id);
        return $this->db->get();
    }

    function get_sale_payments($sale_id) {
        $this->db->select('sales_payments.payment_amount, payments.payment_type, payments.payment_id');
        $this->db->from('payments');
        $this->db->join('sales_payments', 'sales_payments.payment_id = payments.payment_id');
        //$this->db->join('people', 'people.person_id = suppliers.person_id');
        $this->db->where('sales_payments.sale_id', $sale_id);
        return $this->db->get();
    }

    function get_customer($sale_id) {
        $this->db->from('sales');
        $this->db->where('sale_id', $sale_id);
        return $this->Customer->get_info($this->db->get()->row()->customer_id);
    }

    //We create a temp table that allows us to do easy report/sales queries
    public function create_sales_items_temp_table() {
        if ($this->db->table_exists('sales_items_temp')) {
            //Borra datos previos
            $this->db->query("drop table " . $this->db->dbprefix('sales_items_temp'));
        }
        $this->db->query("CREATE TABLE if not exists " . $this->db->dbprefix('sales_items_temp') . "
		(SELECT date(sale_time) as sale_date, consumo.id as consumo_id, " . $this->db->dbprefix('sales_items') . ".sale_id, comment, payment_type, customer_id, employee_id, 
		" . $this->db->dbprefix('consumo') . 
        ".id, consumo.consumo_medidor as consumo_medidor, 0 as subtotal, registro_medidor, consumo.fecha_consumo, consumo.cargo, consumo.detalle_cargo, consumo.interes_generado as interes, consumo.valor_cuota as valor_cuota, 0 as profit, ".
        "(sales_items.valor_a_pagar+ifnull(consumo.cargo,0)) as total, ".
        // "(sales_items.valor_a_pagar+ifnull(consumo.interes_generado,0)+ifnull(consumo.cargo,0)) as total, ".
        "tipo_consumo.nombre as nombre_tipo_consumo FROM " . 
        // ".id, consumo.consumo_medidor as consumo_medidor, 0 as subtotal, registro_medidor, consumo.fecha_consumo, consumo.cargo, consumo.detalle_cargo, consumo.interes_generado as interes, consumo.valor_cuota as valor_cuota, 0 as profit, (consumo.valor_a_pagar+ifnull(consumo.interes_generado,0)+ifnull(consumo.cargo,0)) as total, tipo_consumo.nombre as nombre_tipo_consumo FROM " . 
        $this->db->dbprefix('sales_items') . "
		INNER JOIN " . $this->db->dbprefix('sales') . "
		ON  " . $this->db->dbprefix('sales_items') . '.sale_id=' . $this->db->dbprefix('sales') . '.sale_id' . "
		INNER JOIN " . $this->db->dbprefix('consumo') . " ON  " . $this->db->dbprefix('sales_items') . '.consumo_id=' . $this->db->dbprefix('consumo') . '.id ' .
                
                "LEFT OUTER JOIN " . $this->db->dbprefix('cuotas') . " ON  " . $this->db->dbprefix('cuotas') . '.id=' . $this->db->dbprefix('consumo') . '.id_cuota ' .
                "LEFT OUTER JOIN " . $this->db->dbprefix('tipo_consumo') . " ON  " . $this->db->dbprefix('tipo_consumo') . '.id=' . $this->db->dbprefix('cuotas') . '.id_tipo_consumo' .
//		LEFT OUTER JOIN ".$this->db->dbprefix('sales_items_taxes')." ON  "
//		.$this->db->dbprefix('sales_items').'.sale_id='.$this->db->dbprefix('sales_items_taxes').'.sale_id '.
//		.$this->db->dbprefix('sales_items').'.item_id='.$this->db->dbprefix('sales_items_taxes').'.item_id'." and "
//		.$this->db->dbprefix('sales_items').'.line='.$this->db->dbprefix('sales_items_taxes').'.line'."
//		.$this->db->dbprefix('sales_items').'.line='.$this->db->dbprefix('sales_items_taxes').'.line'."
                " GROUP BY sale_id, consumo_id, line)");

        //Update null item_tax_percents to be 0 instead of null
        //$this->db->where('item_tax_percent IS NULL');
        //$this->db->update('sales_items_temp', array('item_tax_percent' => 0));
        //Update null tax to be 0 instead of null
        //$this->db->where('tax IS NULL');
        //$this->db->update('sales_items_temp', array('tax' => 0));
        //Update null subtotals to be equal to the total as these don't have tax
        $this->db->query('UPDATE ' . $this->db->dbprefix('sales_items_temp') . ' SET total=subtotal WHERE total IS NULL');
    }

}
