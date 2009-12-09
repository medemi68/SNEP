{*
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
 *}
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}

{config_load file="../includes/setup.conf" section="cores"}
<table align="center" >   
   <thead>
      <tr>
         <td class="cen" width="10px">{$LANG.id}</td>
         <td class="cen" width="15%">{$LANG.name}</td>
         <td class="cen">{$LANG.city}</td>
         <td class="cent">{$LANG.state}</td>
         <td class="cen">{$LANG.phone}</td>
         <td class="cent">{$LANG.cell}</td>
         {if $VIEW_AIE === true}
            <td class="cen" colspan="2" >{$LANG.actions}</td>
         {/if}
      </tr>
   </thead>
   {section name=contatos loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[contatos].id}</td>
         <td>{$DADOS[contatos].name}</td>
         <td class="cen">{$DADOS[contatos].city}</td>
         <td class="cen">{$DADOS[contatos].state}</td>
         <td class="dir">{formata->fmt_telefone a=$DADOS[contatos].phone_1} </td>
         <td class="dir">{formata->fmt_telefone a=$DADOS[contatos].cell_1} </td>
         {if $VIEW_AIE === true}
            <form name="acao" method="post" action="../src/cont_names.php">
            <td align="center" valign="middle" width="30px">
                <acronym title="{$LANG.change}">
                   <a href="../src/cont_names.php?acao=alterar&amp;id={$DADOS[contatos].id}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
                </acronym>
            </td>      
            <td valign="middle" align="center" width="30px">
                <acronym title="{$LANG.exclude}">
                   <a href="../src/cont_names.php?acao=excluir&amp;id={$DADOS[contatos].id}"><img src="../imagens/delete.png" alt="{$LANG.exclude}" /></a>
                </acronym>    
             </td>
            <input type="hidden" name="id" value="{$DADOS[contatos].id}" />
            </form>
         {/if}
       </tr>
      </tr>
   {/section}          
</table>
{ include file="rodape.tpl }
