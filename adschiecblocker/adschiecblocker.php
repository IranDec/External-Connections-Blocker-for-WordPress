<?php
/**
 * 2007-2023 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    Mohammad Babaei <adschi.com>
 *  @copyright 2023 Mohammad Babaei
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdschiEcBlocker extends Module
{
    public function __construct()
    {
        $this->name = 'adschiecblocker';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Mohammad Babaei';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Adschi External Connections Blocker');
        $this->description = $this->l('Blocks external HTTP requests and Google Fonts to improve website speed for servers in restricted networks (like Iran).');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (!parent::install() ||
            !Configuration::updateValue('ADSCHI_BLOCK_EXTERNAL', 1) ||
            !Configuration::updateValue('ADSCHI_BLOCK_GOOGLE_FONTS', 1) ||
            !Configuration::updateValue('ADSCHI_CUSTOM_WHITELIST', '') ||
            !Configuration::updateValue('ADSCHI_CUSTOM_BLACKLIST', '')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('ADSCHI_BLOCK_EXTERNAL') ||
            !Configuration::deleteByName('ADSCHI_BLOCK_GOOGLE_FONTS') ||
            !Configuration::deleteByName('ADSCHI_CUSTOM_WHITELIST') ||
            !Configuration::deleteByName('ADSCHI_CUSTOM_BLACKLIST')
        ) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitAdschiEcBlocker')) {
            Configuration::updateValue('ADSCHI_BLOCK_EXTERNAL', (int)Tools::getValue('ADSCHI_BLOCK_EXTERNAL'));
            Configuration::updateValue('ADSCHI_BLOCK_GOOGLE_FONTS', (int)Tools::getValue('ADSCHI_BLOCK_GOOGLE_FONTS'));
            Configuration::updateValue('ADSCHI_CUSTOM_WHITELIST', Tools::getValue('ADSCHI_CUSTOM_WHITELIST'));
            Configuration::updateValue('ADSCHI_CUSTOM_BLACKLIST', Tools::getValue('ADSCHI_CUSTOM_BLACKLIST'));

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . $this->renderForm() . $this->renderFooter();
    }

    protected function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Block External Requests'),
                        'name' => 'ADSCHI_BLOCK_EXTERNAL',
                        'is_bool' => true,
                        'desc' => $this->l('Blocks functions like file_get_contents for external domains.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Block Google Fonts'),
                        'name' => 'ADSCHI_BLOCK_GOOGLE_FONTS',
                        'is_bool' => true,
                        'desc' => $this->l('Removes Google Fonts (fonts.googleapis.com, fonts.gstatic.com) from front-end and back-end to increase speed.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Custom Whitelist'),
                        'name' => 'ADSCHI_CUSTOM_WHITELIST',
                        'desc' => $this->l('Domains to allow. Enter one domain per line. (e.g. api.mybank.com)'),
                        'cols' => 40,
                        'rows' => 10,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Custom Blacklist'),
                        'name' => 'ADSCHI_CUSTOM_BLACKLIST',
                        'desc' => $this->l('Domains to force block. Enter one domain per line.'),
                        'cols' => 40,
                        'rows' => 10,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAdschiEcBlocker';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function getConfigFormValues()
    {
        return array(
            'ADSCHI_BLOCK_EXTERNAL' => Configuration::get('ADSCHI_BLOCK_EXTERNAL', 1),
            'ADSCHI_BLOCK_GOOGLE_FONTS' => Configuration::get('ADSCHI_BLOCK_GOOGLE_FONTS', 1),
            'ADSCHI_CUSTOM_WHITELIST' => Configuration::get('ADSCHI_CUSTOM_WHITELIST', ''),
            'ADSCHI_CUSTOM_BLACKLIST' => Configuration::get('ADSCHI_CUSTOM_BLACKLIST', ''),
        );
    }

    protected function renderFooter()
    {
        $version = $this->version;
        $html = '
        <div class="panel">
            <div class="panel-footer" style="text-align: center; font-size: 14px; padding: 15px;">
                <p>نسخه ماژول: <strong>' . $version . '</strong></p>
                <p>نویسنده: <strong>Mohammad Babaei</strong></p>
                <p>برای مشاوره و ساخت ماژول اختصاصی درخواست بدهید: <a href="https://adschi.com" target="_blank" style="text-decoration: underline; color: #25b9d7;">adschi.com</a></p>
            </div>
        </div>';

        return $html;
    }
}
