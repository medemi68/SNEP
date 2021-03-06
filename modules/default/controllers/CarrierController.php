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

/**
 * Carrier Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti
 */

class CarrierController extends Zend_Controller_Action {

    /**
     * List all Carrier
     */
    public function indexAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Carrier"),
            $this->view->translate("Carrier")
        ));

        $this->view->url = $this->getFrontController()->getBaseUrl() ."/". $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from('carrier')
                        ->where('fg_active = true')
                        ->order('ds_name');
                 
        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("`$field` like '%$query%'");
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->carrier = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/"+
                                "{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("id_carrier"      => $this->view->translate("Code"),
                        "ds_name"         => $this->view->translate("Name"));

        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . 
                           $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/"+
                              "{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url"     => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Add Carrier"),
                                          "css"     => "include"));
    }

    /**
     *  Add Carrier
     */
    public function addAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Carrier"),
            $this->view->translate("Add")
        ));

        $this->view->objSelectBox = "carrier";

        $xml = new Zend_Config_Xml( "modules/default/forms/carrier.xml" );
        $form = new Snep_Form($xml);
        
        $carrier = new Snep_Carrier_Manager();
        
        // Popula Centro de Custo
        $_idleCostCenter = Snep_Carrier_Manager::getIdleCostCenter();
                
        $idleCostCenter = array();
        foreach($_idleCostCenter as $idle) {
            $idleCostCenter[$idle['id_costcenter']] = $idle['id_costcenter'] .
                            " : ". $idle['cd_type'] ." - ". $idle['ds_name'];
        }
        
        if($idleCostCenter) {
            $form->setSelectBox($this->view->objSelectBox, 
                                $this->view->translate('Cost Center'), 
                                $idleCostCenter);
        }
        
        // Popula lista de operadoras
        $carrierList = array();
        foreach ($carrier->fetchAll() as $carrierRow) {        
        	$carrierList[$carrierRow->id_carrier] = $carrierRow->ds_name;
        }
        
        $carrierElement = $form->getElement('name');
        
        $carrierElement->setMultiOptions($carrierList)
                       ->removeDecorator('DtDdWrapper')
                       ->setRegisterInArrayValidator(false);
                              
        if($this->_request->getPost()) {
                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();
                $carrierId = $dados['name'];
                
                if( $form_isValid ) {
                	// Atualiza operadora
                    $dados['active'] = 1;
                    $carrier->save($carrierId, $dados);
       
                    $this->_redirect( $this->getRequest()->getControllerName() );
                }
        }
        $this->view->form = $form;
    }

    /**
     * Edit Carrier
     */
    public function editAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Carrier"),
            $this->view->translate("Edit")
        ));

        $this->view->objSelectBox = "carrier";
        $id = $this->_request->getParam("id");

        $xml = new Zend_Config_Xml("modules/default/forms/carrier.xml");
        
        $form = new Snep_Form($xml);        
        
        $carriers = new Snep_Carrier_Manager();
        $carrier = $carriers->find($id)->current();
        
        // Popula lista de operadoras
        $carrierList = array();
        foreach ($carriers->fetchAll() as $carrierRow) {        
            $carrierList[$carrierRow->id_carrier] = $carrierRow->ds_name;
        }
        
        $carrierElement = $form->getElement('name');
        
        $carrierElement->setMultiOptions($carrierList)
                       ->removeDecorator('DtDdWrapper')
                       ->setRegisterInArrayValidator(false);
        
        $name = $form->getElement('name');
        
        $name->setValue($carrier['id_carrier'])
             ->setAttrib('disabled', 'disabled');
             
        $form->getElement('ta')->setValue($carrier['vl_start']);
        $form->getElement('tf')->setValue($carrier['vl_fractionation']);        

        $_idleCostCenter = Snep_Carrier_Manager::getIdleCostCenter();

        $idleCostCenter = array();
        foreach($_idleCostCenter as $idle) {
            $idleCostCenter[$idle['id_costcenter']] = $idle['id_costcenter'] .
                            " : ". $idle['cd_type'] ." - ". $idle['ds_name'];
        }

        if (isset($id)) {
            $_selectedCostCenter = $carrier->findSnep_CostCenter_Manager();

            $selectedCostCenter = array();
            foreach($_selectedCostCenter as $selected) {
                $selectedCostCenter[$selected['id_costcenter']] = $selected['id_costcenter'] .
                            " : ". $selected['cd_type'] ." - ". $selected['ds_name'];
            }            
        }

        $form->setSelectBox( $this->view->objSelectBox,
                             $this->view->translate('Cost Center'),
                             $idleCostCenter,                             
                             $selectedCostCenter );

        $formId = new Zend_Form_Element_Hidden('id');
        $formId->setValue($id);
        
        $form->addElement($formId);

        if ($this->_request->getPost()) {
                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();
                
                if($form_isValid) {
                    $dados['active'] = 1;
                    
                	$manager = new Snep_Carrier_Manager();
                    $manager->save($id, $dados);
                    
                    $this->_redirect($this->getRequest()->getControllerName());
                }
        }
        $this->view->form = $form;
    }

    /**
     * Remove a Carrier
     */
    public function removeAction() {

       $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Carrier"),
            $this->view->translate("Delete")
       ));

       $id = $this->_request->getParam('id');
       
       // Reset all values
       $data = array( 'ta' => 0, 
                       'tf' => 0, 
                       'active' => 0 );
       
       $manager = new Snep_Carrier_Manager();
       $manager->save($id, $data);
       
       $this->_redirect( $this->getRequest()->getControllerName() );

    }
       
}
