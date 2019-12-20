<?php

require_once ("secure_area.php");

class Consumos extends Secure_area {

    function __construct() {
        parent::__construct('consumos');
        //$this->load->library('receiving_lib');
        $this->controller_name = 'consumos';
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
				'0' => array(
						'function' => "view",
						'common_language' => "consumo_consumo",
						'language' => "_update",
						'width' => $this->get_form_width(),
						'height' => $this->get_form_height()),
                '1' => array(
                        'function' => "view_acometida",
                        'common_language' => "consumo_acometida",
                        'language' => "_update",
                        'width' => $this->get_form_width(),
                        'height' => $this->get_form_height()),
                '2' => array(
                        'function' => "view_multa",
                        'common_language' => "consumo_multa",
                        'language' => "_update",
                        'width' => $this->get_form_width(),
                        'height' => $this->get_form_height()),
                '3' => array(
                        'function' => "view_medidor",
                        'common_language' => "consumo_medidor",
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

    /**
     * Insert consumption
     * @param type $customer_id
     */
    function view($customer_id = -1,$consumo_id = -1) {
        $person_info = $this->Customer->get_info($customer_id);
        $data['person_info'] = $person_info;
        
        $consumo_info = $this->consumo->get_info($consumo_id);
        $data['consumo_info'] = $consumo_info;
        
        $tipo_consumo = $this->tipo_consumo->get_all(100,0,"","","id,nombre");        
        //$registro_anterior = $this->consumo->get_all(1000,0,"id_cliente=$customer_id","id");
        $where = array(
            'id_cliente'=>$customer_id,
            'consumo.tipo_consumo'=> 'consumo'
        );
        $registro_anterior = $this->consumo->get_all(1000,0,$where,"fecha_consumo asc");
        
        if(count($registro_anterior)==0){
            $registro_anterior = $person_info->registro_inicial;
            $fecha_anterior = $person_info->fecha_ingreso;
        }
		//echo gettype($registro_anterior);
		//Cuando reinician el medidor
		if($person_info->registro_inicial == -1 or gettype($registro_anterior)=="string"){
			$registro_tmp = $registro_anterior[count($registro_anterior)-1];
			if(isset($registro_tmp['fecha_consumo'])){
				$fecha_anterior = substr($registro_tmp['fecha_consumo'],0,10);
			}else{
				$fecha_anterior = $person_info->fecha_ingreso;
			}
			if(gettype($registro_anterior)=="string")
				$registro_anterior = $registro_anterior;
			else
				$registro_anterior = 0;
		}
		else{
            $registro_tmp = $registro_anterior[count($registro_anterior)-1];
            $registro_anterior = $registro_tmp['registro_medidor'];
            $fecha_anterior = substr($registro_tmp['fecha_consumo'],0,10);
        }
        if(is_null($registro_anterior) || is_null($fecha_anterior)){
            $data['error'] = "Sin registros anteriores. Revise información del cliente";
            //return null;
        }
//        $tasas_aplicables = $this->cuota->get_all(0,100,array('id_tipo_consumo'=>$person_info->person_id));
        $tasas_aplicables = $this->cuota->get_all(100,0,'id_tipo_consumo='.$person_info->id_tipo_consumo);
        $formula = "";//if((A1-"~registro_anterior~")<0,0,
        $cantidad_tasas = count($tasas_aplicables)-1;
        $valor_cambio_medidor = 0;
        foreach($tasas_aplicables as $tasa){
            $rango =$tasa['rango'];
            $valor =$tasa['valor'];
            if($tasa['rango']!='medidor'){
                if (strlen($formula) != 0) {
                    $formula .= ",";
                }
                $formula.="if(A2<=$rango,$valor";
            }
            else{
                $valor_cambio_medidor = $valor;
            }
            if($tasas_aplicables[$cantidad_tasas]==$tasa){
                $formula.=str_repeat(")", $cantidad_tasas);
            }
        }
        //var_dump($formula);
        
        $data['formula_cuota'] = $formula;
        $data['tasas_aplicables'] = $tasas_aplicables;
        $data['valor_cambio_medidor'] = $valor_cambio_medidor;
        //var_dump($tasas_aplicables);
        $data['registro_anterior'] = $registro_anterior;
        //echo $registro_anterior;
        //2016-12-26
        $fecha_anterior = DateTime::createFromFormat('Y-m-d',$fecha_anterior)->add(new DateInterval("P1M"))->format('Y-m-d');
        $data['fecha_consumo'] = $fecha_anterior;
        
        $data['tipo_consumo'] = array_to_htmlcombo($tipo_consumo, array('blank_text' => 'Escoja una opción', 'id' => 'id', 'name' => 'nombre'));        
         $this->twiggy->set($data);
        $this->twiggy->display('consumo/form');
    }

    /**
     * Insert acometida value
     * @param type $customer_id
     */
    function view_acometida($customer_id = -1) {
        $data = $this->view_generic($customer_id, "acometida");
    }
    /**
     * Insert medidor value
     * @param type $customer_id
     */
    function view_medidor($customer_id = -1) {
        $data = $this->view_generic($customer_id, "medidor");
    }
    /**
     * Insert multa value
     * @param type $customer_id
     */
    function view_multa($customer_id = -1) {
        $data = $this->view_generic($customer_id, "multa");
    }

    function view_generic($customer_id = -1, $tipo_consumo) {
        $person_info = $this->Customer->get_info($customer_id);
        $data['person_info'] = $person_info;
        $cuota = $this->cuota->get_id_by_rango_consumo($person_info->id_tipo_consumo, $tipo_consumo);
        $data['valor'] = $cuota->valor;
        $data['tipo_consumo'] = $tipo_consumo;
        $this->twiggy->set($data);
        $this->twiggy->display('consumo/form_generic');
    }

    /**
     * Inserts/updates a consumption
     */
    function save($consumo_id = -1) {
        $fecha_hasta = DateTime::createFromFormat('Y-m-d',$this->input->post('fecha_consumo'))->add(new DateInterval("P1M"))->format('Y-m-d');
        //Id Cuota
        $id_tipo_consumo = $this->input->post('id_tipo_consumo');
        $valor_cuota = $this->input->post('valor_cuota');
        $cuota = $this->cuota->get_id_by_consumo($id_tipo_consumo,$valor_cuota);
        $id_cuota = $cuota->id;
		$id_cliente = $this->input->post('id_cliente');
		$registro_inicial = $this->input->post('registro_inicial');
        $consumo_data = array(
            'id_cliente' => $id_cliente,
            'id_cuota' => $id_cuota,
            'registro_medidor' => $this->input->post('registro_medidor'),
            'consumo_medidor' => $this->input->post('consumo_medidor'),
            'valor_a_pagar' => $this->input->post('valor_a_pagar'),
            'fecha_consumo' => $this->input->post('fecha_consumo'),
            'valor_cuota' => $this->input->post('valor_cuota'),
            'fecha_hasta' => $fecha_hasta,
            'fecha_creación' => date('Y-m-d H:i:s'),
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
            'estado' => 'generado',
            'cargo' => $this->input->post('es_cambio_medidor'),
            'detalle_cargo' => $this->input->post('es_cambio_medidor')?'Cambio de Medidor':null
        );
        if ($this->consumo->save($consumo_data, $consumo_id)) {
			//Actualizamos el consumo inicial si es nuevo medidor.
			if($registro_inicial == -1){
				$this->Customer->update_consumo_inicial($id_cliente);
			}
            //New customer
            if ($consumo_id == -1) {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('consumo_successful_adding') . ' ' .
                    $consumo_data['id'] . ' ' . $consumo_data['valor_a_pagar'], 'consumo_id' => $consumo_data['id']));
            } else { //previous customer
                echo json_encode(array('success' => true, 'message' => $this->lang->line('consumo_successful_updating') . ' ' .
                    $consumo_data['id'] . ' ' . $consumo_data['valor_a_pagar'], 'consumo_id' => $consumo_id));
            }
        } else {//failure
            echo json_encode(array('success' => false, 'message' => $this->lang->line('consumo_error_adding_updating') . ' ' .
                $consumo_data['id'] . ' ' . $consumo_data['valor_a_pagar'], 'consumo_id' => -1));
        }
    } 

