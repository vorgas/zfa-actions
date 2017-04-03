<?php
/**
 * Defines the database configurations for api actions for the services
 *
 * This file should be copied into module/{modulename}/config.
 * It should be renamed, replacing 'service' with the appropriate API
 * service name. For example: apiactions.users.php
 */

/**
 * The name of the primary table referenced by this service
 * 
 * You can override this on a per method basis if needed.
 * 
 * @var string $table
 */
$table = 'database_table';

/**
 * Name of the column to use for fetching one row
 * 
 * For actions that use a "WHERE `table`.`id` = ?" this will cause a column
 * name other than 'id' to be used. 
 * 
 * @var string $idColumn
 */
$idColumn = 'id';

/**
 * The name of the Api Service being utilized
 * 
 * You shouldn't need to change this, unless for some reason it is returning
 * the wrong name.
 * 
 * @var string $service
 */
$service = ucfirst(explode('.', array_pop(explode('/', __FILE__)))[1]);


/**
 * The fully qualified name of the resource entity
 * 
 * @var string $entity
 */
$entity = "\V1\Rest\$service\$service" . 'Entity.php';

/**
 * The fully qualified name of the resource collection
 * 
 * @var string $collection
 */
$collection = "\V1\Rest\$service\$service" . 'Collection.php';

/**
 * Defines the default search types for each column
 * 
 * By default, a column comparison check uses equals (WHERE `column` = value).
 * You can override the default for any column here. Again, for each type of
 * http method this can be overriden. And, of course, query parameters will
 * override all.
 *
 * Each entry in the array is the name of the column with any of the following
 * search types:
 *  - equals        WHERE `column` = value
 *  - includes      WHERE `column` LIKE '%value%'
 *  - starts        WHERE `column` LIKE 'value%'
 *  - ends          WHERE `column` LIKE '%value'

 * @var array $compares
 */
$compares = [
    'column_1' => 'search_type'
];


/**
 * Defines the database configurations for api actions for the services
 *
 * This file should be copied into module/{modulename}/config.
 * It should be renamed, replacing 'service' with the appropriate API
 * service name. For example: apiactions.users.php
 *
 * The array is constructed as:
 *      '{apiaction}' => [
 *          'table' => '{table name}',
 *          'crud' => 'select|update|insert|delete',
 *          'columns' => ['associated', 'column', 'names'],
 *          'id_column' => '{id column name}'
 *          'compares' => '[{column} => 'equals|includes|starts|ends'],
 *          'container' => Entity|Collection::class,
 *          'type' => 'entity|collection|boolean'
 *      ]
 * 
 *
 * - API ACTION - This corresponds to the method name in the Resource/Mapper
 *          file. So a GET on a collection would call 'fetchAll' for example.
 *          Please note, if you change your method name (ie: fetch($id)
 *          becomes fetchOne($id) it will break this without some workarounds.
 *
 * - TABLE NAME - The name of the table in the database
 * 
 * - CRUD OPTIONS - These correspond to the zend-db sql abstraction commands.
 *          The factory will call Zend\Db\Sql\Sql()->crud action
 *          
 * - COLUMNS - If present, calls $crud->columns[$columns]
 * 
 * - ID_COLUMN - For actions that use a "WHERE `table`.`id` = ?" this will
 *          cause a column name other than 'id' to be used.
 *          
 * - COMPARES - Each entry in this array is keyed to the column name.
 *          If a column is not included it defaults to 'equals'
 *          - equals    WHERE `column` = value
 *          - includes  WHERE `column` LIKE '%value%'
 *          - starts    WHERE `column` LIKE 'value%'
 *          - ends      WHERE `column` LIKE '%value'
 *          
 * - CONTAINER - The class name of the return object for the action.
 *          Typically an Entity or a Collection class
 *          
 * - TYPE - The type of container. Usually 'entity', 'collection', or 'boolean'
 *          Does not have to be specified unless overriding default
 *          
 * - RELATIONS - Used if related tables need to be included in the query.
 *          Each entry is an associative array keyed by an identifier tag 
 *          (typically the column name). Each array has the following entries:
 *          - name      The name of the related table
 *          - on        The sql join string (table1.column = table2.column)
 *          - columns   Columns to select from the secondary table. Can use
 *                      an associative array for ['alias1' => 'column1']
 *          - type      The type of sql join (JOIN_LEFT, JOIN_INNER, etc) 
 */
return [
    'create' => [
        'table' => $table,
        'crud' => 'insert',
        'columns' => [],
        'container' => $entity
    ],

    'delete' => [
        'table' => $table,
        'crud' => 'delete',
        'id_column' => $idColumn
    ],

    'fetch' => [
        'table' => $table,
        'crud' => 'select',
        'columns' => [],
        'id_column' => $idColumn,
        'container' => $entity,
        'relations' => [
            'county' => [
                'name' => 'counties',
                'on' => 'cities.county = counties.id',
                'columns' => ['countyName' => 'name'],
                'type' => 'JOIN_LEFT'
            ]
        ]
    ],

    'fetchAll' => [
        'table' => $table,
        'crud' => 'select',
        'columns' => [],
        'order' => '',
        'container' => $collection
    ],
    
    'patch' => [
        'table' => $table,
        'crud' => 'update',
        'id_column' => $idColumn
    ],

];