<?php

require_once ("secure_area.php");

class Consumo extends Secure_area {

    function __construct() {
        parent::__construct('consumo');
        //$this->load->library('receiving_lib');
        $this->controller_name = 'consumo';
    }

    function index() {
        $data['controller_name'] = $this->controller_name;
        $data['form_width'] = $this->get_form_width();
        $data['manage_table'] = get_people_manage_table();
        $data['title'] = 'home_home';
        $this->twiggy->set($data);
        $this->twiggy->display('people/manage');
    }
    
    function mis_datos() {
		$data['controller_name'] = $this->controller_name;
		$data['form_width'] = $this->get_form_width();
		$data['form_height'] = 150;
		$aColumns = array('person_id', 'first_name', 'last_name', 'email','phone_number');
		//Eventos Tabla
		$cllAccion = array(
				'1' => array(
						'function' => "view",
						'common_language' => "consumo_add",
						'language' => "_update",
						'width' => $this->get_form_width(),
						'height' => $this->get_form_height()),
				);
		echo getData($this->Customer, $aColumns, $cllAccion);
	}


    /*
      Returns customer table data rows. This will be called with AJAX.
     */

    function search() {
        $search = $this->input->post('search');
        $data_rows = get_people_manage_table_data_rows($this->Customer->search($search), $this);
        echo $data_rows;
    }

    /*
      Gives search suggestions based on what is being searched for
     */

    function suggest() {
        $suggestions = $this->Customer->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
        echo implode("\n", $suggestions);
    }

    /*
      Loads the customer edit form
     */

    function view($customer_id = -1) {
        $data['person_info'] = $this->Customer->get_info($customer_id);
        $tipo_consumo = $this->tipo_consumo->get_all(10,0,"","","id,nombre");
        
        $data['tipo_consumo'] = array_to_htmlcombo($tipo_consumo, array('blank_text' => 'Escoja una opción', 'id' => 'id', 'name' => 'nombre'));
//        $this->load->view("customers/form", $data);
        
         $this->twiggy->set($data);
        $this->twiggy->display('consumo/form');
    }

    /*
      Inserts/updates a customer
     */

    function save($customer_id = -1) {
        $person_data = array(
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'email' => $this->input->post('email'),
            'phone_number' => $this->input->post('phone_number'),
            'address_1' => $this->input->post('address_1'),
            'address_2' => $this->input->post('address_2'),
            'city' => $this->input->post('city'),
            'state' => $this->input->post('state'),
            'zip' => $this->input->post('zip'),
            'country' => $this->input->post('country'),
            'comments' => $this->input->post('comments')
        );
        $customer_data = array(
            'account_number' => $this->input->post('account_number') == '' ? null : $this->input->post('account_number'),
            'taxable' => $this->input->post('taxable') == '' ? 0 : 1,
        );
        if ($this->Customer->save_customer($person_data, $customer_data, $customer_id)) {
            //New customer
            if ($customer_id == -1) {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('customers_successful_adding') . ' ' .
                    $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => $customer_data['person_id']));
            } else { //previous customer
                echo json_encode(array('success' => true, 'message' => $this->lang->line('customers_successful_updating') . ' ' .
                    $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => $customer_id));
            }
        } else {//failure
            echo json_encode(array('success' => false, 'message' => $this->lang->line('customers_error_adding_updating') . ' ' .
                $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => -1));
        }
    }

    /*
      This deletes customers from the customers table
     */

    function delete() {
        $customers_to_delete = $this->input->post('ids');

        if ($this->Customer->delete_list($customers_to_delete)) {
            echo json_encode(array('success' => true, 'message' => $this->lang->line('customers_successful_deleted') . ' ' .
                count($customers_to_delete) . ' ' . $this->lang->line('customers_one_or_multiple')));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->lang->line('customers_cannot_be_deleted')));
        }
    }

    /**
     * Display form: Import data from an excel file
     * @author: Nguyen OJB, Mario T.
     * @since: 10.1
     */
    function excel_import() {
        $this->load->view("customers/excel_import", null);
    }

    /**
     * Read data from excel file -> save it to databse
     * @author: Nguyen OJB, Mario T.
     * @since: 10.1
     */
    function do_excel_import() {
        $msg = "do_excel_import";
        $failCodes = null;
        $successCode = null;
        if ($_FILES['file_path']['error'] != UPLOAD_ERR_OK) {
            $msg = $this->lang->line('items_excel_import_failed');
            echo json_encode(array('success' => false, 'message' => $msg));
            return;
        } else {
            try {
                $this->load->library('spreadsheetexcelreader');
                $this->spreadsheetexcelreader->store_extended_info = false;
                $success = $this->spreadsheetexcelreader->read($_FILES['file_path']['tmp_name']);

                $rowCount = $this->spreadsheetexcelreader->rowcount(0);
                if ($rowCount > 2) {
                    for ($i = 3; $i <= $rowCount; $i++) {
                        $account_number = $this->spreadsheetexcelreader->val($i, 'A');
                        $customer_id = $this->Customer->get_customer_id($account_number);
                        $customer_data = array(
                            'account_number' => $account_number,
                            'taxable' => $this->spreadsheetexcelreader->val($i, 'B')
                        );
                        $person_data = array(
                            'first_name' => $this->spreadsheetexcelreader->val($i, 'C'),
                            'last_name' => $this->spreadsheetexcelreader->val($i, 'D'),
                            'phone_number' => $this->spreadsheetexcelreader->val($i, 'E'),
                            'email' => $this->spreadsheetexcelreader->val($i, 'F'),
                            'address_1' => $this->spreadsheetexcelreader->val($i, 'G'),
                            'address_2' => $this->spreadsheetexcelreader->val($i, 'H'),
                            'city' => $this->spreadsheetexcelreader->val($i, 'I'),
                            'state' => $this->spreadsheetexcelreader->val($i, 'J'),
                            'zip' => $this->spreadsheetexcelreader->val($i, 'K'),
                            'country' => $this->spreadsheetexcelreader->val($i, 'L'),
                            'comments' => $this->spreadsheetexcelreader->val($i, 'M')
                        );
                        if ($this->Customer->save_customer($person_data, $customer_data, $customer_id)) {
                            
                        } else {//insert or update item failure
                            $failCodes[] = $this->spreadsheetexcelreader->val($i, 'A');
                            //echo json_encode( array('success'=>true,'message'=>'Your upload file has no data or not in supported format.') );
                        }
                    }
                } else {
                    // rowCount < 2
                    echo json_encode(array('success' => true, 'message' => 'Your upload file has no data or not in supported format.'));
                    return;
                }
            } catch (Exception $e) {
                //echo 'Caught exception: ',  $e->getMessage(), "\n";
                // echo json_encode( array('success'=>false,'message'=>$e->getMessage()) );
                echo json_encode(array('success' => false, 'message' => 'vamos'));
                return;
            }
        }

        $success = true;
        if (count($failCodes) > 1) {
            $msg = "Most suppliers imported. But some were not, here is list of their CODE (" . count($failCodes) . "): " . implode(", ", $failCodes);
            $success = false;
        } else {
            $msg = "Import suppliers successful";
        }

        echo json_encode(array('success' => $success, 'message' => $msg));
    }

    /*
      get the width for the add/edit form
     */

    function get_form_width() {
        return 350;
    }
    function get_form_height() {
        return 550;
    }
}