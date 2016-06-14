<?php

namespace Ponticlaro\Bebop\Db;

use Ponticlaro\Bebop\Common\Collection;
use Ponticlaro\Bebop\Common\Utils;

class WpQueryEnhanced {

   /**
     * List of raw arguments
     * 
     * @var Ponticlaro\Bebop\Common\Collection
     */
    protected $raw_args;

   /**
     * List of clean arguments
     * 
     * @var Ponticlaro\Bebop\Common\Collection
     */
    protected $clean_args;

   /**
     * List of options
     * 
     * @var Ponticlaro\Bebop\Common\Collection
     */
    protected $options;

    /**
     * Query data
     * 
     * @var \WP_Query
     */
    protected $query;

    /**
     * List of mapped arguments
     * 
     * @var array
     */
    protected static $args_map = array(
        'type'           => 'post_type',
        'status'         => 'post_status',
        'parent'         => 'post_parent',
        'mime_type'      => 'post_mime_type',
        'max_results'    => 'posts_per_page',
        'ppp'            => 'posts_per_page',
        'sort_by'        => 'orderby',
        'sort_direction' => 'order',
        'page'           => 'paged',
        'include'        => 'post__in',
        'exclude'        => 'post__not_in',
        'search'         => 's'
    );

    /**
     * Comparator map for composite query parameters
     * 
     * @var array
     */
    protected static $comparator_map = array(
        'eq'         => '=', 
        'noteq'      => '!=', 
        'is'         => '=', 
        'isnot'      => '!=', 
        'gt'         => '>', 
        'gte'        => '>=', 
        'lt'         => '<', 
        'lte'        => '<=', 
        'like'       => 'LIKE', 
        'notlike'    => 'NOT LIKE', 
        'in'         => 'IN', 
        'notin'      => 'NOT IN', 
        'between'    => 'BETWEEN', 
        'notbetween' => 'NOT BETWEEN',
        'and'        => 'AND'
    );

    /**
     * Value types map
     * 
     * @var array
     */
    protected static $value_type_map = array(
        'numeric'  => 'NUMERIC', 
        'decimal'  => 'DECIMAL', 
        'binary'   => 'BINARY', 
        'char'     => 'CHAR', 
        'date'     => 'DATE', 
        'datetime' => 'DATETIME',
        'time'     => 'TIME',
        'signed'   => 'SIGNED', 
        'unsigned' => 'UNSIGNED'
    );

    /**
     * Available taxonomy query fields
     * 
     * @var array
     */
    protected static $taxonomy_query_fields = array(
        'term_id',
        'name',
        'slug'
    );

    /**
     * List of arguments that require arrays as the value
     * 
     * @var array
     */
    protected $args_requiring_arrays = array(
        'author__in',
        'author__not_in',
        'category__and',
        'category__in',
        'category__not_in',
        'tag__and',
        'tag__in',
        'tag__not_in',
        'tag_slug__and',
        'tag_slug__in',
        'post_parent__in',
        'post_parent__not_in',
        'post__in',
        'post__not_in',
    );

    /**
     * List of arguments that admit arrays as the value
     * @var array
     */
    protected $args_admitting_arrays = array(
        'post_type',
        'post_status'
    );

    /**
     * Instantiates new WpQueryEnhanced
     * 
     * @param array $args    Optional raw query arguments
     * @param array $options Options
     */
    public function __construct(array $args = array(), array $options = array())
    {
        $this->raw_args   = new Collection($args);
        $this->clean_args = new Collection();
        $this->options    = new Collection(array(
            'with_meta' => false
        ));

        if ($options)
            $this->setOptions($options);
    }

    /**
     * Sets multiple options
     * 
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            
            $this->setOption($key, $value);
        }

        return $this;
    }

    /**
     * Sets a single option
     * 
     * @param string $key  
     * @param mixed  $value
     */
    public function setOption($key, $value)
    {
        if (is_string($key))
            $this->options->set($key, $value);

        return $this;
    }

    /**
     * Returns a single option
     * 
     * @param  string $key Option key
     * @return mixed       Option value
     */
    public function getOption($key)
    {
        return $this->options->get($key);
    }

    /**
     * Returns multiple options
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->options->getAll();
    }

    /**
     * Sets multiple raw arguments
     * 
     * @param array $options
     */
    public function setArgs(array $args)
    {
        foreach ($args as $key => $value) {
            
            $this->raw_args->set($key, $value);
        }

        return $this;
    }

    /**
     * Sets a single raw argument
     * 
     * @param string $key  
     * @param mixed  $value
     */
    public function setArg($key, $value)
    {
        if (is_string($key))
            $this->raw_args->set($key, $value);

        return $this;
    }

    /**
     * Removes multiple raw arguments
     * 
     * @param array $options
     */
    public function removeArgs(array $args)
    {
        foreach ($args as $key) {
            
            $this->raw_args->remove($key);
        }

        return $this;
    }

