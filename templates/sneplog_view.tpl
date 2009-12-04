{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_abandono.tpl - Relatorio de movimenta��o nas filas.
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Rafael Bozzetti <rafael@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
 {include file="cabecalho.tpl"}
 <div class="bgfiltro" style="height: 30px; border: 1px solid #A4A7AB;padding: 4px;">

        <span style="float:right;padding: 4px 10px 0 0;">
            <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='sneplog.php'" />
            <div class="buttonEnding"></div>
        </span>

        <span style="float:left;margin-top: 4px;">
            <strong>{$LANG.viewmode} :</strong>
            <input type="radio" name="modo" checked id="modo" value="normal" onclick="visualiza(this.value);"> {$LANG.viewnormal}
            <input type="radio" name="modo" id="modo" value="terminal" onclick="visualiza(this.value);"> {$LANG.viewconsole}
            <input type="radio" name="modo" id="modo" value="contraste" onclick="visualiza(this.value);"> {$LANG.viewcontrast}
        </span>
{if $type == "tail"}
        <span style="float:left;margin: 7px 0 0 20px; ">
            <strong>{$LANG.linenumbers} :</strong>
            <input type="text" name="n" value="30" class="campos" id="n" style="width: 30px;" />
        </span>
{/if}
 </div>
 
 {if $type == "log"}

 <div id="tail" style="border:1px solid #d2d2d2;padding: 3px;">

     {foreach from=$resultado key=k item=v}
           {$v} <br />
     {/foreach}

 </div>

 {else}
 
 <div id="tail" style="border:1px solid #d2d2d2;padding: 3px;"> </div>

    <script type="text/javascript">
    {literal}
        status();
        var periodicalExecuter = new PeriodicalExecuter(status, 1);

        function status() {
            new Ajax.Updater('tail', 'sneplogtail.php',
                        { parameters: { n: $F('n') } });

        }

    {/literal}
    </script>

 {/if}
<script type="text/javascript">
{literal}

        function visualiza(valor) {

            switch(valor) {
                case "normal":
                  $('tail').removeClassName('terminal');
                  $('tail').removeClassName('contraste');
                  $('tail').addClassName('normal');
                  break;
                case "terminal":
                  $('tail').removeClassName('normal');
                  $('tail').removeClassName('contraste');
                  $('tail').addClassName('terminal');
                  break;
                case "contraste":
                  $('tail').removeClassName('terminal');
                  $('tail').removeClassName('normal');
                  $('tail').addClassName('contraste');
                  break;
            }

        }

{/literal}
</script>


 { include file="rodape.tpl }
 