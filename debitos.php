<?php
/*
 * debitos.php
 * 
 * Copyright 2019 Juanmanuel <jmdedio@gmail.com>
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
function rend_cobranzas($file)
{
    $array = file($file) or die("No se pudo leer el fichero: ".$file);

    $debitos = array();
    $debito = array(
        'cbu'     => '',
        'fecha'   => '',
        'estado'  => '',
        'importe' => 0
    );

    // Códigos de rechazo
    $rechazos = array(
        'R02', 'R03', 'R04','R05', 'R06','R07', 'R08',   
        'R10', 'R13', 'R14','R15', 'R17','R19', 'R20',   
        'R23', 'R24', 'R25','R26', 'R28','R29', 'R75',   
        'R81', 'R87', 'R89','R91', 'R95'
    );

    /* Bucle principal
     * Recorre todo el fichero, línea por linea para armar el array.
     * Punto 1
     */
    foreach($array as $a){
        // Condicional para seleccionar sólo los registros de débitos
        if(
            strpos(substr($a, 0, 26), 'BANCO') === False
            && strlen($a) > 3
        ){
            $debito['cbu'] = substr($a, 4, 22);
            $debito['fecha'] = substr($a, 294, 8); // Fecha de cobro
            $debito['estado'] = substr($a, 134, 3);
            // Condicional para determinar el estado del débito
            // Puntos 2 y 3
            if(array_search($debito['estado'], $rechazos) === False){
                $debito['estado'] = 'cobrado';
                $debito['importe'] = number_format(substr_replace(substr($a, 302, 14), '.', -2, 0), 2);
            } else{
                $debito['estado'] = 'rechazado '.$debito['estado'];
            }
            array_push($debitos, $debito);
        }
    }
    return $debitos;
}
?>
