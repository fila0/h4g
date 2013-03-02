<?php

class Fila0 extends Module {

    function __construct() {

        $version_mask = explode('.', _PS_VERSION_, 3);
        $version_test = $version_mask[0] > 0 && $version_mask[1] > 3;
        $this->name = 'fila0';
        $this->tab = $version_test ? 'others' : 'Fila0 Donaciones';

        if ($version_test)
            $this->author = 'Fila0 Development';
            $this->version = '0.1';
            parent::__construct();
            $this->displayName = $this->l('Fila0 Donaciones');
            $this->description = $this->l('Activa las donaciones a Fila0 en tu Prestashop');
    }

    public function getContent() {

        if (Tools::isSubmit('submit')) {
            Configuration::updateValue($this->name.'_api_key', Tools::getValue('send_api_key'));
            Configuration::updateValue($this->name.'_secreto_compartido', Tools::getValue('secreto_compartido'));
        }

        $this->_displayForm();

        return $this->_html;
    }

    private function _displayForm() {

        $api_key_bd = Configuration::get($this->name.'_api_key', $id_lang = NULL);
        $secreto_compartido_bd = Configuration::get($this->name.'_secreto_compartido', $id_lang = NULL);

        $this->_html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
                                        <label>'.$this->l('Introduce tu Fila0 API Key').'</label>
                                        <div class="margin-form">
                                            <input type="text" name="send_api_key" value="'.$api_key_bd.'" />
                                        </div>

                                        <label>'.$this->l('Introduce tu secreto compartido').'</label>
                                        <div class="margin-form">
                                            <input type="text" name="secreto_compartido"  value="'.$secreto_compartido_bd.'" />
                                        </div>
                                        <input type="submit" name="submit" value="'.$this->l('Guardar cambios').'" class="button" />
                                        </form>';
    }

    public function install() {

        parent::install();

        if (!$this->registerHook('footer'))

        return false;
    }

    public function hookFooter() {

        return '<div class="block&quot"<h4>Fila0 API Key'. Configuration::get($this->name.'_api_key') . '</h4><br />'
                    .'<h4>Secreto Compartido'.Configuration::get($this->name.'_secreto_compartido') .'</div>';
    }
}

?>
