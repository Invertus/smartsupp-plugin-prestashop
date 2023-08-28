<?php
/**
 * Smartsupp Live Chat integration module.
 *
 * @author    Smartsupp <vladimir@smartsupp.com>
 * @copyright 2016 Smartsupp.com
 * @license   GPL-2.0+
 * @package   Smartsupp
 * @link      http://www.smartsupp.com
 *
 * Plugin Name:       Smartsupp Live Chat
 * Plugin URI:        http://www.smartsupp.com
 * Description:       Adds Smartsupp Live Chat code to PrestaShop.
 * Version:           2.2.0
 * Text Domain:       smartsupp
 * Author:            Smartsupp
 * Author URI:        http://www.smartsupp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

use \Smartsupp\Auth\Api;

class AdminSmartsuppAjaxController extends ModuleAdminController
{
    public $ssl = true;
    private $partnerKey = 'h4w6t8hln9';

    public function init()
    {
        $api = new Api();
                
        switch (Tools::getValue('action')) {
            case 'login':
                $response = $api->login([
                    'email' => Tools::getValue('email'),
                    'password' => Tools::getValue('password'),
                    'platform' => 'Prestashop ' . _PS_VERSION_,
                ]);

                if (!isset($response['account']['key'])) {
                    $response['error'] = isset($response['error']) ? $response['error'] : $this->module->l('Unknown Error Occurred');
                    $response['message'] = isset($response['message']) ? $response['message'] : $this->module->l('Unknown Error Occurred');
                } else {
                    Configuration::updateValue('SMARTSUPP_KEY', $response['account']['key']);
                    Configuration::updateValue('SMARTSUPP_EMAIL', Tools::getValue('email'));
                }
                break;
            case 'create':
                $response = $api->create([
                    'email' => Tools::getValue('email'),
                    'password' => Tools::getValue('password'),
                    'partnerKey' => $this->partnerKey,
                    'consentTerms' => 1,
                    'platform' => 'Prestashop ' . _PS_VERSION_,
                ]);

                if (!isset($response['account']['key'])) {
                    $response['error'] = isset($response['error']) ? $response['error'] : $this->module->l('Unknown Error Occurred');
                    $response['message'] = isset($response['message']) ? $response['message'] : $this->module->l('Unknown Error Occurred');
                } else {
                    Configuration::updateValue('SMARTSUPP_KEY', $response['account']['key']);
                    Configuration::updateValue('SMARTSUPP_EMAIL', Tools::getValue('email'));
                }
                break;
            case 'deactivate':
                Configuration::updateValue('SMARTSUPP_KEY', '');
                Configuration::updateValue('SMARTSUPP_EMAIL', '');
                break;
        }
                
        if (isset($response) && isset($response['error'])) {
            Configuration::updateValue('SMARTSUPP_KEY', '');
            Configuration::updateValue('SMARTSUPP_EMAIL', '');
        }

        header('Content-Type: application/json');

        $responseData = [
            'key' => Configuration::get('SMARTSUPP_KEY'),
            'email' => Configuration::get('SMARTSUPP_EMAIL'),
        ];

        if (isset($response['error'])) {
            $responseData['error'] = $response['error'];
        }

        if (isset($response['message'])) {
            $responseData['message'] = $response['message'];
        }

        if (isset($response['hint'])) {
            $responseData['hint'] = $response['hint'];
        }

        die(json_encode($responseData));
    }
}
