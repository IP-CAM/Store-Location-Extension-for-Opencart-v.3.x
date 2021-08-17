<?php

class ControllerExtensionModuleMinify extends Controller
{

    private $dom;

    // Exclude JS-files
    private $exclude_js = [
        'jquery-3.3.1.min.js',
        'moment.js',
        'bootstrap.min.js',
        'common.js',
    ];
    // Exclude CSS-files
    private $exclude_css = [
        'font-awesome.min.css',
    ];

    private $js_array = [];
    private $css_array = [];

    private $output_js;
    private $output_css;

    private $out_folder;

    public function minify()
    {
        if ($this->config->get('config_theme') == 'theme_default') {
            $theme = $this->config->get('theme_default_directory');
        } else {
            $theme = $this->config->get('config_theme');
        }

        $this->out_folder = 'catalog/view/theme/' . $theme . '/minify';

        $buffer = $this->response->getOutput();
        $new_buffer = null;

        $this->dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $this->dom->loadHTML($buffer, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Append UTF-8 encoding to head
        $this->appendEncoding();

        $this->checkPath($this->out_folder);

        $scripts = $this->dom->getElementsByTagName('script');

        $delete_js = [];

        // Search scripts elements
        foreach ($scripts as $script) {

            $file_name = $script->getAttribute('src');

            if (!empty($file_name)) {
                if (is_readable($file_name) && !$this->isExclude($this->exclude_js, $file_name)) {

                    $delete_js[] = $script;

                    $this->js_array[] = [
                        'filename' => $file_name
                    ];
                }
            }
        }

        // remove JS-elements from DOM
        $this->removeElements($delete_js);


        $_js_file = md5(serialize($this->js_array)) . '.js';
        $this->output_js = $this->out_folder . '/' . $_js_file;

        // Caching minify JS-file name
        if ($cache_js = $this->cache->get($_js_file)) {

            $this->output_js = $cache_js;
        } else {

            $this->concatFiles('js');
            $this->cache->set($_js_file, $this->output_js);
        }

        if (!empty($this->js_array)) $this->appendJs();

        $styles = $this->dom->getElementsByTagName('link');

        // Search CSS-elements
        foreach ($styles as $style) {
            $file_name = $style->getAttribute('href');

            if (!empty($file_name)) {
                if (is_readable($file_name) && !$this->isExclude($this->exclude_css, $file_name)) {

                    $delete_css[] = $style;

                    $this->css_array[] = [
                        'filename' => $file_name
                    ];
                }
            }
        }

        // Delete CSS-elements from DOM
        $this->removeElements($delete_css);

        $_css_file = md5(serialize($this->css_array)) . '.css';
        $this->output_css = $this->out_folder . '/' . $_css_file;

        // Caching minify CSS-file name
        if ($cache_css = $this->cache->get($_css_file)) {

            $this->output_css = $cache_css;
        } else {
            $this->concatFiles('css');
            $this->cache->set($_css_file, $this->output_css);
        }

        if (!empty($this->css_array)) $this->appendCss();

        // Save DOM
        $new_buffer = $this->dom->saveHTML();

        if ($new_buffer === null) {
            $new_buffer = $buffer;
        }

        $this->response->setOutput($new_buffer);
    }

    private function concatFiles($type)
    {
        switch ($type) {
            case 'js':
                $files = $this->js_array;
                $output_file = $this->output_js;
                break;
            case 'css':
                $files = $this->css_array;
                $output_file = $this->output_css;
                break;
        }

        $content = '';

        foreach ($files as $file) {
            $concat = false;
            $_content = '';

            if (file_exists($file['filename'])) {
                $concat = true;
            }

            if ($concat) {
                $handler = @fopen($file['filename'], 'r');

                if (!$handler) {
                    continue;
                }

                $_content .= fread($handler, filesize($file['filename']));

                fclose($handler);

                if ($type === 'js') {
                    if (substr(rtrim($_content), -1) !== ';') {
                        $_content .= ';';
                    }
                }

                if (!empty($_content)) {
                    switch ($type) {
                        case 'js':
                            $content .= (!substr_count($file['filename'], '.min.')) ? $this->minifyJS($_content) : $_content;
                            break;
                        case 'css':
                            $content .= (!substr_count($file['filename'], '.min.')) ? $this->minifyCSS($_content) : $_content;
                            break;
                    }

                }
            }
        }

        if (empty($content)) return false;

        $handle = fopen($output_file, 'w');
        $result = fwrite($handle, $content);
        fclose($handle);

        return $result;
    }

    private function checkPath($path)
    {
        if (!file_exists($path)) {
            mkdir($path);
        }

        if (!is_readable($path)) {
            throw new Exception('Directory for compressed assets is not readable.');
        }

        if (!is_writable($path)) {
            throw new Exception('Directory for compressed assets is not writable.');
        }
    }

    private function minifyCSS($input)
    {
        if (trim($input) === "") {
            return $input;
        }

        // Force white-space(s) in `calc()`
        if (strpos($input, 'calc(') !== false) {
            $input = preg_replace_callback('#(?<=[\s:])calc\(\s*(.*?)\s*\)#',
                function ($matches) {
                    return 'calc(' . preg_replace('#\s+#', "\x1A", $matches[1]) . ')';
                },
                $input
            );
        }

        return preg_replace(
            array(
                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',

                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',

                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',

                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',

                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',

                // Replace `0.6` with `.6`, but only when preceded by a white-space or `=`, `:`, `,`, `(`, `-`
                '#(?<=[\s=:,\(\-]|&\#32;)0+\.(\d+)#s',

                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][-\w]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',

                // Minify HEX color code
                '#(?<=[\s=:,\(]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',

                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',

                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s',
                '#\x1A#'
            ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                '$1:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
                '$1:0',
                '$1$2',
                ' '
            ),
            $input
        );
    }

