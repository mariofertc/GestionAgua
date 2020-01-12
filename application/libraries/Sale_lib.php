<?php

class Sale_lib {

    var $CI;

    function __construct() {
        $this->CI = & get_instance();
    }

    function get_cart() {
        if (!$this->CI->session->userdata('cart'))
            $this->set_cart(array());

        return $this->CI->session->userdata('cart');
    }

    function set_cart($cart_data) {
        $this->CI->session->set_userdata('cart', $cart_data);
    }

    //Alain Multiple Payments
    function get_payments() {
        if (!$this->CI->session->userdata('payments'))
            $this->set_payments(array());

        return $this->CI->session->userdata('payments');
    }

    //Alain Multiple Payments
    function set_payments($payments_data) {
        $this->CI->session->set_userdata('payments', $payments_data);
    }

    //Alain Multiple Payments
    function add_payment($payment_id, $payment_amount, $payment_type) {
        $payments = $this->get_payments();
        $payment = array($payment_id =>
            array(
                'payment_id' => $payment_id,
                'payment_type' => $payment_type,
                'payment_amount' => $payment_amount
            )
        );

        //payment_method already exists, add to payment_amount
        if (isset($payments[$payment_id])) {
            $payments[$payment_id]['payment_amount'] += $payment_amount;
        } else {
            //add to existing array
            $payments += $payment;
        }

        $this->set_payments($payments);
        return true;
    }

    //Alain Multiple Payments
    function edit_payment($payment_id, $payment_amount) {
        $payments = $this->get_payments();
        if (isset($payments[$payment_id])) {
            $payments[$payment_id]['payment_type'] = $payment_id;
            $payments[$payment_id]['payment_amount'] = $payment_amount;
            $this->set_payments($payment_id);
        }
        return false;
    }

    //Alain Multiple Payments
    function delete_payment($payment_id) {
        $payments = $this->get_payments();
        unset($payments[$payment_id]);
        $this->set_payments($payments);
    }

    //Alain Multiple Payments
    function empty_payments() {
        $this->CI->session->unset_userdata('payments');
    }

    //Alain Multiple Payments
    function get_payments_total() {
        $subtotal = 0;
        foreach ($this->get_payments() as $payments) {
            $subtotal += $payments['payment_amount'];
        }
        return to_currency_no_money($subtotal);
    }

    //Alain Multiple Payments
    function get_amount_due() {
        $amount_due = 0;
        $payment_total = $this->get_payments_total();
        $sales_total = $this->get_total();
        $amount_due = to_currency_no_money($sales_total - $payment_total);
        return $amount_due;
    }

    function get_customer() {
        if (!$this->CI->session->userdata('customer'))
            $this->set_customer(-1);

        return $this->CI->session->userdata('customer');
    }

    function set_customer($customer_id) {
        $this->CI->session->set_userdata('customer', $customer_id);
    }

    function get_mode() {
        if (!$this->CI->session->userdata('sale_mode'))
            $this->set_mode('sale');

        return $this->CI->session->userdata('sale_mode');
    }

    function set_mode($mode) {
        $this->CI->session->set_userdata('sale_mode', $mode);
    }

    //Para que venda en un almacen determinado
    function get_almacen() {
        if (!$this->CI->session->userdata('almacen_mode'))
            $this->set_almacen('-1');

        return $this->CI->session->userdata('almacen_mode');
    }

