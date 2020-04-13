<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Importador extends CI_Controller {

public function __construct()
{
    parent::__construct();
    $this->load->model('importador_model','modelo');
}
    public function index()
    {
       
        $this->read_file();
    }
     /**
     * funcion publica la cual ejecutara el cron
     */
    public function read_file()
    {
        $this->get_server_file();
    }
    /**
     * { function_description }
     *
     * @param      array   $array     array de datos
     * @param      <type>  $database  base de datos
     * @param      <type>  $table     tabla
     */
    private function process_file_directo(array $array,$database,$table,$file)
    {
        return $this->importador->onInsert($array,$database,$table,$file);
    }
    /**
     * funcion procesa archivos de tipo txt con formato campo1|campo2|campo3 y retorna array asociativo
     *
     * @param      <File>  $file   el archivo
     *
     * @return     <Array>  Array Asociativo
     */
    private function file_to_array($file) {

            $csv= array_map(function($v){return str_getcsv($v, ",");}, file($file));
            // echo memory_get_usage()."<br>";
            array_walk($csv, function(&$a) use ($csv) {
              $a = array_combine($csv[0], $a);
            });
            // echo memory_get_usage();
            array_shift($csv);

            if (empty($csv)) {
                $path_parts=pathinfo($file);
                rename(
                    $file,
                    APPPATH."files/no_proccess/".$path_parts['basename']
                );
                exit();
            }
            return $csv;
    }
    /**
     * funcion que obtiene todos los archivos del servidor
     */
    private function get_server_file()
    {
        $list1 = glob(FCPATH."files/to_import/*.csv");
        $list2 = glob(FCPATH."files/to_import/*.txt");
        $list = array_merge($list1,$list2);

        if (!empty($list)) {

            foreach ($list as $key => $value) {

                $path_parts = pathinfo($value);
                $nombre = $path_parts['filename'];
                $nombre=str_replace("_", " ", $nombre);
                $nombre=str_replace("-", " ", $nombre);
                $nombre=str_replace(".", " ", $nombre);
                $this->name_cases($nombre,$value);
                sleep(4);
            }
        }
    }

    /**
     * funcion encargada de validar que el archivo tenga el nombre
     * correcto para poder ser cargado en caso de no coincidencia
     * se mueve de carpeta
     *
     * @param      <type>  $name   nombre del archivo
     * @param      <type>  $file   el archivo
     */
    private function name_cases($name,$file)
    {
        $bases = ['internet','clickbus','celular'];
        foreach ($bases as $obligatorio) {
            $palabra = $obligatorio;
            $existe = strpos(strtolower($name), $palabra);

            if ($existe !== false) {
                    $estatus=$this->importar(
                        $this->file_to_array($file),
                        $name,
                        $palabra
                    );
                    $path_parts=pathinfo($file);
                    if($estatus){
                       rename(
                            $file,
                            FCPATH."files/imported/".$path_parts['basename']
                        );
                       echo "archivo ".$path_parts['basename']." fue cargado con exito";
                       echo "<br>";
                    }
                    else {
                          rename(
                            $file,
                            FCPATH."files/no_proccess/".$path_parts['basename']
                        );
                        echo "ocurrio un error en el archivo ".$path_parts['basename'];
                        echo "<br>";
                    }

            }
        }
    }
    /**
     * function carga de archivo
     *
     * @param      array   $array  The array
     * @param      <type>  $file   el archivo
     * @param      <type>  $tipo   The tipo
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    private function importar(array $array,$file,$tipo)
    {

         if ( isset($array[0]['Fecha Corrida']) ) {
            $fecha = $array[0]['Fecha Corrida'];
            $fecha = $date = str_replace('/', '-', $fecha);

            $fecha = strtotime($fecha);
            $fecha  = date('m-Y',$fecha);
            $fecha = explode("-", $fecha);

            $tabla = $this->modelo->tableinicial($fecha[0],$fecha[1]);
            return $this->modelo->importar($array,$file,$tipo,$tabla);
         }

    }

}
