# External Connections Blocker for WordPress

Block all unwanted external HTTP requests from your WordPress site while allowing only what you need — like Google services for Google Ads tracking.

## 🔒 What It Does

This lightweight plugin gives you full control over your website's outbound HTTP requests. It can:

- Block all external requests (themes/plugins license checks, analytics, etc.)
- Whitelist trusted domains (e.g., `googleapis.com`)
- Blacklist specific domains regardless of whitelist
- Disable automatic updates, XML-RPC, emojis, and Google Fonts
- Add a persistent top banner in the WordPress admin area
- Offer a fixed bottom banner in the settings page

## ⚙️ Features

- ✅ **Block external requests** with the `WP_HTTP_BLOCK_EXTERNAL` method
- ✅ **Custom whitelist & blacklist**
- ✅ **Toggle options**: Google domains, updates, XML-RPC, emojis, Google Fonts
- ✅ **Admin UI** to manage options
- ✅ **Persistent admin banner** for branding or messaging
- ✅ Fully translatable and ready for localization

## 📸 Screenshots

![Settings Page](screenshot-1.png)  
*Easily manage external access settings.*

## 📦 Installation

1. Download the plugin as a `.zip` file
2. In your WordPress dashboard, go to **Plugins > Add New > Upload Plugin**
3. Upload the `.zip` file and activate the plugin
4. Go to **Settings > Connections Blocker** to configure

## 🛠 Settings Available

| Option                | Description                                     |
|----------------------|-------------------------------------------------|
| Block External        | Enable to block all non-whitelisted domains     |
| Allow Google Domains  | Let Google Ads & Analytics function normally    |
| Disable Updates       | Stop WP core/plugin/theme update checks         |
| Disable XML-RPC       | Disable XML-RPC for improved security           |
| Disable Emojis        | Remove extra emoji scripts from loading         |
| Disable Google Fonts  | Remove Google Fonts (fonts.googleapis.com etc.) |
| Custom Whitelist      | List of allowed domains (one per line)          |
| Custom Blacklist      | List of denied domains (one per line)           |

## 📄 Credits

Developed by [Mohammad Babaei](https://adschi.com)  
Plugin home: [https://adschi.com](https://adschi.com)

## 💼 Banner Message

A persistent top banner in your WP Admin shows:

> **خدمات مشاوره حرفه‌ای و تبلیغات در گوگل با [ادزچی](https://adschi.com)**

You can customize this for branding.

## 📝 License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---

### 🙌 Contributions Welcome

Feel free to open issues or pull requests to improve or extend functionality.

