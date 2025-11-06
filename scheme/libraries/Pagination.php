<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 4
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */
/**
 * LavaLust Pagination Class
 *
 * Provides pagination logic and customizable rendering for different frontend styles.
 */
class Pagination
{
    /**
     * @var array Stores pagination metadata (limit, current page, etc.)
     */
    protected $page_array = [];

    /**
     * @var int Current page number
     */
    protected $page_num;

    /**
     * @var int Number of rows per page
     */
    protected $rows_per_page;

    /**
     * @var int Number of page links to show (crumbs)
     */
    protected $crumbs;

    /**
     * @var array Final output for render
     */
    protected $pagination;

    /**
     * @var string Label for "First" page link
     */
    protected $first_link = '&lsaquo; First';

    /**
     * @var string Label for "Next" page link
     */
    protected $next_link = '&gt;';

    /**
     * @var string Label for "Previous" page link
     */
    protected $prev_link = '&lt;';

    /**
     * @var string Label for "Last" page link
     */
    protected $last_link = 'Last &rsaquo;';

    /**
     * @var string Delimiter used between base URL and page number
     */
    protected $page_delimiter = '/';

    /**
     * @var string Current theme layout: 'bootstrap', 'tailwind', or 'custom'
     */
    protected $theme = 'bootstrap';

    /**
     * @var array CSS class mappings for HTML generation
     */
    protected $classes = [
        'nav'    => 'pagination-nav',
        'ul'     => 'pagination-list',
        'li'     => 'pagination-item',
        'a'      => 'pagination-link',
        'active' => 'active'
    ];

    /**
     * @var object LavaLust core instance
     */
    protected $LAVA;

    /**
     * @var string Base URL to use when generating page links (optional)
     */
    protected $base_url = '';

    /**
     * Constructor
     *
     * Loads language and session libraries and initializes labels
     */
    public function __construct()
    {
        $this->LAVA = lava_instance();
        $this->LAVA->call->helper('language');
        $this->LAVA->call->library('session');

        $set_language = $this->LAVA->session->userdata('page_language') ?? config_item('language');
        language($set_language);

        foreach (['first_link', 'next_link', 'prev_link', 'last_link', 'page_delimiter'] as $key) {
            $this->$key = lang($key) ?? $this->$key;
        }

        $this->set_theme($this->theme);
    }

    /**
     * Set layout theme
     *
     * @param string $theme One of 'bootstrap', 'tailwind', or 'custom'
     */
    public function set_theme($theme)
    {
        $this->theme = $theme;
        switch ($theme) {
            case 'bootstrap':
                $this->classes = [
                    'nav'    => 'd-flex justify-content-center',
                    'ul'     => 'pagination',
                    'li'     => 'page-item',
                    'a'      => 'page-link',
                    'active' => 'active'
                ];
                break;
            case 'tailwind':
                $this->classes = [
                    'nav'    => 'flex justify-center mt-4',
                    'ul'     => 'inline-flex -space-x-px',
                    'li'     => 'px-1',
                    'a'      => 'inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 first:rounded-l-md last:rounded-r-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                    'active' => 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600 hover:bg-indigo-50'
                ];
                break;
            case 'custom':
                // Custom classes can be set using set_custom_classes()
                break;
        }
    }

    /**
     * Override specific CSS classes for layout
     *
     * @param array $classes Associative array of class types and values
     */
    public function set_custom_classes(array $classes)
    {
        $this->classes = array_merge($this->classes, $classes);
    }

