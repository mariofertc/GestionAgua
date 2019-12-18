<?php

class Consumo extends CI_Model {
    /*
      Determines if a given person_id is a customer
     */

    function exists($consumo_id) {
        $this->db->from('consumo');
        $this->db->where('id', $consumo_id);
        $this->db->where('deleted', 0);
        $query = $this->db->get();
        return ($query->num_rows() == 1);
    }

    /*
      Returns all the customers
     */

    function get_all($num = 10, $offset = 0, $where = "", $order = null) {
        if ($order == null) {
            $order = "fecha_consumo";
        }
        //$this->db->select('id','nombre');
        $this->db->from('consumo');
        $this->db->join('customers', 'customers.person_id=consumo.id_cliente');
        if ($where != "") {
            $this->db->where($where);
        }
        $this->db->where('consumo.deleted', 0);
        //$this->db->where('consumo.tipo_consumo', 'consumo');
        $this->db->order_by($order);
        $this->db->limit($num, $offset);

        return $this->db->get()->result_array();
    }

    function get_total($where = '') {
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        if ($where != "")
            $this->db->where($where);
        $this->db->where('deleted', 0);
        return $this->db->count_all_results();
    }

    /*
      Gets information about a particular customer
     */

    function get_info($consumo_id) {
        $this->db->from('consumo');
        $this->db->where('deleted', 0);
        $this->db->where('id', $consumo_id);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            //Get empty base parent object, as $customer_id is NOT an customer
            $fields = $this->db->list_fields('consumo');
            $consumo_obj = new stdClass;
            foreach ($fields as $field) {
                $consumo_obj->$field = '';
            }

            return $consumo_obj;
        }
    }

    /*
      Gets information about multiple customers
     */

    function get_multiple_info($customer_ids) {
        $this->db->from('customers');
        $this->db->join('people', 'people.person_id = customers.person_id');
        $this->db->where('deleted', 0);
        $this->db->where_in('customers.person_id', $customer_ids);
        $this->db->order_by("last_name", "asc");
        return $this->db->get();
    }

    /*
      Coje el Id del Proveedor, con el # de cuenta
     */

    function get_customer_id($account_number) {
        $this->db->from('customers');
        $this->db->where('account_number', $account_number);
        $this->db->where('deleted', 0);

        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            return $query->row()->person_id;
        }

        return false;
    }

    /**
     * Inserts or updates a consumption.
     * @param type $person_data
     * @param type $customer_data
     * @param type $customer_id
     * @return type
     */
    function save(&$consumo_data, $consumo_id) {
        $success = false;
        //Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->trans_start();


        //if (parent::save($person_data, $customer_id)) {
        if (!$consumo_id or ! $this->exists($consumo_id)) {
            $success = $this->db->insert('consumo', $consumo_data);
            $consumo_data['id'] = $this->db->insert_id();
        } else {
            $this->db->where('id', $consumo_id);
            $success = $this->db->update('consumo', $consumo_data);
        }


        $this->db->trans_complete();
        return $success;
    }

    /**
     * Update the interest gained.
     * @param type $consumo_data
     * @param type $consumo_id
     * @return type
     */
    function update_interest(&$consumo_data, $consumo_id) {
        $success = false;
        //Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->trans_start();
        $this->db->where('id', $consumo_id);
        $success = $this->db->update('consumo', array('interes_generado'=>$consumo_data['interes_generado']));        

        $this->db->trans_complete();
        return $success;
    }

    /*
      Preform a search on customers
     */

    function search($search) {
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		email LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		phone_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		account_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        $this->db->order_by("last_name", "asc");

        return $this->db->get();
    }

}
