<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Importador_model extends CI_Model{

    function __construct()
    {
        parent::__construct();
    }
    private function coct($table, $fields)
    {
        if (!$this->db->table_exists($table)) {
            $this->load->dbforge();
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_field($fields);
            if($this->dbforge->create_table($table)) {
                return $table;
            } else {
                return false;
            }
        } else {
            return $table;
        }
    }
    private function diff_folios($a,$b)
    {
        if (isset($a['numero_de_folio']) && isset($b['numero_de_folio'])) {
            return (int)$b['numero_de_folio'] - (int)$a['numero_de_folio'];
        }
    }
    public function tableinicial($mes,$anio)
    {

        $table = 'transacciones_'.$mes."_".$anio;
        $fields = array(
             'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'origen_del_viaje' => array(
                'type' =>'VARCHAR',
                'constraint' => '250',
                'unsigned' => true,
            ),
            'destino_del_viaje' => array(
                'type' =>'VARCHAR',
                'constraint' => '250',
                'unsigned' => true,
            ),
            'numero_de_asiento' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'tipo_de_boleto' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'numero_de_folio' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'concepto' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'tipo_de_forma_de_pago' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'importe_sin_iva' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'iva' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'importe_total' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'fecha_venta' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'hora_venta' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'numero_de_operacion' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'clase_de_servicio' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'nombre_asesor' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'descripcion_marca' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'numero_de_corrida' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'fecha_corrida' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'hora_corrida' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'mes_de_la_corrida' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'tipo_de_corrida' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'hora_de_emision' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'correo_usuario' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'tipo_de_carga' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
            ),
            'created_at' => array(
                'type' => 'DATETIME',
            ),
             'updated_at' => array(
                'type' => 'DATETIME',
            ),
        );
        $this->coct($table, $fields);
        return $table;
    }
    public function importar($array,$file,$tipo,$tabla)
    {
        $this->db->select('numero_de_folio as numero_de_folio')
            ->from($tabla);
        $sale_ids_chunk = array_chunk($array, 1000);

        foreach($sale_ids_chunk as $sale_ids)
        {
             $this->db->or_where_in('numero_de_folio', array_column($sale_ids,'Número de Folio de Boleto'));
        }

        $resultado= $this->db->get()->result_array();

        if (!$resultado) {
              $insert=$this->format_db($array,$tipo);
            $this->db->insert_batch($tabla, $insert);
        } else {
            $udif=$this->format_db($array,$tipo);

            $diferencia=array_udiff($udif, $resultado, [$this, 'diff_folios']);
            if ($diferencia)
            {
                $this->db->insert_batch($tabla, $diferencia);
            }
            $update=$this->format_db($array,$tipo);
            $this->db->update_batch($tabla,$update,'numero_de_folio');
        }
        return TRUE;
    }
    private function format_db($array,$tipo)
    {
        try {
             return array_map(function($array)  use($tipo){
             $arrayreturn=array(
                'tipo_de_carga' => $tipo,
                'origen_del_viaje' => $array['Origen del Viaje'],
                'destino_del_viaje' => $array['Destino del Viaje'],
                'numero_de_asiento' => $array['Número de Asiento'],
                'tipo_de_boleto' => $array['Tipo de Boleto'],
                'numero_de_folio' => $array['Número de Folio de Boleto'],
                'concepto' => $array['Concepto'],
                'tipo_de_forma_de_pago' => $array['Tipo de Forma de Pago'],
                'importe_sin_iva' => $array['Importe sin IVA'],
                'iva' => $array['IVA'],
                'importe_total' => $array['Importe Total'],
                'fecha_venta' => $array['Fecha Venta'],
                'hora_venta' => $array['Hora Venta'],
                'numero_de_operacion' => $array['Número de Operación'],
                'clase_de_servicio' => $array['Clase de Servicio'],
                'nombre_asesor' => $array['Nombre Asesor'],
                'descripcion_marca' => $array['Descripción Marca'],
                'numero_de_corrida' => $array['Número de Corrida'],
                'fecha_corrida' => $array['Fecha Corrida'],
                'hora_corrida' => $array['Hora Corrida'],
                'mes_de_la_corrida' => $array['Mes de la corrida'],
                'tipo_de_corrida' => $array['Tipo de Corrida'],
                'hora_de_emision' => $array['Hora de Emisión del Reporte'],
                'correo_usuario' => $array['Correo Usuario'],
                'created_at'=> date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
            );
                return $arrayreturn;
            }, $array);
        } catch (Exception $e) {
            echo "un archivo no tiene el formato esperado";
            exit();
        }
    }
}