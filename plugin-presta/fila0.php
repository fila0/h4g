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

        if (!$this->registerHook('orderConfirmation') || !$this->registerHook('footer') || !$this->registerHook('shoppingCart') || !$this->registerHook('shoppingCartExtra'))

        return false;
    }

    public function hookShoppingCart() {

        $url = "http://localhost:8888/prestashop/index.php";

        $static_token = Tools::getToken(false);

        $html = ""
                        ."<div id=\"tablaDonacion\"><table width=\"100%\" border=\"0\" style=\"border:1px solid black;\">"
                        ."<tr>"
                        ."<td width=\"5%\"><center><img src=\"/prestashop/img/p/3/2/32-small_default.jpg\"  style=\"margin-left: 10px;\" alt=\"Fila0\" /></td>"
                        ."<td width=\"5%\"><center><input type=\"checkbox\" id=\"checkDonacion\" value=\"\" /></td>"
                        ."<td width=\"90%\" style=\"font-size: 13px;\">Quiero donar <b>1€</b> al proyecto <b>Lucha contra el VIH</b> en Zimbabwe de <b>Médicos sin Fronteras</b>.<br />"
                        ."<div style=\"padding-top: 5px;\">Más info en <a href=\"http://www.filacero.org\" target=\"_blank\">http://www.filacero.org</a></div></td>"
                        ."</tr>"
                        ."</table>"
                        ."<p></p>"
                        ."</div>"
                        ."<script>"
                        ."function enviarDonacion() {"
                            ."$.post(\"index.php\", {"
                                ."controller: \"cart\", "
                                ."add: \"1\", "
                                ."ajax: \"false\", "
                                ."qty: \"1\", "
                                ."id_product: \"12\", "
                                ."token: \"".$static_token."\""
                            ."},function() {"
                                //."$('#tablaDonacion').hide();"
                                ."window.location.reload();"
                            ."});"
                        ."}"
                        ."</script>"
                        ."<script>"
                            ."$('#checkDonacion').bind('change', function () {"
                                ."if ($(this).is(':checked'))"
                                    ."enviarDonacion();"
                                ."else"
                                    ."enviarDonacion();"
                            ."});"
                        ."</script>";

        return $html;

    }

    public function hookOrderConfirmation() {

        $api_key_bd = Configuration::get($this->name.'_api_key', $id_lang = NULL);
        $secreto_compartido_bd = Configuration::get($this->name.'_secreto_compartido', $id_lang = NULL);
        $transaction_id = "7";

        $id_cart = intval(Tools::getValue('id_cart', 0));
        $id_module = intval(Tools::getValue('id_module', 0));
        $id_order = Order::getOrderByCartId(intval($id_cart));

        //$url = "http://localhost:8888/filacero/web/insertDonation/?apikey=".$api_key_bd."&apisecret=".$secreto_compartido_bd."&format=json&import=1&currency=eur&transactionid=".$id_order;

        $jquery = "<script>"
            ."$(document).ready(function() {"
                ."enviarDonacion();"
            ."});"
            ."function enviarDonacion() {"
                ."$.post(\"http://localhost:8888/filacero/web/insertDonation/\", {"
                    ."apikey: \"".$api_key_bd."\", "
                    ."apisecret: \"".$secreto_compartido_bd."\", "
                    ."format: \"json\", "
                    ."import: \"1\", "
                    ."currency: \"eur\", "
                    ."transactionid: \"".$id_order."\""
                ."},function() {"
                    //."alert('¡Donado!');"
                ."});"
            ."}"
        ."</script>";

        $html = $jquery;

        return $html;

    }

    /*public function hookFooter() {

        return '<div class="block&quot"<h4>Fila0 API Key'. Configuration::get($this->name.'_api_key') . '</h4><br />'
                    .'<h4>Secreto Compartido'.Configuration::get($this->name.'_secreto_compartido') .'</div>';
    }*/

    /*public function hookShoppingCartExtra() {

        return '<div class="block&quot"<h4>Fila0 API Key'. Configuration::get($this->name.'_api_key') . '</h4><br />'
                    .'<h4>Secreto Compartido'.Configuration::get($this->name.'_secreto_compartido') .'</div>';
    }*/
}

?>
