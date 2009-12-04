{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: debugger.tpl - template para o debugger de dialplan
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Henrique Grolli Bassotto <henrique@opens.com.br>            
 * ---------------------------------------------------------------------------- *}
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
  <head>
   <link rel="icon" href="favicon.ico" type="images/x-icon" />
   <link rel="shortcut icon" href="favicon.ico" />
   <link rel="stylesheet" href="../css/{$CSS_TEMPL}.css" type="text/css" />
   <link rel="stylesheet" href="../css/debugger.css" type="text/css" />
   
   <meta http-equiv="Content-Script-Type" content="text/javascript" />
   <meta http-equiv="imagetoolbar" content="false" />
   <META http-equiv="Expires" content="Fri, 25 Dec 1980 00:00:00 GMT" />
   <META http-equiv="Last-Modified" content="{php}gmdate('D, d M Y H:i:s'){/php} GMT" />
   <META http-equiv="Cache-Control" content="no-cache, must-revalidate" />
   <META http-equiv="Pragma" content="no-cache" />
  </head>
  <table border="0" cellpadding="0" cellspacing="0">
     {if $deb_ERROR == "norule"}
        <tr>
           <td>
              <p class="error">{$LANG.deb_norule}</p>
           </td>
        </tr>
     {/if}
     {if $deb_result}
        <tr>
           <td class="campos">
              <div id="titulo">
              <strong>{$LANG.conflicts}</strong>
              </div>
           </td>
        </tr>
        <tr>
           <td>
              <strong>{$LANG.deb_caller}</strong>: {$deb_input.caller}
              &nbsp;&nbsp;
              <strong>{$LANG.deb_dst}</strong>: {$deb_input.dst}
              &nbsp;&nbsp;
              <!--<strong>{$LANG.deb_time}</strong>: {$deb_input.time}-->
           </td>
        </tr>
        {section name=rule loop=$deb_result}
           <tr  class="rule {$deb_result[rule].state}">
              <td>
                 <strong>{$LANG.rule}:</strong> {$deb_result[rule].desc}
                 <br />
                 <strong>{$LANG.deb_caller}:</strong> {$deb_result[rule].caller}
                 &nbsp;&nbsp;
                 <strong>{$LANG.deb_dst}:</strong> {$deb_result[rule].dst}
                 <br />
                 <strong>{$LANG.deb_valid}:</strong> {$deb_result[rule].valid}
                 <br  />
                 <strong>{$LANG.deb_actions_torun}</strong>:<br />
                 {section name=action loop=$deb_result[rule].actions}
                    &nbsp;-&nbsp;{$deb_result[rule].actions[$smarty.section.action.index]}<br />
                 {/section}
              </td>
           </tr>
        {/section}
        <tr>
           <td>
              <ul id="legenda">
                 <li class="torun">{$LANG.deb_leg_col_torun}</li>
                 <li class="outdated">{$LANG.deb_leg_col_outdated}</li>
                 <li class="ignored">{$LANG.deb_leg_col_ignored}</li>
              </ul>
           </td>
        </tr>
     {/if}
  </table>
</html>