    function set_almacen($almacen) {
        $this->CI->session->set_userdata('almacen_mode', $almacen);
    }

//add_item($row->consumo_id,$row->consumo_medidor,$row->valor_a_pagar,$row->fecha_consumo,$row->valor_cuota,$row->cargo,$row->detalle_cargo)
    function add_item($customer_id, $consumo_id = null, $sale_id = null) {
        //make sure item exists
//                var_dump($customer_id);
        if (!$this->CI->Customer->exists($customer_id) && is_null($consumo_id)) {
            //try to get item id given an account_number
            $test = $customer_id = $this->CI->Customer->get_customer_id($customer_id);

            if (!$customer_id)
                return false;
        }
        //Get all items in the cart so far...

        if (is_null($consumo_id)) {
            $items = $this->CI->consumo->get_all(100, 0, array('id_cliente' => $customer_id, 'estado' => 'generado'));
            for ($idx = 0; $idx < count($items); $idx++) {
                $items[$idx]['fecha_consumo'] = explode(" ", $items[$idx]['fecha_consumo'])[0];
                if($items[$idx]['tipo_consumo'] == "acometida" || $items[$idx]['tipo_consumo'] == "medidor"){
                    $valor_acometidas =$this->CI->Sale->get_acometidas($items[$idx]['id']);
                    //Discriminar por line.
                    $items[$idx]['valor_a_pagar'] = $items[$idx]['valor_a_pagar'] - $valor_acometidas;

                    $items[$idx]['acometida'] = $items[$idx]['valor_a_pagar'];
                }
                //echo $items[$idx]['interes_generado'];
                //var_dump($items[$idx]['interes_generado']);
                if ($items[$idx]['interes_generado'] == null) {
                    //Vemos si es mayor de dos meses para aplicar intereses.
                    $d1 = new DateTime();
                    $d2 = new DateTime($items[$idx]['fecha_consumo']);
                    $meses_morosidad = $d1->diff($d2)->m;
                    if ($d1 > $d2 && $meses_morosidad >= 2) {
                        //var_dump(); // int(4)
                        $interes = $this->CI->Appconfig->get('interest');
                        $items[$idx]['interes_generado'] = ($interes / 100 / 12) * $items[$idx]['valor_cuota'] * $meses_morosidad;
                        $items[$idx]['valor_a_pagar'] = $items[$idx]['valor_a_pagar'] + ($interes / 100 / 12) * $items[$idx]['valor_cuota'] * $meses_morosidad;
                    }
                } else {
                    $items[$idx]['valor_a_pagar'] = $items[$idx]['valor_a_pagar'] + $items[$idx]['interes_generado'];
                }

                $items[$idx]['line'] = $idx;
            }

            if (count($items) > 0)
                $this->set_customer($customer_id);
            $this->set_cart($items);
            return true;
        }
        //Aca no llega
        $items = $this->get_cart();
        //We need to loop through all items in the cart.
        //If the item is already there, get it's key($updatekey).
        //We also need to get the next key that we are going to use in case we need to add the
        //item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.
        $maxkey = 0;                       //Highest key so far
        $itemalreadyinsale = FALSE;        //We did not find the item yet.
        $insertkey = 0;                    //Key to use for new entry.
        $updatekey = 0;                    //Key to use to update(quantity)

        foreach ($items as $item) {
            //We primed the loop so maxkey is 0 the first time.
            //Also, we have stored the key in the element itself so we can compare.

            if ($maxkey <= $item['line']) {
                $maxkey = $item['line'];
            }

            if ($item['id'] == $consumo_id) {
                $itemalreadyinsale = TRUE;
                $updatekey = $item['line'];
            }
        }

        $insertkey = $maxkey + 1;

//        $item = $this->CI->Item->get_info($consumo_id);
        // $item = $this->CI->consumo->get_info($consumo_id);
        $item = $this->CI->consumo->get_info($consumo_id);
        //array/cart records are identified by $insertkey and item_id is just another field.
        $item = array(($insertkey) =>
            array(
                'id' => $consumo_id,
                'line' => $insertkey,
                'registro_medidor' => $item->registro_medidor,
                'consumo_medidor' => $item->consumo_medidor,
                'interes_generado' => $item->interes_generado,
                'valor_a_pagar' => (double) $item->valor_a_pagar + ($item->interes_generado!=null?$item->interes_generado:0),
                'fecha_consumo' => $item->fecha_consumo,
                'cargo' => (double) $item->cargo,
                'detalle_cargo' => $item->detalle_cargo,
                'valor_cuota' => (double) $item->valor_cuota,
                'tipo_consumo' => $item->tipo_consumo
            )
        );
        //Check if is acometida.
        if($item[$insertkey]['tipo_consumo'] == "acometida" || $item[$insertkey]['tipo_consumo'] == "medidor"){
            $sale_item =$this->CI->Sale->get_sale_items($sale_id, $consumo_id);
            foreach ($sale_item->result() as $sale) {
                $item[$insertkey]['valor_a_pagar'] = $sale->valor_a_pagar;
            }
            // $row->consumo_id
        }
        // $items[$idx]['valor_pagado_acometida'] = $items[$idx]['valor_a_pagar'];
        //add to existing array
        $items += $item;

        $this->set_cart($items);
        return true;
    }

