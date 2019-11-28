<?php
/*
 * debitos.php
 * 
 * Copyright 2019 Juan Manuel Dedionigis <jmdedio@gmail.com>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */
?>

<?php
/*
 * 1- recuperar un (array,lista,pila,etc), en base a lo que indica el manual, con los cbus, importe y fecha de la cobranza.
 * 2- indicar en el mismo (array,lista,pila,etc) si la cobranza es "cobrado" o "rechazado" 
 * 3- Dado el caso de que sea rechazo se quiere ver el importe como $0
 */
class CobranzasClass
{
    public function __construct()
    {
        // Lista de débito
        $this->debitos = array();

        // Obejto débito
        $this->debito = array(
            'cbu'     => '',
            'fecha'   => '',
            'estado'  => '',
            'importe' => 0
        );

        // Códigos de rechazo
        $this->rechazos = array(
            'R02', 'R03', 'R04','R05', 'R06','R07', 'R08',   
            'R10', 'R13', 'R14','R15', 'R17','R19', 'R20',   
            'R23', 'R24', 'R25','R26', 'R28','R29', 'R75',   
            'R81', 'R87', 'R89','R91', 'R95'
        );

        // Registros de débitos
        $this->registros = array();
    }

    public function __destruct(){}

    // Extrae los registros del fichero y los almacena en $this->registros
    function extrae_registros($file)
    {
        $this->registros = file($file) or die("No se pudo leer el fichero: ".$file);
        return $this->registros;
    }

    function rend_cobranzas()
    {
        /* Bucle principal
         * Recorre todo el fichero, línea por linea para armar el array.
         * Punto 1
         */
        foreach($this->registros as $registro){
            // Condicional para seleccionar sólo los registros de débitos
            if(
                strpos(substr($registro, 0, 26), 'BANCO') === False
                && strlen($registro) > 3
            ){
                $this->debito['cbu'] = substr($registro, 0, 24);
                $this->debito['fecha'] = strtotime(substr($registro, 294, 8)); // Fecha de cobro
                $this->debito['estado'] = substr($registro, 134, 3);
                // Condicional para determinar el estado del débito
                // Puntos 2 y 3
                if(array_search($this->debito['estado'], $this->rechazos) === False){
                    $this->debito['estado'] = 'cobrado';
                    $this->debito['importe'] = number_format(substr_replace(substr($registro, 302, 14), '.', -2, 0), 2);
                } else{
                    $this->debito['estado'] = 'rechazado '.$this->debito['estado'];
                }
                array_push($this->debitos, $this->debito);
            }
        }
        return $this->debitos;
    }
}

?>
