<?php
/**
 * Discar para Tronco
 *
 * Ação de Regra de Negócio do snep que controla a lógica de discagem para
 * troncos.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_DiscarTronco extends PBX_Rule_Action {

    /**
     * @var Internacionalização
     */
    private $i18n;

    /**
     * Construtor
     * @param array $config configurações da ação
     */
    public function __construct() {
        $path = Zend_Registry::get('config');
        // Especificando caminho para arquivo de tradução
        $this->i18n = new Zend_Translate('gettext', $path->system->path->base . "/lang/actions/" . get_class($this) . "/" . "pt_BR.mo" , 'pt_BR');
    }

    /**
     * Retorna o nome da Ação. Geralmente o nome da classe.
     *
     * @return Nome da Ação
     */
    public function getName() {
        return $this->i18n->translate("Discar para Tronco");
    }

    /**
     * Retorna o numero da versão da classe.
     *
     * @return Versão da classe
     */
    public function getVersion() {
        return "1.0";
    }

    /**
     * Envia email de alerta sobre uso desse tronco
     *
     * @param array string $adresses
     * @param array $informations, informações a serem anexadas ao email.
     */
    private function sendMailAlert($addresses, $informations) {
        $log = Zend_Registry::get('log');
        $config = Zend_Registry::get('config');
        $mail = new Zend_Mail('UTF-8');

        $mail->setFrom($config->system->mail, 'SNEP PBX');

        if(is_array($addresses)) {
            foreach ($addresses as $address) {
                $mail->addTo(trim($address));
            }
        }
        else {
            $mail->addTo($addresses);
        }

        $mail->setSubject('[snep] Alerta de uso de Tronco Redundante');

        $tronco = PBX_Trunks::get($this->config['tronco']);
        $msg = "Sr. Administrador,\n\tEste é um alerta de que o SNEP identificou o uso de um tronco redudante para uma chamada.\n";
        $msg .= "Alerta para tronco $tronco, as seguintes informações foram registradas pelo sistema:\n";

        foreach ($informations as $info => $message) {
            $msg .= "$info: $message\n";
        }

        $mail->setBodyText($msg);
        $log->info("Enviando email de alerta para {$this->config['alertEmail']}");
        $mail->send();
    }

    /**
     * Seta as configurações da ação.
     *
     * @param array $config configurações da ação
     */
    public function setConfig($config) {

        if( !isset($config['tronco']) ) {
            throw new PBX_Exception_BadArg("Trunk is required");
        }

        $config['dial_timeout'] = (isset($config['dial_timeout'])) ? $config['dial_timeout'] : '60';
        $config['dial_flags']   = (isset($config['dial_flags'])) ? $config['dial_flags'] : "TWK";
        $config['alertEmail']   = (isset($config['alertEmail'])) ? $config['alertEmail'] : "";
        $this->config = $config;
    }

    /**
     * Retorna uma breve descrição de funcionamento da ação.
     * @return Descrição de funcionamento ou objetivo
     */
    public function getDesc() {
        return $this->i18n->translate("Disca para um tronco cadastrado no banco de dados do SNEP");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $i18n = $this->i18n;

        $tronco          = (isset($this->config['tronco']))?"<value>{$this->config['tronco']}</value>":"";
        $dial_timeout    = (isset($this->config['dial_timeout']))?"<value>{$this->config['dial_timeout']}</value>":"";
        $dial_flags      = (isset($this->config['dial_flags']))?"<value>{$this->config['dial_flags']}</value>":"";
        $dial_limit      = (isset($this->config['dial_limit']))?"<value>{$this->config['dial_limit']}</value>":"";
        $dial_limit_warn = (isset($this->config['dial_limit_warn']))?"<value>{$this->config['dial_limit_warn']}</value>":"";
        $omit_kgsm       = (isset($this->config['omit_kgsm']))?"<value>{$this->config['omit_kgsm']}</value>":"";
        $alertEmail      = (isset($this->config['alertEmail']))?"<value>{$this->config['alertEmail']}</value>":"";

        return <<<XML
<params>
    <tronco>
        <id>tronco</id>
        $tronco
    </tronco>

    <int>
        <id>dial_timeout</id>
        <label>{$i18n->translate("Dial Timeout")}</label>
        <unit>{$i18n->translate("segundos")}</unit>
        <size>2</size>
        <default>60</default>
        $dial_timeout
    </int>

    <int>
        <id>dial_limit</id>
        <default>0</default>
        <label>{$i18n->translate("Limite da chamada")}</label>
        <size>4</size>
        <unit>{$i18n->translate("segundos")}</unit>
        $dial_limit
    </int>

    <int>
        <id>dial_limit_warn</id>
        <default>0</default>
        <label>{$i18n->translate("Iniciar alerta faltando")}</label>
        <size>4</size>
        <unit>{$i18n->translate("segundos")}</unit>
        $dial_limit_warn
    </int>

    <string>
        <id>dial_flags</id>
        <label>{$i18n->translate("Dial Flags")}</label>
        <size>10</size>
        <default>TWK</default>
        $dial_flags
    </string>

    <boolean>
        <id>omit_kgsm</id>
        <default>false</default>
        <label>{$i18n->translate("Omitir origem (somente KGSM)")}</label>
        $omit_kgsm
    </boolean>

    <string>
        <id>alertEmail</id>
        <label>{$i18n->translate("Emails para alerta")}</label>
        <size>50</size>
        $alertEmail
    </string>
</params>
XML;
    }

    /**
     * Executa a ação. É chamado dentro de uma instancia usando AGI.
     *
     * @param AGI $asterisk
     * @param Asterisk_AGI_Request $request
     */
    public function execute($asterisk, $request) {
        $log = Zend_Registry::get('log');

        $tronco = PBX_Trunks::get($this->config['tronco']);

        // Montando as Flags para limite na ligação
        $flags = $this->config['dial_flags'];
        if(isset($this->config['dial_limit']) && $this->config['dial_limit'] > 0) {
            $flags .= "L(" . $this->config['dial_limit'];
            if( isset($this->config['dial_limit_warn']) && $this->config['dial_limit_warn'] > 0) {
                $flags .= ":" . $this->config['dial_limit_warn'];
            }
            $flags .= ")";
        }

        $postfix = ( isset($this->config['omit_kgsm']) && $this->config['omit_kgsm'] == "true" ) ? "/orig=restricted" : "";

        if($tronco->getInterface() instanceof PBX_Asterisk_Interface_SIP_NoAuth || $tronco->getInterface() instanceof PBX_Asterisk_Interface_IAX2_NoAuth)
            $destiny = $tronco->getInterface()->getTech() . "/" . $request->destino . "@" . $tronco->getInterface()->getHost();
        else
            $destiny = $tronco->getInterface()->getCanal() . "/" . $request->destino . $postfix;

        $log->info("Discando para $request->destino atraves do tronco {$tronco->getName()}($destiny)");

        $dialstatus = $asterisk->get_variable("DIALSTATUS");
        $lastdialstatus = $dialstatus['data'];

        $log->debug("Dial($destiny, {$this->config['dial_timeout']}, $flags)");
        $asterisk->exec_dial($destiny, $this->config['dial_timeout'], $flags);

        $dialstatus = $asterisk->get_variable("DIALSTATUS");
        $log->debug("DIALSTATUS: " . $dialstatus['data']);

        // Enviar email de alerta.
        if(isset($this->config['alertEmail']) && $this->config['alertEmail'] != "") {
            $informations = array(
                'Regra'             => $this->getRule(),
                'Hora da chamada'   => date('H:i'),
                'Data da chamada'   => date('d/m/Y'),
                'Origem Original'   => $request->getOriginalCallerid(),
                'Destino original'  => $request->getOriginalExtension(),
                'Origem'            => $request->origem,
                'Destino'           => $request->destino,
                'Status da Ligação' => $dialstatus['data']
            );

            if($lastdialstatus != "") {
                $lastdialaction = null;
                foreach ($this->getRule()->getAcoes() as $action) {
                    if($action == $this) {
                        break;
                    }
                    $cfg = $action->getConfigArray();
                    if($action instanceof PBX_Rule_Action_DiscarTronco) {
                        $lastdialaction = Snep_Troncos::get($cfg['tronco']);
                    }
                    else if($action instanceof PBX_Rule_Action_DiscarRamal) {
                        $lastdialaction = $cfg['ramal'];
                    }
                }
                $informations["\nHouve uma tentativa de ligação anterior"] = "";
                $informations["Ultima ligação para"] = $lastdialaction;
                $informations["Estado da ultima ligação"] = $lastdialstatus;
            }

            $this->sendMailAlert(explode(",",$this->config['alertEmail']), $informations);
        }

        switch($dialstatus['data']) {
            case 'ANSWER':
            case 'CANCEL':
                throw new PBX_Rule_Action_Exception_StopExecution("Fim da ligacao");
                break;
            case 'NOANSWER':
            case 'BUSY':
                $log->info($dialstatus['data'] . " ao discar para $request->destino pelo tronco $tronco");
                break;
            default:
                $log->err($dialstatus['data'] . " ao discar para $request->destino pelo tronco $tronco");
        }
    }
}