    function out_of_stock($item_id, $almacen) {
        //make sure item exists
        if (!$this->CI->Item->exists($item_id)) {
            //try to get item id given an item_number
            $item_id = $this->CI->Item->get_item_id($item_id);

            if (!$item_id)
                return false;
        }

        //$item = $this->CI->Item->get_info($item_id);
        $almacenes = $this->CI->Item->get_almacenes($item_id, $almacen->almacen_id);
        $quanity_added = $this->get_quantity_already_added($item_id);

        // if ($item->quantity - $quanity_added < 0)
        if ($almacenes == null)
            return false;
        if ($almacenes->cantidad - $quanity_added < 0) {
            return true;
        }

        return false;
    }

    function get_stock($item_id) {
        //make sure item exists
        if (!$this->CI->Item->exists($item_id)) {
            //try to get item id given an item_number
            $item_id = $this->CI->Item->get_item_id($item_id);

            if (!$item_id)
                return 0;
        }

        $item = $this->CI->Item->get_info($item_id);
        return $item->quantity;
    }

    function get_quantity_already_added($item_id) {
        $items = $this->get_cart();
        $quanity_already_added = 0;
        foreach ($items as $item) {
            if ($item['item_id'] == $item_id) {
                $quanity_already_added += $item['quantity'];
            }
        }

        return $quanity_already_added;
    }

    function get_item_id($line_to_get) {
        $items = $this->get_cart();

        foreach ($items as $line => $item) {
            if ($line == $line_to_get) {
                return $item['item_id'];
            }
        }

        return -1;
    }

    function edit_item($line, $acometida) {
        $items = $this->get_cart();
        if (isset($items[$line])) {
            if($items[$line]['tipo_consumo'] == 'acometida' || $items[$line]['tipo_consumo'] == 'medidor'){
                if($acometida > $items[$line]['valor_a_pagar'])
                    $items[$line]['acometida'] = $items[$line]['valor_a_pagar'];
                else
                    $items[$line]['acometida'] = $acometida;
                //$items[$line]['valor_a_pagar_acometida'] = $acometida;
            }
            //$items[$line]['discount'] = $discount;
            // $items[$line]['valor_a_pagar'] = $price;
            $this->set_cart($items);
        }

        return false;
    }

    function is_valid_receipt($receipt_sale_id) {
        //POS #
        $pieces = explode(' ', $receipt_sale_id);

        if (count($pieces) == 2) {
            return $this->CI->Sale->exists($pieces[1]);
        }

        return false;
    }

    function return_entire_sale($receipt_sale_id) {
        //POS #
        $pieces = explode(' ', $receipt_sale_id);
        $sale_id = $pieces[1];
        //$sale_id = $pieces[0];

        $this->empty_cart();
        $this->delete_customer();

        foreach ($this->CI->Sale->get_sale_items($sale_id)->result() as $row) {
            $this->add_item($row->item_id, -$row->quantity_purchased, $row->discount_percent, $row->item_unit_price, null, $row->description, $row->serialnumber);
        }
        $this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
    }