    private function minifyJS($input)
    {
        if (trim($input) === "") {
            return $input;
        }

        return preg_replace(
            array(
                // Remove comment(s)
                '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',

                // Remove white-space(s) outside the string and regex
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',

                // Remove the last semicolon
                '#;+\}#',

                // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                '#([\{,])([\'])(\d+|[a-z_]\w*)\2(?=\:)#i',

                // --ibid. From `foo['bar']` to `foo.bar`
                '#([\w\)\]])\[([\'"])([a-z_]\w*)\2\]#i',

                // Replace `true` with `!0`
                '#(?<=return |[=:,\(\[])true\b#',

                // Replace `false` with `!1`
                '#(?<=return |[=:,\(\[])false\b#',

                // Clean up ...
                '#\s*(\/\*|\*\/)\s*#'
            ),
            array(
                '$1',
                '$1$2',
                '}',
                '$1$3',
                '$1.$3',
                '!0',
                '!1',
                '$1'
            ),
            $input
        );
    }

    private function isExclude($array, $filename)
    {
        foreach ($array as $value) {

            if (substr_count($filename, $value)) return true;
        }

        return false;
    }

    private function removeElements(array $elements)
    {
        foreach ($elements as $del_item) {
            $del_item->parentNode->removeChild($del_item);
        }
    }

    // Fix encoding for DOMDocument
    private function appendEncoding()
    {
        $meta = $this->dom->createElement('meta');

        $meta_type = $this->dom->createAttribute('http-equiv');
        $meta_type->value = 'Content-Type';
        $meta_content = $this->dom->createAttribute('content');
        $meta_content->value = 'text/html; charset=utf-8';

        $meta->appendChild($meta_type);
        $meta->appendChild($meta_content);

        $head = $this->dom->getElementsByTagName('head')->item(0);
        $head->appendChild($meta);
    }

    private function appendJs()
    {
        $script = $this->dom->createElement('script');

        $script_type = $this->dom->createAttribute('type');
        $script_type->value = 'text/javascript';

        $script_src = $this->dom->createAttribute('src');
        $script_src->value = '/' . $this->output_js;

        $script->appendChild($script_type);
        $script->appendChild($script_src);

        if (($this->dom->getElementsByTagName('script')->length == 0) ||
            !empty($this->dom->getElementsByTagName('script')->item(0)->nodeValue)
        ) {
            $head = $this->dom->getElementsByTagName('head')->item(0);
            $head->appendChild($script);
        } else {
            $first_js = $this->dom->getElementsByTagName('script')->item(0);
            $first_js->parentNode->insertBefore($script, $first_js);
        }
    }

    private function appendCss()
    {

        $style = $this->dom->createElement('link');

        $style_rel = $this->dom->createAttribute('rel');
        $style_rel->value = 'stylesheet';

        $style_href = $this->dom->createAttribute('href');
        $style_href->value = '/' . $this->output_css;

        $style_type = $this->dom->createAttribute('type');
        $style_type->value = 'text/css';

        $style->appendChild($style_type);
        $style->appendChild($style_href);
        $style->appendChild($style_rel);

        $head = $this->dom->getElementsByTagName('head')->item(0);
        $head->appendChild($style);
    }
}