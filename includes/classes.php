<?php
/*-----------------------------------------------------------------------------
 * Programa: login.php - Login do Sistema
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------
 * Classe Bar_Graph - Monta uma linha horizontal para exibicao grafica
 * Recebe : array($parrams), com os seguintes valores:
 *          a = Valor percentual
 * Retorna: Linha de "imagens"  que compoe uma barra horizontal do grafico
 *----------------------------------------------------------------------------*/
class Bar_Graph {
    function linha($params, &$smarty) {
       $a = $params['a'] ;
       if (strpos($a,"%") > 0)
          $a = substr($a,0,strpos($a,"%")) ;
       if ($a < 10) {
         $ret="<table class=subtable border=0 cellpadding=0 cellspacing=0 width=100%><tr>" ;
         $ret.="<td class=subtable height=16 width=$a% style=".'"background: url(../imagens/greenbar_middle.gif) repeat-x;">'."</td><td class=subtable style=".'"text-align:left;color: #000;font-weight: bold" '." width=".(100-$a)."%>$a%</td></tr></table>" ;
       } elseif ($a >= 10  && $a < 50) {
         $ret="<table class=subtable border=0 cellpadding=0 cellspacing=0 width=100%><tr>" ;
         $ret.="<td class=subtable height=16 width=$a% style=".'"background: url(../imagens/greenbar_middle.gif) repeat-x;text-align:right;color: #000;font-weight: bold">'."$a% </td><td class=subtable width=".(100-$a)."%></td></tr></table>" ;
       } elseif ($a >= 50  && $a < 80) {
         $ret="<table class=subtable border=0 cellpadding=0 cellspacing=0 width=100%><tr>" ;
         $ret.="<td class=subtable height=16 width=$a% style=".'"background: url(../imagens/orangebar_middle.gif) repeat-x;text-align:right;color: #FFF;font-weight: bold">'."$a% </td><td class=subtable width=".(100-$a)."%></td></tr></table>" ;
       } else {
         $ret="<table class=subtable border=0 cellpadding=0 cellspacing=0 width=100%><tr>" ;
         $ret.="<td class=subtable height=16 width=$a% style=".'"background: url(../imagens/redbar_middle.gif) repeat-x;text-align:right;color: #FFF;font-weight: bold">'."$a% </td><td class=subtable width=".(100-$a)."%></td></tr></table>" ;
       } // Fim do IF

       return $ret ;
    } // Fim da Funcao
 } // Fim  da Classe

 class Formata {
    /*-------------------------------------------------------------------------
     * Funcao fmt_segundos - Formata segundos em uma saida padrao
     * Recebe : segundos, tipo
     * Retorna: "m" Minutos  ; "H": Horas ;             "h": Horas arredondada
     *          "D": Dias    ; "d": Dias arredontados ; "hms": hh:mm:ss
     *-------------------------------------------------------------------------*/
    function fmt_segundos($params,$smarty){
       $segundos = $params['a'] ;
       $tipo_ret = (isset($params['b']) && $params['b'] != "") ? $params['b'] : 'hms' ;
       switch($tipo_ret){
        case "m":
           $ret = $segundos/60;
           break;
        case "H":
           $ret = $segundos/3600;
           break;
        case "h":
           $ret = round($segundos/3600);
           break;
        case "D":
           $ret = $segundos/86400;
           break;
        case "d":
           $ret = round($segundos/86400);
           break;
        case "hms":
           $min_t = intval($segundos/60) ;
           $tsec = sprintf("%02s",intval($segundos%60)) ;
           $thor = sprintf("%02s",intval($min_t/60)) ;
           $tmin = sprintf("%02s",intval($min_t%60)) ;
           $ret = $thor.":".$tmin.":".$tsec;
           break ;
        case "ms":
           $min_t = intval($segundos/60) ;
           $tsec = sprintf("%02s",intval($segundos%60)) ;
           $tmin = sprintf("%02s",intval($min_t%60)) ;
           $ret = $tmin.":".$tsec;
           break ;
       }
     return $ret ;
   } // Fim da funcao fmt_segundos
   /*--------------------------------------------------------------------------
    * Funcao fmt_telefone - Formata Nuemro do telefone
    * Recebe : Numero do telefone
    * Retorna: Numero formatado no tipo (xxx) xxxx-xxxx
    *--------------------------------------------------------------------------*/
   function fmt_telefone($params,$smarty){
      $numero = trim($params['a']) ;
      if (strlen($numero) < 8 || !is_numeric($numero))
          return $numero ;
      if (substr($numero,0,4) == "0800" || substr($numero,0,4) == "0300") {
         $numero =  substr($numero,0,4) . "-" . substr($numero,4) ;
      } else {
         $num = substr($numero,-4) ;
         $prefixo = substr($numero,-8,4) ;
         $ddd = trim(substr($numero,0,strlen($numero)-8)) ;
         if (strlen($ddd) > 3) {
            $dd  = substr($ddd,-2) ;
            $rst = substr($ddd,0,strlen($ddd)-2) ;
            $ddd = $rst." ($dd)" ;
         } elseif (strlen($ddd > 1))
            $ddd = "($ddd)" ;
         $numero = "$ddd $prefixo-$num" ;
      }
      return $numero ;
   }// Fim da funcao fmt_telefone
   /*--------------------------------------------------------------------------
    * Funcao fmt_cidade - Pesquiisa e exibe nome da cidade
    * Recebe : Numero do telefone
    *          Tipo Retorno: "" = Normal, so variavel $cidade
    *                        "A" = Array($cidade,$flag)
    *                                $flag = S/N - Se encontrou a cidade em CNL
    * Retorna: Nome da Cidade/Estado
    *--------------------------------------------------------------------------*/
   function fmt_cidade($params,$smarty)  {
      global $LANG, $db;
      $flag = "N" ;
      $tp_ret = ($smarty=="A") ? "A" : "" ;
      $telefone=trim($params['a']);
      if (strlen($telefone)==6)
         $prefixo = $telefone ;
      elseif (strlen($telefone) > 6) {
         $prefixo = substr($telefone,0,strlen($telefone)-4) ;
         if (strlen($prefixo) > 6)
            $prefixo=substr($prefixo,-6) ;
      } else
         $prefixo = $telefone ;
      if (!is_numeric($prefixo))
         $cidade = $LANG['unknown'];
      try {
         $sqlcidade = "select municipio,uf from cnl where prefixo = '$prefixo'" ;
         $rowcidade = $db->query($sqlcidade)->fetch();
         $cidade = ucfirst(strtolower($rowcidade['municipio']))."-".$rowcidade['uf'] ;
         //return array("cidade"=>$sqlcidade,"flag"=>"S") ;
         if (strlen($cidade) <= 3) {
            if (strlen($telefone) >= 8 && substr($prefixo,-4,1) > 6)
                $cidade = $LANG['cell'];
            else
                if ( strlen($telefone) >= 14 )
                   $cidade = $LANG['international'];
                else
                   $cidade = $LANG['local'];
         } else
            $flag = "S" ;  // Encontrou cidade na tabela CNL
      } catch (Exception $e) {
         $cidade = $LANG['error'];
      }
      if ($tp_ret == "A")
         return array("cidade"=>$cidade,"flag"=>$flag) ;
      else
         return $cidade ;
   } // Fim da Funcao fmt_cidade
   /*-----------------------------------------------------------------------------
    * Funcao fmt_telefone - Formata Nuemro do telefone
    * Recebe : Numero do telefone
    * Retorna: Numero formatado no tipo (xxx) xxxx-xxxx
    *----------------------------------------------------------------------------*/
   function fmt_gravacao($params,$smarty){
      $arquivo = $params['a'] ;
      $path_voz = $params['b'] ;
      $sufixo_voz = $params['c'] ;
      $comando = 'find ../' . $path_voz . ' -iname \*'.$arquivo."\*" . $sufixo_voz ;
     
      $arq_voz = exec($comando) ;
      // Se arquivo de voz existir e usuario tiver permissao para ve-lo ...
      
      if ( file_exists( $arq_voz ) && ver_permissao(81,"",True) ) {
         $ret=$arq_voz;
         //$ret = "<a href='".$arq_voz."' class='link_esp_1'>".$LANG['listen']."</a>";
      } else { 
         $ret = "N.D." ;

      }
      $smarty->assign('voz',$ret) ;
   } // Fim da Funcao fmt_gravacao

   /*-----------------------------------------------------------------------------
   *Funcao calcula_tarifa - Calcula Tarifa de uma Ligacao
   * Recebe: destino - campo 'dst' da tabela CDR
   *         duracao - campo 'billsec' da tabela CDR
   *         ccusto  - campo 'accountcode' da tabela CDR
   *         dt_chamada - campo 'calldate' da tabela CDR
   * Retorna: array (valor, cidade, estado, tp_fone, dst_fmtd)
   * ----------------------------------------------------------------------------*/
   function fmt_tarifa($param,$smarty) {
      global $db ;
      $destino = $param['a'] ;
      $duracao = $param['b'] ;
      $ccusto = $param['c'] ;
      $dt_chamada = $param['d'] ;
      $tipoccusto = ( $param['e'] ? $param['e'] : NULL );

      $dbg = 0; // Debug =- 1 retorna string com dados encontrados ;
      // Aceita somente destino de 8,11 ou 13 digitos
      $tn = strlen($destino) ;
      $duracao = (int) $duracao ;
      if ($duracao == 0)
         return 0 ;
      if ( $tn < 8  || !is_numeric($destino) || $tipoccusto == "E" ) {
         return "N.A." ;
         return $param;
      }

      if(substr(trim($destino),0,4) == "0800") {
          return "N.A." ;
      }

      // Separa o numero do telefone em 3 partes: telefone , ddd, e ddi
      $num_dst = substr($destino,-4) ;
      $prefixo = substr($destino,-8,4) ;
      $ddd_dst = substr($destino,-10,2) ;
      if ( $tn == 11 ) {
         $ddi_dst = "" ;
      } elseif  ($tn > 13 ) {
         $ddi_dst = "" ;
      }
      $dst_fmtd = "$ddi ($ddd_dst) $prefixo-$num_dst";

      if ($dbg==1) {
         $ret = "<hr>CCUSTOS=$ccusto == DATA=$dt_chamada == TEMPO=$duracao <br>DST = $dst_fmtd" ;
      }

      // Pesquisa cidades no CNL - Anatel
      $array_cidade = $this->fmt_cidade(array("a"=>$destino),"A") ;
      $cidade = $array_cidade['cidade'] ;
      if ($array_cidade['flag'] == "S") {
         $nome_cidade = substr($cidade,0,strlen($cidade)-3) ;
      }else {
         $nome_cidade = "" ;
      }
      if ($dbg==1) {
         $ret .= " // CIDADE = $cidade($nome_cidade)" ;
      }

      // Conecta no BD e pega dados da operadora, baseado no "accountcode"
      try {
         // Pega dados da Operadora
         $sql = "SELECT * FROM operadoras WHERE codigo = " ;
         $sql.= " (SELECT operadora FROM oper_ccustos  ";
         $sql.= " LEFT JOIN ccustos ON ccustos.codigo = oper_ccustos.ccustos " ;
         $sql.= " WHERE ccustos.codigo = '$ccusto')" ;
         $row = $db->query($sql)->fetch();
      } catch (Exception $e) {
         echo "1)".$LANG['error'].$e->getMessage() ;
      }
      $operadora = $row['codigo'];
      $tpm       = $row['tpm'];   // Tempo do 1o. minuto da operadora - em seg
      $tdm       = $row['tdm'];   // Tempo em segundos dos intervalos subsequentes
      $tbf       = $row['tbf'];   // Valor Padrao para Fixo
      $tbc       = $row['tbc'];   // Valor Padrao para Celular
      // Parametros Novos
      $vpf       = $row['vpf'];   // Valor de partida para Fixo
      $vpc       = $row['vpc'];   // Valor de partida para Celular.

      if ($dbg == 1) {
         $ret .= " // OPERADORA=$operadora , TPM=$tpm , TDM=$tdm , TBF=$tbf , TBC=$tbc, VPC=$vpc, VPF=$vpf" ;
      }
      if (trim($operadora) === "") {
         return "N.O.D" ;
      }
      /* Pega dados das tarifas conforme requisitos da operadora, ddi , ddd e prefixo)
         Condicoes do cadastro de tarifas - ATENCAO: Diferentes cidades tem o mesmo DDD
         1) ddd valido + prefixo valido - Tarifa especial para o prefixo
         2) ddd valido + prefixo=0000   - Tarifa generica para os prefixo do ddd
         3) ddi valido + ddd=valido + prefixo=0000 - Tarifa para determinada regiao do pais
         4) ddi valido + ddd=0 + prefixo=0000 - Tarifa generica para o pais
      */
      try {
         $cod_tarifa = "" ;
         $sql =  "SELECT * FROM tarifas WHERE (operadora = $operadora) " ;         
         // Pega tarifa GENERICA para a operadora - condicoes:
         // cidade=Selecionar, estado=--, ddd=0, prefixo=0000,ddi=55, pais=BRASIL
         $sqlg = $sql." AND cidade='Selecionar' AND estado='--' AND ddd=0 ";
         $sqlg .= " AND prefixo='0000' AND ddi=55 AND pais='BRASIL'" ;
         $rowg = $db->query($sqlg)->fetch();
         $cod_tarifa = $rowg['codigo'] ;
           
         // Se uma cidade foi encontrada na tabela CNL ...         
         if ($nome_cidade != "") {
            $sql .= " AND (cidade = \"$cidade\")" ;
         }
         
         if ($ddi_dst != "" && $ddi_dst != 55) {  // se nao for BRASIL, considera DDI 
             $sql .= " AND (ddi = $ddi_dst) " ;
             $sql .= " AND (prefixo = '$prefixo' OR prefixo = '0000') ";
             $sql .= " ORDER BY ddi,ddd,prefixo" ;
             $cod_tarifa = "" ;
             
             foreach ($db->query($sql) as $row){
                $cod_tarifa = $row['codigo'] ;
                // Varre registros encontrados e para na primeira condicao satisfeita
                if ($prefixo == $row['prefixo']) {  // um prefixo equivalente ...
                   break ;
                } elseif ($row['prefixo'] == "0000") {  // um prefixo GENERICO
                   break ;
                }
             }
         }

         if ($dbg==1) {
            $ret .= " // COD_TARIFA=$cod_tarifa" ;
         }
         
         // Se encontrou TARIFA equivalente, pega valores compativeis com CALLDATE      
         if ($cod_tarifa != "") {
            $sql = "SELECT * FROM tarifas_valores where codigo = $cod_tarifa" ;
            $sql.= " AND data >= '".substr($dt_chamada,0,10)."'";
            $sql.= " order by data" ;
            //echo $sql ;
                foreach ($db->query($sql) as $row){
                   $tbf = $row['vfix'] ;
                   $tbc = $row['vcel'] ;
                   $vpf = $row['vpf'] ;   // Valor de partida para Fixo
                   $vpc = $row['vpc'] ;   // Valor de partida para Celular.
                }
            
            if ($dbg==1) {
               $ret .= "<br> Tarifa Atualizada: TBF=$tbf , TBC=$tbc , VPF=$vpf , VPC=$vpc " ;
            }
         }
      } catch (Exception $e) {
         echo "2".$LANG['error'].$e->getMessage() ;
      }    

      // Calcula o tempo do primeiro minuto e desconta o tempo restante
      $tp_fone = (strlen($destino) >= 8 && substr($prefixo,-4,1) > 6) ? "C" : "F" ;
      $tpo_resta = $duracao - $tpm ;
      
      if ($tp_fone == 'C') {
         $tarifa_vp = $vpc ;   // Tarifa de Partida valida ara o tempo do primeiro minuto
         $tarifa = $tbc ;      // Tarifa para o restante dos tempo
      } else {
         $tarifa_vp = $vpf ;   // Tarifa de Partida valida ara o tempo do primeiro minuto
         $tarifa = $tbf;       // Tarifa para o restante dos tempo
      }
      // Calcula o valor dos demais minutos 
      $valor_prop = 0 ;
      
      if ($tpo_resta > 0) { 
         // Calcula o valor de cada fatia de tempo do minuto baseado na variavel
         // tdm = tempo dos demais minutos
         $tarifa_prop = ( $tarifa / (60/$tdm) ) ;  
         if ($dbg == 1) {
             echo "<br>Tarifa=$tarifa // Calc (1) = $tarifa_prop" ;
         }         
         // qualcula quantas fatias existem no tempo restante da ligacao
         $qtd_de_prop = ( (int)( $tpo_resta / $tdm ) +1 ) ;
         if ($dbg == 1) {
             echo " // Calc (2) = $qtd_de_prop" ;
         }
         // Calcula o valor do tempo restante da ligacao
         $valor_prop = ($qtd_de_prop * $tarifa_prop);
         if ($dbg == 1) {
             echo " // Calc (3) = $valor_prop" ;
         }
      } 
      $tarifa = $tarifa_vp + $valor_prop ;      
      if ($dbg == 1) {
         $ret .= " // TIPOFONE=$tp_fone // TARIFA_PARTIDA = $tarifa_vp // VALOR_PROP=$valor_prop ";
         $ret .= " ///// TARIFA FINAL ==== $tarifa";
         echo $ret."<br>" ;
         return number_format($tarifa,2,",",".") ;
      }

      if ($smarty == "A")
         return $tarifa;
      else
         return number_format($tarifa,2,",",".") ;
   }  
} // Fim da Classe formata