    function copy_entire_sale($sale_id) {
        $this->empty_cart();
        $this->delete_customer();

        foreach ($this->CI->Sale->get_sale_items($sale_id)->result() as $row) {
            $this->add_item(null, $row->consumo_id, $row->sale_id); //, $row->consumo_medidor, $row->valor_a_pagar, $row->fecha_consumo, $row->valor_cuota, $row->cargo, $row->detalle_cargo);
        }
        foreach ($this->CI->Sale->get_sale_payments($sale_id)->result() as $row) {
            //$this->add_payment($row->payment_type,$row->payment_amount);
            $this->add_payment($row->payment_id, $row->payment_amount, $row->payment_type);
        }
        $this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
    }

    function copy_entire_suspended_sale($sale_id) {
        $this->empty_cart();
        $this->delete_customer();

        foreach ($this->CI->Sale_suspended->get_sale_items($sale_id)->result() as $row) {
            $this->add_item($row->item_id, $row->quantity_purchased, $row->discount_percent, $row->item_unit_price, $row->description, $row->serialnumber);
        }
        foreach ($this->CI->Sale_suspended->get_sale_payments($sale_id)->result() as $row) {
            $this->add_payment($row->payment_id, $row->payment_amount, $row->payment_type);
        }
        $this->set_customer($this->CI->Sale_suspended->get_customer($sale_id)->person_id);
    }

    function delete_item($line) {
        $items = $this->get_cart();
        unset($items[$line]);
        if (count($items) == 0)
            $this->delete_customer();
        $this->set_cart($items);
    }

    function empty_cart() {
        $this->CI->session->unset_userdata('cart');
    }

    function delete_customer() {
        $this->CI->session->unset_userdata('customer');
    }

    function clear_mode() {
        $this->CI->session->unset_userdata('sale_mode');
    }

    function clear_all() {
        $this->clear_mode();
        $this->empty_cart();
        //Alain Multiple Payments
        $this->empty_payments();
        $this->delete_customer();
    }

    function get_taxes() {
//        return 0;
        $customer_id = $this->get_customer();
        $customer = $this->CI->Customer->get_info($customer_id);

        //Do not charge sales tax if we have a customer that is not taxable
//        if (!$customer->taxable and $customer_id != -1) {
//            return array();
//        }

        $interes = $this->CI->Appconfig->get('interest');
        $tax_amount = 0;
        foreach ($this->get_cart() as $line => $item) {
            if (!isset($item['interes_generado'])) {
                $d1 = new DateTime();
                $d2 = new DateTime($item['fecha_consumo']);
                $meses_morosidad = $d1->diff($d2)->m;
                if ($d1 > $d2 && $meses_morosidad >= 2) {
                    //var_dump(); // int(4)
                    $tax_amount += ($interes / 100 / 12) * $item['valor_cuota'] * $meses_morosidad;
                }
            } else {
                $tax_amount += $item['interes_generado'];
            }

            //}
        }
        $taxes = array('Interés' => $tax_amount);
        //print_r($taxes);

        return $taxes;
    }

    function get_subtotal() {
        $subtotal = 0;
        foreach ($this->get_cart() as $item) {
//		    $subtotal+=($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100);
            if(($item['tipo_consumo'] == 'acometida' || $item['tipo_consumo'] == 'medidor') && isset($item['acometida']))
                $subtotal += $item['acometida'];
            else
                $subtotal += ($item['valor_a_pagar']);
        }
        return to_currency_no_money($subtotal);
    }

    function get_total() {
        $total = 0;
        foreach ($this->get_cart() as $item) {
            
            if(($item['tipo_consumo'] == 'acometida' || $item['tipo_consumo'] == 'medidor') && isset($item['acometida']))
                $total += $item['acometida'];
            else
                $total += $item['valor_a_pagar'];
//                    $total+=($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100);
        }

//        foreach ($this->get_taxes() as $tax) {
//            $total += $tax;
//        }

        return to_currency_no_money($total);
    }

}
