<?php

class Snep_Form_Element_Trunk extends Zend_Form_Element_Select {
    public function __construct($spec, $options = null) {
        foreach (PBX_Trunks::getAll() as $trunk) {
            $data[$trunk->getId()] = $trunk->getName();
            Zend_Debug::dump($data);
        }
        Zend_Debug::dump($data);
        exit(1);
        if ($data != null){
            $this->addMultiOptions($data);
        }else{
            $this->addMultiOption(null, '');
        }
        
        parent::__construct($spec, $options);
    }
}
