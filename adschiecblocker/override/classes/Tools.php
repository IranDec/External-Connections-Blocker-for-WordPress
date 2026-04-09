<?php
/**
 * Override Tools to block external requests.
 */

class Tools extends ToolsCore
{
    /**
     * Override file_get_contents to block external calls if configured.
     */
    public static function file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 5)
    {
        if (Configuration::get('ADSCHI_BLOCK_EXTERNAL')) {
            // Check if it's a URL
            if (preg_match('/^https?:\/\//i', $url)) {
                $host = parse_url($url, PHP_URL_HOST);
                if ($host) {
                    // Check Whitelist
                    $whitelist_raw = Configuration::get('ADSCHI_CUSTOM_WHITELIST');
                    $whitelist = array_filter(array_map('trim', explode("\n", $whitelist_raw)));

                    // Always allow localhost/own domain
                    $whitelist[] = Tools::getHttpHost(false, false);
                    $whitelist[] = 'localhost';
                    $whitelist[] = '127.0.0.1';

                    $is_whitelisted = false;
                    foreach ($whitelist as $allowed_host) {
                        if (!empty($allowed_host) && stripos($host, $allowed_host) !== false) {
                            $is_whitelisted = true;
                            break;
                        }
                    }

                    // Check Blacklist
                    $blacklist_raw = Configuration::get('ADSCHI_CUSTOM_BLACKLIST');
                    $blacklist = array_filter(array_map('trim', explode("\n", $blacklist_raw)));

                    $is_blacklisted = false;
                    foreach ($blacklist as $blocked_host) {
                        if (!empty($blocked_host) && stripos($host, $blocked_host) !== false) {
                            $is_blacklisted = true;
                            break;
                        }
                    }

                    // Blacklist overrides whitelist. If blocked, return empty string or false.
                    if ($is_blacklisted || !$is_whitelisted) {
                        return false;
                    }
                }
            }
        }

        return parent::file_get_contents($url, $use_include_path, $stream_context, $curl_timeout);
    }

    /**
     * Override addonsRequest to prevent calling PrestaShop Addons API
     * which causes significant slowdowns when servers can't reach it.
     */
    public static function addonsRequest($request, $params = array())
    {
        if (Configuration::get('ADSCHI_BLOCK_EXTERNAL')) {
            return false;
        }

        return parent::addonsRequest($request, $params);
    }
}
