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
<table cellspacing="0" cellpadding="0" border="0" align="center"  >   
   <thead>
      <tr>
         <td class="cen" width="15%">{$LANG.realname}</td>
         <td class="esq">{$LANG.aliasname}</td>
         <td class="cen" colspan="3" width="20%">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=alias loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td>{$DADOS[alias].cod}</td>
         <td>{$DADOS[alias].realname}</td>
         <td>{$DADOS[alias].aliasname}</td>
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="../src/troncos_aliases.php?acao=alterar&amp;cod={$DADOS[alias].cod}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center">
            <acronym title="{$LANG.exclude}">
               <a href="../src/troncos_aliases.php?acao=excluir&amp;cod={$DADOS[alias].cod}"><img src="../imagens/delete.png" alt="{$LANG.exclude}" /></a>
            </acronym>          
         </td>
      </tr>
   {/section}
</table>
{ include file="rodape.tpl }