<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_List_Table
 * A more generic configurable list table.
 *
 * @package     Piklist
 * @subpackage  List_Table
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_List_Table 
{
  /**
   * _construct
   * Class constructor.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function _construct()
  {
  }
  
  /**
   * render
   * This method will render a list table using the universal piklist list table.
   *
   * @param array $arguments Configuration options for the table.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function render($arguments)
  {
    extract($arguments);
    
    $list_table = new Piklist_List_Table_Template($arguments);
    $list_table->arguments = $arguments;
    $list_table->prepare_items();
    
    piklist::render('shared/list-table', array(
      'list_table' => $list_table
      ,'name' => $name
      ,'export' => isset($export) ? $export : false
      ,'form_id' => 'piklist_list_table_form_' . piklist::slug($name)
    ));
  }
}

if (!class_exists('WP_List_Table'))
{
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Piklist_List_Table_Template
 * See WP_List_Table for documentation on these methods.
 *
 * @package piklist
 */
class Piklist_List_Table_Template extends WP_List_Table 
{
  var $data;

  var $key;

  var $name;

  var $table_id;
  
  var $actions = array();

  var $column;
  
  var $classes = array();
  
  var $columns = array();

  var $sortable_columns = array();
  
  function __construct($arguments)
  {
    global $wpdb;
    
    extract($arguments);
    
    // TODO: Require fields
    $this->key = $key;
    $this->name = $name;
    $this->table_id = piklist::slug($this->name);
    $this->column = $column;
    $this->columns = $columns;
    $this->classes = isset($classes) ? $classes : array('widefat', 'fixed');
    $this->sortable_columns = isset($sortable_columns) ? $sortable_columns : false;
    $this->actions = isset($actions) ? $actions : $this->actions;
    $this->per_page = isset($per_page) ? $per_page : 10;
    $this->ajax = isset($ajax) ? $ajax : false;
    $this->export = isset($export) ? $export : false;
    
    if (isset($data))
    {
      $this->data = is_object($data) || is_array($data) ? piklist::object_to_array($data) : $wpdb->get_results($data, ARRAY_A);
      
      usort($this->data, array($this, 'data_sort'));
      
      $this->current_page = $this->get_pagenum();
      $this->total_items = count($this->data);
      
      // TODO: If sql use orderby
      // TODO: Implement offset in sql with current_page
    }
    else
    {
      $this->data = array();
      $this->current_page = 1;
      $this->total_items = 0;
    }
    
    parent::__construct(array(
      'singular' => piklist::singularize($this->name)
      ,'plural' => piklist::pluralize($this->name)
      ,'ajax' => $this->ajax
   ));
  }
  
  function get_columns()
  {
    return $this->columns;
  }
  
  function get_sortable_columns() 
  {
    foreach ($this->columns as $column => $label)
    {
      if (!isset($this->sortable_columns[$column]) && $column != 'cb')
      {
        $this->sortable_columns[$column] = array($column, false);
      }
    }
    
    return $this->sortable_columns;
  }
  
  function data_sort($a, $b)
  {
    $orderby = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : key($a); 
    $order = !empty($_REQUEST['order']) ? strtolower($_REQUEST['order']) : 'asc'; 
   
    $a[$orderby] = preg_replace("/(?![.=$'€%-])\p{P}/u", '', $a[$orderby]);
    $b[$orderby] = preg_replace("/(?![.=$'€%-])\p{P}/u", '', $b[$orderby]);
   
    $result = strnatcmp($a[$orderby], $b[$orderby]);

    return ($order === 'asc') ? $result : -$result;
  }
  
  function column_cb($item)
  {
    return sprintf(
      '<input type="checkbox" name="%1$s[]" value="%2$s" />'
      ,$this->_args['singular']
      ,$item[$this->key]
   );
  }
  
  function column_default($item, $column_name)
  {
    if ($this->column == $column_name)
    {
      $actions = array();
      foreach ($this->actions as $action => $label)
      {
        $actions[$action] = sprintf('<a href="?page=%s&action=%s&%s=%s">%s</a>', $_REQUEST['page'], $action, $this->name, $item[$this->key], $label);
      }
    
      return sprintf(
        '%1$s %3$s'
        ,$item[$this->column]
        ,$item[$this->key]
        ,$this->row_actions($actions)
     );
    }
    else
    {
      // TODO: Add Filter to allow formatting changes or lookups like author ID to display name
      return isset($item[$column_name]) ? $item[$column_name] : null;
    }
  }
  
  function get_table_classes() 
  {
    array_push($this->classes, $this->_args['plural']);
    
    return $this->classes;
  }
  
  function get_bulk_actions() 
  {
    // TODO: Add Filter to add more actions or adjust them
    return $this->actions;
  }
  
  function process_bulk_action() 
  {
  }
  
  function prepare_items() 
  {
    global $wpdb; 
    
    $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
      
    $this->process_bulk_action();

    $this->items = array_slice($this->data, (($this->current_page - 1) * $this->per_page), $this->per_page);
    
    $this->set_pagination_args(array(
      'total_items' => $this->total_items
      ,'per_page' => $this->per_page
      ,'total_pages' => ceil($this->total_items / $this->per_page)
   ));
  }
  
  function get_pagenum()
  {
    $pagenum = isset($_REQUEST['paged_' . $this->table_id]) ? absint($_REQUEST['paged_' . $this->table_id]) : 0;

    if (isset($this->_pagination_args['total_pages']) && $pagenum > $this->_pagination_args['total_pages'])
    {
      $pagenum = $this->_pagination_args['total_pages'];
    }  
    
    return max(1, $pagenum);    
  }
  
  function pagination($which) 
  {
    if (empty($this->_pagination_args))
    {
      return;
    }
    
    extract($this->_pagination_args, EXTR_SKIP);

    $output = '<span class="displaying-num">' . sprintf(_n('1 item', '%s items', $total_items, 'piklist'), number_format_i18n($total_items)) . '</span>';

    $current = $this->get_pagenum();

    $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . esc_url($_SERVER['REQUEST_URI']));
    $current_url = remove_query_arg(array('hotkeys_highlight_last', 'hotkeys_highlight_first'), $current_url);
    
    $page_links = array();

    $disable_first = $disable_last = '';
    
    if ($current == 1)
    {
      $disable_first = ' disabled';
    }
    
    if ($current == $total_pages)
    {
      $disable_last = ' disabled';
    }
    
    $page_links[] = sprintf(
      "<a class='%s' title='%s' href='%s'>%s</a>"
      ,'first-page' . $disable_first
      ,esc_attr__('Go to the first page', 'piklist')
      ,esc_url(remove_query_arg('paged_' . $this->table_id, $current_url))
      ,'&laquo;'
    );

    $page_links[] = sprintf(
      "<a class='%s' title='%s' href='%s'>%s</a>"
      ,'prev-page' . $disable_first
      ,esc_attr__('Go to the previous page', 'piklist')
      ,esc_url(add_query_arg('paged_' . $this->table_id, max(1, $current - 1), $current_url))
      ,'&lsaquo;'
    );

    $html_current_page = sprintf(
      "<input class='current-page' title='%s' type='text' name='paged' value='%s' size='%d' />"
      ,esc_attr__('Current page', 'piklist')
      ,$current
      ,strlen($total_pages)
    );
    
    $html_total_pages = sprintf("<span class='total-pages'>%s</span>", number_format_i18n($total_pages));
    $page_links[] = '<span class="paging-input">' . sprintf(_x('%1$s of %2$s', 'paging', 'piklist'), $html_current_page, $html_total_pages) . '</span>';

    $page_links[] = sprintf(
      "<a class='%s' title='%s' href='%s'>%s</a>"
      ,'next-page' . $disable_last
      ,esc_attr__('Go to the next page', 'piklist')
      ,esc_url(add_query_arg('paged_' . $this->table_id, min($total_pages, $current + 1), $current_url))
      ,'&rsaquo;'
    );

    $page_links[] = sprintf(
      "<a class='%s' title='%s' href='%s'>%s</a>"
      ,'last-page' . $disable_last
      ,esc_attr__('Go to the last page', 'piklist')
      ,esc_url(add_query_arg('paged_' . $this->table_id, $total_pages, $current_url))
      ,'&raquo;'
    );

    $pagination_links_class = 'pagination-links';
    if (! empty($infinite_scroll))
    {
      $pagination_links_class = ' hide-if-js';
    }
    
    $output .= "\n<span class='$pagination_links_class'>" . join("\n", $page_links) . '</span>';

    if ($total_pages)
    {
      $page_class = $total_pages < 2 ? ' one-page' : '';
    }
    else
    {
      $page_class = ' no-pages';
    }
    
    $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

    echo $this->_pagination;
  }
}