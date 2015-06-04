<?php

namespace Ponticlaro\Bebop\Db\Query;

class ArgFactory extends \Ponticlaro\Bebop\Common\Patterns\FactoryAbstract {

    /**
     * Holds the class that manufacturables must extend
     */
    const ARG_CLASS = 'Ponticlaro\Bebop\Db\Query\Arg';

    /**
     * List of manufacturable classes
     * 
     * @var array
     */
    protected static $manufacturable = array(
        'author'       => 'Ponticlaro\Bebop\Db\Query\Presets\AuthorArg',
        'cat'          => 'Ponticlaro\Bebop\Db\Query\Presets\CatArg',
        'date'         => 'Ponticlaro\Bebop\Db\Query\Presets\DateArg',
        'day'          => 'Ponticlaro\Bebop\Db\Query\Presets\DayArg',
        'hour'         => 'Ponticlaro\Bebop\Db\Query\Presets\HourArg',
        'ignoresticky' => 'Ponticlaro\Bebop\Db\Query\Presets\IgnoreStickyArg',
        'limit'        => 'Ponticlaro\Bebop\Db\Query\Presets\PostsPerPageArg',
        'maxresults'   => 'Ponticlaro\Bebop\Db\Query\Presets\PostsPerPageArg',
        'meta'         => 'Ponticlaro\Bebop\Db\Query\Presets\MetaArg',
        'metakey'      => 'Ponticlaro\Bebop\Db\Query\Presets\MetaKeyArg',
        'metavalue'    => 'Ponticlaro\Bebop\Db\Query\Presets\MetaValueArg',
        'mime'         => 'Ponticlaro\Bebop\Db\Query\Presets\MimeArg',
        'minute'       => 'Ponticlaro\Bebop\Db\Query\Presets\MinuteArg',
        'month'        => 'Ponticlaro\Bebop\Db\Query\Presets\MonthArg',
        'offset'       => 'Ponticlaro\Bebop\Db\Query\Presets\OffsetArg',
        'orderby'      => 'Ponticlaro\Bebop\Db\Query\Presets\OrderByArg',
        'orderbymeta'  => 'Ponticlaro\Bebop\Db\Query\Presets\OrderByMetaArg',
        'page'         => 'Ponticlaro\Bebop\Db\Query\Presets\ResultsPageArg',
        'paged'        => 'Ponticlaro\Bebop\Db\Query\Presets\ResultsPageArg',
        'parent'       => 'Ponticlaro\Bebop\Db\Query\Presets\ParentArg',
        'post'         => 'Ponticlaro\Bebop\Db\Query\Presets\PostArg',
        'postsperpage' => 'Ponticlaro\Bebop\Db\Query\Presets\PostsPerPageArg',
        'posttype'     => 'Ponticlaro\Bebop\Db\Query\Presets\TypeArg',
        'ppp'          => 'Ponticlaro\Bebop\Db\Query\Presets\PostsPerPageArg',
        'second'       => 'Ponticlaro\Bebop\Db\Query\Presets\SecondArg',
        'status'       => 'Ponticlaro\Bebop\Db\Query\Presets\StatusArg',
        'tag'          => 'Ponticlaro\Bebop\Db\Query\Presets\TagArg',
        'tax'          => 'Ponticlaro\Bebop\Db\Query\Presets\TaxArg',
        'taxonomy'     => 'Ponticlaro\Bebop\Db\Query\Presets\TaxArg',
        'week'         => 'Ponticlaro\Bebop\Db\Query\Presets\WeekArg',
        'year'         => 'Ponticlaro\Bebop\Db\Query\Presets\YearArg',
        's'            => 'Ponticlaro\Bebop\Db\Query\Presets\SearchArg',
        'search'       => 'Ponticlaro\Bebop\Db\Query\Presets\SearchArg',
    );

    /**
     * Creates instance of target class
     * 
     * @param  string] $type Class ID
     * @param  array   $args Class arguments
     * @return object        Class instance
     */
    public static function create($type, array $args = array())
    {
        // Create object
        $obj = parent::create($type, $args);

        return is_a($obj, self::ARG_CLASS) ? $obj : null;
    }
}