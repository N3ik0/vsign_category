<?php 

if(!defined('_PS_VERSION_')){
     exit;
}

class Vsign_category extends Module {

    public function __construct(){

        $this->name = 'vsign_category'; 
        $this->tab = 'front_office_features'; 
        $this->version = '1.0.0'; 
        $this->author = 'Valentin Brenner'; 
        $this->need_instance = 0; 
        $this->bootstrap = true; 
        parent::__construct(); 

        $this->displayName = $this->l('Show category');
        $this->description = $this->l('Can display category in the home page');

        $this->templateFile = 'module:vsign_category/views/templates/hook/nav.tpl'; 
    }


    public function install(){
        if(!parent::install() ||
            !$this->registerHook('displayTop') ||
            !$this->registerHook('displayHeader')
            ){
                return false;
            }
            return true;
    }

    public function hookActionFrontControllerSetMedia(){
        $this->context->controller->registerStylesheet('module-vsign_category', 'modules/'.$this->name.'/views/css/vsign_category.css');
    }

    public function hookDisplayHome()
    {
        $this->context->controller->registerStyleSheet('module-vsign_category', 'modules/'.$this->name.'/views/css/vsign_category.css'
        );
    }

    public function hookDisplayHeader() {
        $categories = Category::getNestedCategories($this->context->language->id, true, false);
        $this->context->smarty->assign('categories', $categories);
    }

    public function hookDisplayTop() {

        $categoryIds = [];

        for ($i = 1; $i <= 4; $i++) {
            $categoryIds[$i] = Configuration::get('SELECT_OPTION_' . $i);
        }
    

        $categoriesDetails = [];

        foreach ($categoryIds as $id) {
            if ($id) {
                $category = new Category($id, $this->context->language->id);
                if (Validate::isLoadedObject($category)) {
                    $categoriesDetails[] = array(
                        'id' => $category->id,
                        'name' => $category->name,
                        'link' => $this->context->link->getCategoryLink($category),
                    );
                }
            }
        }
    
        $this->context->smarty->assign('categoriesDetails', $categoriesDetails);
    
        return $this->display(__FILE__, 'nav.tpl');
    }

    public function getContent(){

    $output = null;
    
    if(Tools::isSubmit('submit'.$this->name)){
        $isValid = true;
        
        for ($i = 1; $i <= 4; $i++) {
            $select_option = strval(Tools::getValue('SELECT_OPTION_' . $i));
            
            if (!$select_option || !Validate::isUnsignedInt($select_option)) {
                $isValid = false;
                $output .= $this->displayError($this->l('Invalid selection for category ' . $i));
                break;
            } else {
                Configuration::updateValue('SELECT_OPTION_' . $i, $select_option);
            }
        }
    
        if ($isValid) {
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
    }
    
    return $output.$this->displayForm();
    }

    public function displayForm() {
    // Get all categories
    $categories = Category::getCategories($this->context->language->id, true, false);
    $options = array();


    foreach ($categories as $category) {
        $options[] = array(
            'id_option' => $category['id_category'],
            'name' => $category['name'] 
        );
    }

        $inputs = [];
        for ($i = 1; $i <= 4; $i++) {
        $inputs[] = array(
            'type' => 'select',
            'label' => $this->l('Select Category ' . $i),
            'name' => 'SELECT_OPTION_' . $i,
            'options' => array(
                'query' => $options,
                'id' => 'id_option',
                'name' => 'name'
            )
        );
        }
    
        $fieldsForm[0]['form'] = array(
        'legend' => array(
            'title' => $this->l('Settings'),
        ),
        'input' => $inputs,
        'submit' => array(
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right'
        )
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submit'.$this->name;
    
        for ($i = 1; $i <= 4; $i++) {
            $helper->fields_value['SELECT_OPTION_' . $i] = Configuration::get('SELECT_OPTION_' . $i);
        }

        return $helper->generateForm($fieldsForm);
    }
}