    /**
     * Set custom pagination options like links and delimiter.
     *
     * @param array $options
     * @return void
     */
    public function set_options(array $options)
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Initialize pagination values and logic
     *
     * Supports two call styles for backward compatibility:
     * 1) initialize(total_rows, rows_per_page, page_num, crumbs)
     * 2) initialize(total_rows, rows_per_page, page_num, base_url, crumbs)
     *
     * @param int $total_rows Total number of database rows
     * @param int $rows_per_page Rows to display per page
     * @param int $page_num Current page number
     * @param mixed $base_url_or_crumbs Either base URL (string) or crumbs (int)
     * @param int|null $crumbs Number of visible page links (if base URL is provided separately)
     * @return array Metadata for pagination
     */
    public function initialize($total_rows, $rows_per_page, $page_num, $base_url_or_crumbs = 5, $crumbs = null)
    {
        // Detect whether the 4th parameter is a base URL (string) or crumbs (int)
        if (is_string($base_url_or_crumbs)) {
            $this->base_url = $base_url_or_crumbs;
            $this->crumbs = ($crumbs !== null) ? (int) $crumbs : 5;
        } else {
            $this->crumbs = (int) $base_url_or_crumbs;
        }
        $this->rows_per_page = (int) $rows_per_page;

        $last_page = max(1, ceil($total_rows / $this->rows_per_page));
        $this->page_num = max(1, min($page_num, $last_page));

        $offset = ($this->page_num - 1) * $this->rows_per_page;
        $this->page_array['limit'] = 'LIMIT '.$offset.','.$this->rows_per_page;
        $this->page_array['current'] = $this->page_num;
        $this->page_array['previous'] = max(1, $this->page_num - 1);
        $this->page_array['next'] = min($last_page, $this->page_num + 1);
        $this->page_array['last'] = $last_page;
        $this->page_array['info'] = 'Page ('.$this->page_num.' of '.$last_page.')';
        $this->page_array['pages'] = $this->render_pages($this->page_num, $last_page);

        return $this->page_array;
    }

    /**
     * Generate array of page numbers to display
     *
     * @param int $page_num Current page
     * @param int $last_page Last page number
     * @return array List of visible page numbers
     */
    protected function render_pages($page_num, $last_page)
    {
        $arr = [];
        if ($page_num == 1) {
            for ($i = 0; $i < min($this->crumbs, $last_page); $i++) {
                $arr[] = $i + 1;
            }
        } elseif ($page_num == $last_page) {
            $start = max(0, $last_page - $this->crumbs);
            for ($i = $start; $i < $last_page; $i++) {
                $arr[] = $i + 1;
            }
        } else {
            $start = max(0, $page_num - ceil($this->crumbs / 2));
            $end = min($last_page, $start + $this->crumbs);
            for ($i = $start; $i < $end; $i++) {
                $arr[] = $i + 1;
            }
        }
        return $arr;
    }

    /**
     * Render the full pagination HTML
     *
     * @return string HTML output
     */
    public function paginate()
    {
        if (empty($this->page_array['pages'])) return '';

        $html = '<nav class="'.$this->classes['nav'].'"><ul class="'.$this->classes['ul'].'">';

        $html .= $this->build_link(1, $this->first_link);
        $html .= $this->build_link($this->page_array['previous'], $this->prev_link);

        foreach ($this->page_array['pages'] as $page) {
            $active = ($page == $this->page_array['current']) ? $this->classes['active'] : '';
            $html .= $this->build_link($page, $page, $active);
        }

        $html .= $this->build_link($this->page_array['next'], $this->next_link);
        $html .= $this->build_link($this->page_array['last'], $this->last_link);

        $html .= '</ul></nav>';
        return $html;
    }

    /**
     * Generate an individual page link
     *
     * @param int $page Target page number
     * @param string $label Link text
     * @param string $active_class Optional active class
     * @return string HTML list item with link
     */
    protected function build_link($page, $label, $active_class = '')
    {
        $url = $this->build_url($page);
        return '<li class="'.$this->classes['li'].'">
                    <a class="'.$this->classes['a'].' '.$active_class.'" href="'.$url.'">'.$label.'</a>
                </li>';
    }

    /**
     * Build a URL for a given page using base_url and page_delimiter strategy.
     * If page_delimiter contains '=', it's treated as a query parameter (e.g., 'page=' or '&page=').
     * Otherwise, it's treated as a path delimiter (e.g., '/').
     *
     * @param int $page
     * @return string
     */
    protected function build_url($page)
    {
        // If a base URL is provided, use it; otherwise, fall back to site_url
        if (!empty($this->base_url)) {
            $delimiter = (string) $this->page_delimiter;

            // Query-style delimiter (e.g., '?page=' or '&page=')
            if (strpos($delimiter, '=') !== false) {
                $hasQuery = (strpos($this->base_url, '?') !== false);
                $sep = $hasQuery ? '&' : '?';
                // Normalize parameter: remove leading ? or & if present
                $param = ltrim($delimiter, "?&");
                // Ensure it ends with '=' (if someone provided just 'page' or '&p')
                if (strpos($param, '=') === false) {
                    $param .= '=';
                }
                return $this->base_url . $sep . $param . $page;
            }

            // Path-style delimiter (default '/')
            return rtrim($this->base_url, '/') . $delimiter . $page;
        }

        // Legacy behavior without base_url
        return site_url($this->page_delimiter.$page);
    }
}