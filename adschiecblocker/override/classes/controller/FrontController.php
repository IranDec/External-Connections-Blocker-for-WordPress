<?php
/**
 * Override FrontController to remove Google Fonts
 */

class FrontController extends FrontControllerCore
{
    public function display()
    {
        if (Configuration::get('ADSCHI_BLOCK_GOOGLE_FONTS')) {
            ob_start();
            parent::display();
            $html = ob_get_clean();

            // Remove google fonts links
            $html = preg_replace('/<link[^>]*href=["\'](https?:)?\/\/fonts\.(googleapis|gstatic)\.com[^>]*>/i', '', $html);
            $html = preg_replace('/<style[^>]*>.*?@import url\(["\']?(https?:)?\/\/fonts\.googleapis\.com.*?<\/style>/is', '', $html);

            echo $html;
        } else {
            parent::display();
        }
    }
}
