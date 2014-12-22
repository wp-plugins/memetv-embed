<?php
/**
 * Plugin Name: memeTV Embed
 * Plugin URI: http://www.memetv.com/plugins/wordpress/memetv-embed
 * Description: memeTV-embed plugin allows you to embed videos created with memeTV.
 * Version: 1.0
 * Author: memetv
 * Author URI: http://www.memeTV.com
 * License: GPL2
 */

/*  Copyright 2014  memeTV  (email : developer@memetv.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('MemetvEmbed')) {

    class MemetvEmbed {

        /**
         * Embed Sizes array
         *
         * @var
         * @private
         */
        private $SIZES = array(
            'small'  => array(320, 320), // default
            'medium' => array(480, 480),
            'large'  => array(600, 600)
        );

        /**
         * Constructor
         *
         * @public
         */
        public function __construct() {
            add_action('init', array($this, 'registerShortcodes'));
            add_action('init', array($this, 'registerPostButton'));
            add_action('admin_enqueue_scripts', array($this, 'memetvEmbedAdminScripts'));

        }

        public static function activate() {}
        public static function deactivate() {}

        /**
         * Adds admin scripts/styles
         *
         * @public
         */
        public function memetvEmbedAdminScripts() {
            wp_enqueue_style('memetv-embed-settings', plugins_url() . '/memetv-embed/files/memetv-embed-settings.min.css');

        }

        /**
         * Registers Shortcode
         *
         * @public
         */
        public function registerShortcodes() {
            add_shortcode('memetv', array($this, 'memetvEmbed'));

        }


        /**
         * Prepares memetvembed button registration
         *
         * @public
         */
        public function register_button($buttons) {
            array_push($buttons, "|", "memetvembed");
            return $buttons;

        }

        /**
         * Adds a plugin
         *
         * @public
         *
         * @param {mixed} $plugin_array
         * @return {mixed} $plugin_array
         */
        public function add_plugin($plugin_array) {
            $plugin_array['memetvembed'] = plugins_url() . '/memetv-embed/files/memetv-embed.min.js';
            return $plugin_array;

        }

        /**
         * Registers Post Button (for editors TinyMCE)
         *
         * @public
         */
        public function registerPostButton() {
            if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
                return;
            }

            if (get_user_option('rich_editing') == 'true') {
                add_filter('mce_external_plugins', array($this, 'add_plugin'));
                add_filter('mce_buttons', array($this, 'register_button'));
            }

        }

        /**
         * Prepares IFRAME's URL from given Video Share Page Url
         *
         * @private
         *
         * @param {String} $url video share page url
         * @return {String} embed iframe url or null
         */
        private function prepareIframeUrl($url) {

            // input:  http://memetv.com/meme/2ts/alley-oops/
            // output: //memetv.com/meme/embed/2ts'

            $pattern = '/\/\/(.*\.)?(memetv\.com)\/(meme|m)\/([a-zA-Z0-9]+)(\/.*)?$/';
            preg_match($pattern, $url, $matches);

            if ($matches && sizeof($matches) >= 4) {
                return sprintf('//%s%s/m/%s/embed', $matches[1], $matches[2], $matches[4]);
            }

            return null;

        }

        /**
         * Replaces shortcode with HTML
         *
         * @public
         *
         * @param {mixed} $atts Shortcode attributes
         * @return {String} video iframe code or empty string
         */
        public function memetvEmbed($atts) {

            $attributes = shortcode_atts(array(
                'size' => 'small',
                'url' => null,
                'autoplay' => '0'
            ), $atts, 'memetv');

            $url = $attributes['url'];
            $size = strtolower($attributes['size']);
            $autoplay = $attributes['autoplay'] == '1' ? '1' : '0';

            $iframe_url = $this->prepareIframeUrl($url);

            // return empty string if no correct URL
            if (!$iframe_url)
                return '';

            if (!array_key_exists($size, $this->SIZES)) {
                $size = 'small';
            }

            $width  = $this->SIZES[$size][0];
            $height = $this->SIZES[$size][1];

            $tpl = '<iframe class="memetv-embed" src="%1$s?autoplay=%4$s&v=2" width="%2$d" height="%3$d" style="border:none;margin:0 auto;" seamless frameborder=0 scrolling="no"></iframe><script async type="text/javascript" src="//memetv.com/static/embed/v2/embed.js"></script>';

            return sprintf($tpl, $iframe_url, $width, $height, $autoplay);

        }

    }

}


if (class_exists('MemetvEmbed')) {

    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('MemetvEmbed', 'activate'));
    register_deactivation_hook(__FILE__, array('MemetvEmbed', 'deactivate'));

    // instantiate the plugin class
    $memetv_embed = new MemetvEmbed();

}
