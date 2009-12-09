<?php
/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 */

 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");
 ver_permissao(45) ;
 $titulo = $LANG['menu_tarifas']." -> ".$LANG['menu_tarifas'] ;
 // Monta lista de Operadoras
 if (!isset($operadora) || count($operadoras) == 0) {
    try {
       $sql_oper = "SELECT * FROM operadoras ORDER by nome" ;
       $row_oper = $db->query($sql_oper)->fetchAll();
    } catch (Exception $e) {
       display_error($LANG['error'].$e->getMessage(),true) ;
    }
    unset($val);
    $operadoras = array(""=>$LANG['undef']);
    foreach ($row_oper as $val)
       $operadoras[$val['codigo']] = $val['nome'] ;
    asort($operadoras) ;
 }

 // SQL padrao
 $sql = "SELECT tarifas.*, operadoras.nome FROM tarifas, operadoras WHERE tarifas.operadora=operadoras.codigo " ;
 // Opcoes de Filtros
 $opcoes = array( "nome" => $LANG['operadora'],
                  "cidade" => $LANG['city'],
                  "ddd" => $LANG['ddd']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) {
    $sql .= " AND ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
 }
 $sql .= "  ORDER BY operadora,cidade,prefixo" ;
 // Executa acesso ao banco de Dados
 
 try {
     $row = $db->query($sql)->fetchAll();
     foreach ($row as $key=>$value) {
        $codigo = $value['codigo'] ;
        $sql_vlr = "SELECT * FROM tarifas_valores WHERE codigo = $codigo " ;
        $sql_vlr.= " ORDER BY data DESC LIMIT 1" ;
        $row_vlr = $db->query($sql_vlr)->fetch();
        if ($row_vlr != "" )
           $row[$key] += $row_vlr ;          
     }
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ; 
    exit ;
 }

 $tot_pages = ceil(count($row)/$SETUP['ambiente']['linelimit']) ;
 for ($i = 1 ; $i <= $tot_pages ; $i ++ )
     $paginas[$i] = $i;
 
 // Define variaveis do template
 $smarty->assign('OPERADORAS',$operadoras);
 $smarty->assign ('DADOS',$row);
 $smarty->assign ('TOT',$tot_pages);
 $smarty->assign ('PAGINAS',$paginas) ;
 $smarty->assign ('INI',1);
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('array_include_buttom',array("url" => "../tarifas/tarifas.php", "display"  => $LANG['include']." ".$LANG['menu_tarifas']));
  
 // Exibe template
 display_template("rel_tarifas.tpl",$smarty,$titulo);
 ?>