    /**
     * Removes a single raw argument
     * 
     * @param string $key  
     */
    public function removeArg($key)
    {
        if (is_string($key))
            $this->raw_args->remove($key);

        return $this;
    }

    /**
     * Executes query and returns data
     * 
     * @return mixed
     */
    public function execute()
    {
        return $this->__executeQuery();
    }

    /**
     * Returns WP_Query object
     * 
     * @return mixed WP_Query object after query execution, null if before
     */
    public function getWpQuery()
    {
        return $this->query;
    }

    /**
     * Captures built-in arguments and its alias
     * 
     * @param  array $args
     * @return void
     */
    protected function __captureBuiltInArgs(array &$args = array())
    {
        foreach ($args as $key => $value) {

            // Check if we should map a custom query parameter to a built-in query parameter
            if (array_key_exists($key, static::$args_map)) 
                $key = static::$args_map[$key];

            // Make sure comma delimited values are converted to arrays
            // on parameters that require arrays as the value
            if (in_array($key, $this->args_requiring_arrays) && is_string($value)) {
                
                $value = explode(',', $value);
            }

            // Make sure comma delimited values are converted to arrays
            // on parameters that require arrays as the value
            if (in_array($key, $this->args_admitting_arrays) && is_string($value)) {
                
                $value = explode(',', $value);

                if (count($value) == 1) $value = $value[0];
            }

            $this->clean_args->set($key, $value);
        }
    }

    /**
     * Captures custom taxonomy arguments
     * 
     * @param  array $args
     * @return void
     */
    protected function __captureCustomTaxArgs(array &$args = array())
    {
        foreach ($args as $key => $value) {

            if (preg_match('/^tax\:/', $key)) {

                $data_string = str_replace('tax:', '', $key);

                if ($data_string) {
                    
                    $data = null;
                    
                    if (Utils::isJson($data_string)) {
                        
                        $data = $data_string ? json_decode($data_string, true) : null;
                        
                    } else {

                        // check for comparator
                        $params = explode(':', $data_string);

                        $data = array(
                            'taxonomy' => isset($params[0]) ? $params[0] : '',
                            'operator' => isset($params[1]) && array_key_exists(strtolower($params[1]), static::$comparator_map) ? static::$comparator_map[$params[1]] : 'IN'
                        );
                    }

                    if (isset($data['taxonomy']) && $data['taxonomy']) {

                        if (!$this->clean_args->hasKey('tax_query') && isset($args['tax_relation'])) {
                                
                            $relation = $args['tax_relation'];
                            unset($args['tax_relation']);

                            $this->clean_args->set('tax_query.relation', $relation);
                        }

                        if (!isset($data['operator'])) 
                            $data['operator'] = 'IN';

                        if (!isset($data['field'])) 
                            $data['field'] = 'slug';

                        // Search for a value type
                        $params = explode(':', $value);

                        if (count($params) == 2 && in_array(strtolower($params[0]), static::$taxonomy_query_fields)) {
                            
                            $data['field'] = $params[0];
                            $value         = $params[1];
                        }

                        $data['terms'] = array();

                        foreach (explode(',', $value) as $value) {
                            $data['terms'][] = $value;
                        }

                        $this->clean_args->push($data, 'tax_query');
                    }
                }

                unset($args[$key]);
            }
        }
    }

    /**
     * Captures custom meta arguments
     * 
     * @param  array $args
     * @return void
     */
    protected function __captureCustomMetaArgs(array &$args = array())
    {
        foreach ($args as $key => $value) {

            if (preg_match('/^meta\:/', $key)) {

                $data_string = str_replace('meta:', '', $key);

                if ($data_string) {
                    
                    $data = null;
                    
                    if (Utils::isJson($data_string)) {
                        
                        $data = $data_string ? json_decode($data_string, true) : null;
                        
                    } else {

                        // check for comparator
                        $params = explode(':', $data_string);

                        $data = array(
                            'key'     => isset($params[0]) ? $params[0] : '',
                            'compare' => isset($params[1]) && array_key_exists(strtolower($params[1]), static::$comparator_map) ? static::$comparator_map[$params[1]] : '='
                        );
                    }

                    if (isset($data['key']) && $data['key']) {

                        if (!$this->clean_args->hasKey('meta_query') && isset($args['meta_relation'])) {
                                
                            $relation = $args['meta_relation'];
                            unset($args['meta_relation']);

                            $this->clean_args->set('meta_query.relation', $relation);
                        }

                        if (!isset($data['compare']) || !$data['compare']) 
                            $data['compare'] = '=';

                        if (!isset($data['type']) || !$data['type']) 
                            $data['type'] = 'CHAR';
        
                        // Search for a value type
                        $params = explode(':', $value);

                        if (count($params) == 2 && array_key_exists(strtolower($params[0]), static::$value_type_map)) {
                            
                            $data['type'] = $params[0];
                            $value        = $params[1];
                        }

                        $data['value'] = $value;

                        $this->clean_args->push($data, 'meta_query');
                    }
                }

                unset($args[$key]);
            }
        }
    }