    /**
     * Inserts/updates a consumption
     */
    function save_generic($consumo_id = -1) {
        $tipo_consumo = $this->input->post('tipo_consumo');;
        //Id Cuota
        $id_tipo_consumo = $this->input->post('id_tipo_consumo');
        $cuota = $this->cuota->get_id_by_rango_consumo($id_tipo_consumo,$tipo_consumo);
        $id_cliente = $this->input->post('id_cliente');
        $consumo_data = array(
            'id_cliente' => $id_cliente,
            'id_cuota' => $cuota->id,
            'valor_a_pagar' => $cuota->valor,
            'fecha_consumo' => $this->input->post('fecha'),
            'fecha_creación' => date('Y-m-d H:i:s'),
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
            'estado' => 'generado',
            'tipo_consumo' => $tipo_consumo,
            'detalle_cargo' => $this->input->post('observaciones')
        );
        if ($this->consumo->save($consumo_data, $consumo_id)) {
            //New fines
            if ($consumo_id == -1) {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('consumo_successful_adding') . ' ' .
                    $consumo_data['id'] . ' ' . $consumo_data['valor_a_pagar'], 'consumo_id' => $consumo_data['id']));
            } else { //previous customer
                echo json_encode(array('success' => true, 'message' => $this->lang->line('consumo_successful_updating') . ' ' .
                    $consumo_data['id'] . ' ' . $consumo_data['valor_a_pagar'], 'consumo_id' => $consumo_id));
            }
        } else {//failure
            echo json_encode(array('success' => false, 'message' => $this->lang->line('consumo_error_adding_updating') . ' ' .
                $consumo_data['id'] . ' ' . $consumo_data['valor_a_pagar'], 'consumo_id' => -1));
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
        return 450;
    }
    function get_form_height() {
        return 550;
    }
}