    /**
     * Captures custom date arguments
     * 
     * @param  array $args
     * @return void
     */
    protected function __captureCustomDateArgs(array &$args = array())
    {
        foreach ($args as $key => $value) {

            if (preg_match('/^year\:/', $key) || 
                preg_match('/^month\:/', $key) || 
                preg_match('/^day\:/', $key) || 
                preg_match('/^week\:/', $key) || 
                preg_match('/^hour\:/', $key) || 
                preg_match('/^minute\:/', $key) || 
                preg_match('/^second\:/', $key)) {
                    
                $data     = explode(':', $key);
                $date_key = isset($data[0]) ? $data[0] : null;
                $compare  = isset($data[1]) ? $data[1] : '=';

                unset($args[$key]);

                if (array_key_exists(strtolower($compare), static::$comparator_map)) {
                    
                    $compare = static::$comparator_map[$compare];
                    $values  = explode(',', $value);
                    $value   = count($values) == 1 && !in_array($compare, array('IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN')) ? $values[0] : $values;

                    $data = array(
                        $date_key => $value,
                        'compare' => $compare
                    );

                    if (!$this->clean_args->hasKey('date_query')) {
                            
                        $relation = 'AND';

                        if (isset($args['date_relation'])) {
                            
                            $relation = $args['date_relation'];
                            unset($args['date_relation']);
                        }

                        $this->clean_args->set('date_query.relation', $relation);
                    }

                    $this->clean_args->push($data, 'date_query');
                }
            }
        }
    }

    /**
     * Executes current query
     * 
     * @return mixed
     */
    protected function __executeQuery()
    {
        $args = $this->raw_args->getAll();

        // Check if it is a taxonomy query argument
        $this->__captureCustomTaxArgs($args);

        // Check if it is a meta query argument
        $this->__captureCustomMetaArgs($args);

        // Check if it is a date query argument
        $this->__captureCustomDateArgs($args);

        // Capture built-in arguments
        $this->__captureBuiltInArgs($args);

        // Build new query
        $this->query = new \WP_Query($this->clean_args->getAll());

        // If we should return meta, build response structure accordingly
        if ($this->getOption('with_meta')) {

            $data = array(
                'meta'  => $this->__getQueryMeta(),
                'items' => $this->query->posts
            );
        }

        // No meta needed, just return entries
        else {

            $data = $this->query->posts;
        }

        return $data;
    }

    /**
     * Returns query metadata
     * 
     * @return array
     */
    protected function __getQueryMeta()
    {
        $args = $this->clean_args->getAll();
        $meta = array();

        $meta['items_total']    = (int) $this->query->found_posts;
        $meta['items_returned'] = (int) $this->query->post_count;
        $meta['total_pages']    = (int) $this->query->max_num_pages;
        $meta['current_page']   = $this->query->max_num_pages === 0 ? 0 : (int) max(1, $this->query->query_vars['paged']);
        $meta['has_more']       = $meta['current_page'] == $meta['total_pages'] || $meta['total_pages'] == 0 ? false : true;

        // Remove post_type parameter when not querying the /posts resource
        if ((isset($resource_name) && $resource_name != 'posts') && isset($args['post_type'])) {

            unset($args['post_type']);
        } 

        $meta['previous_page'] = in_array($meta['current_page'], [0, 1]) ? null : $this->__buildPreviousPageUrl($this->raw_args->getAll());
        $meta['next_page']     = $meta['current_page'] == $meta['total_pages'] || $meta['total_pages'] == 0 ? null : $this->__buildNextPageUrl($this->raw_args->getAll());

        return $meta;
    }

    /**
     * Builds previous page URL
     * 
     * @param  array  $params Query parameters
     * @return string         Query string
     */
    protected function __buildPreviousPageUrl(array $params = array())
    {
        $params['page'] = isset($params['page']) ? $params['page'] - 1 : 1; 

        return '?'. $this->__buildQuery($params);
    }

    /**
     * Builds next page URL
     * 
     * @param  array  $params Query parameters
     * @return string         Query string
     */
    protected function __buildNextPageUrl(array $params = array())
    {
        $params['page'] = isset($params['page']) ? $params['page'] + 1 : 2;

        return '?'. $this->__buildQuery($params);
    }

    /**
     * Builds URL encoded query string, based on a list of parameters
     * 
     * @param  array  $params Query parameters
     * @return string         Query string
     */
    protected function __buildQuery(array $params = array())
    {
        array_walk($params, function(&$value, $key) {

            if (is_array($value))
                $value = implode(',', $value);
            
            $value = urldecode($value);
        });

        return http_build_query($params);
    }
}