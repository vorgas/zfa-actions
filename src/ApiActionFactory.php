<?php
/**
 * Zend Framework 3 interaction library
 *
 * This file is part of a suite of software to ease interaction with ZF3,
 * particularly Apigility.
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2017 Mike Hill
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
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace vorgas\ZfaApiActions;

use Zend\Db\Adapter\AdapterInterface;
use vorgas\ZfaApiActions\ApiActions\AbstractApiAction;
use ZF\ApiProblem\ApiProblem;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\SqlInterface;
use Zend\Paginator\Adapter\DbSelect;
use vorgas\ZfaApiActions\ApiActions\ApiActionInterface;
use vorgas\ZfaApiActions\ApiActions\ApiActionFactoryInterface;


/**
 * Creates an appropriate api action class based on the calling method
 *
 * Make sure a copy of apiactions.service.php is in module/{module}/config
 * It should be called apiactions.{SERVICE}.php. It tells this all how to
 * function.
 *
 * You can easily create your own special action classes tuned to the name
 * of the calling method. For example, if you use a mapper to the resource
 * where fetch($id) calls $mapper->fetchOne($id), then you could create an
 * class ApiFetchOne in ApiActions/. This factory would then call it
 * automatically.
 */
class ApiActionFactory implements ApiActionFactoryInterface
{
    /**
     * Creates the appropriate api action class, initializes and returns it
     * The basic flow here is as follows:
     *  - Split up the method name to get the necessary parts for processing
     *  - Create the ApiAction class based on the method name
     *  - Convert GET parameters and other info into a valid data structure
     *  - Read in the appropriate config array from the apiactions file
     *  - Move the info from the data structure into the config array
     *  - Clean up the config array, removing unnecessary entries
     *  - Tell the ApiAction class to construct the sql object
     *  - return the ApiAction class, all primed and ready to go!
     *
     * @param AdapterInterface $adapter     Database adapter
     * @param string $method                The calling method. Just use __METHOD__
     * @param mixed $data                   Any additional data
     * @param mixed $id                     Used for patch, etc where an $id is also provided
     * @return ApiActionInterface
     */
    public function build(AdapterInterface $adapter, string $method, $data, $id = null): ApiActionInterface
    {
        $method = $this->parseMethod($method);
        $config = $this->loadConfigFile($method);
        $sql = $this->buildSql($adapter);
        $crud = $this->buildCrud($sql, $config);
        $class = $this->buildApiAction($method);
        $data = $class->convertData($data, $id);
        $config = $this->mergeDataAndConfig($data, $config);
        $config = $this->cleanConfig($config);
        $crud = $this->constructCrud($config, $crud);
        $container = $this->buildContainer($config, $adapter, $crud);
        $class->prepare($sql, $crud, $container);
        return $class;
    }

    
    /**
     * Costructs and returns the api action class
     * 
     * Uses the name of the method to determine the class to create. For 
     * example, if the method is FetchAll then it creates ApiFetchAll. If the
     * method is Foo then it creates ApiFoo.
     * 
     * In this way, a new api action can be created simply by adding the new
     * class in the ./ApiActions directory. Just make sure it extends the
     * AbstractApiAction class.
     * 
     * @param \stdClass $method
     * @return AbstractApiAction
     */
    private function buildApiAction(\stdClass $method): AbstractApiAction
    {
        $fqClass = sprintf('%s\ApiActions\Api%s', __NAMESPACE__, ucfirst($method->action));
        return new $fqClass();
    }
    
    
    /**
     * Uses the $config array to determine the type of result container
     * 
     * @note I'm not wild about this implementation. It should probably
     * go to a result factory. But as long as it only needs these three
     * options, I can keep it.
     * 
     * @param array $config
     * @param AdapterInterface $adapter
     * @param SqlInterface $crud
     * @return unknown|boolean
     */
    private function buildContainer(array $config, AdapterInterface $adapter, SqlInterface $crud)
    {
        $container = $config['container'];
        $type = $this->determineContainerType($config);
        switch ($type)
        {
            case 'collection':
                $paginatorAdapter = new DbSelect($crud, $adapter);
                return new $container($paginatorAdapter);
                break;
                
            case 'entity':
                return new $container();
                break;
                
            default:
                return true;
        }
    }
    
    
    /**
     * Uses the $config array to determine the type of CRUD object to make
     * 
     * @param Sql $sql
     * @param array $config
     * @return SqlInterface
     */
    private function buildCrud(Sql $sql, array $config): SqlInterface
    {
        return $sql->{$config['crud']}($config['table']);
    }
    
    /**
     * Constructs and returns a new Sql handler
     *
     * @param AdapterInterface $adapter
     * @return SqlInterface
     */
    private function buildSql(AdapterInterface $adapter): Sql
    {
        return new Sql($adapter);
    }


    /**
     * Returns the config array with the 'where' filters added
     *
     * Looks for unknown names in $data and converts those into filter
     * entries. For example 'name=mike' would try to find 'mike' in the
     * 'name' column of the database.
     *
     * apiactions.{SERVICE}.php can specify a default comparison for
     * each column accepted in the table. The type of search can also
     * be specified in query string. For example: name=mike:includes
     * would search in the 'name' column for '%mike%'.
     *
     * @param \stdClass $data
     * @param array $config
     * @return array
     */
    private function buildFilter(array $data, array $config): array
    {
        if (! array_key_exists('params', $data)) return $config;
        $params = $data['params'];
        $where = [];
        $compareMap = [
            'equals' => ['compare' => 'equalTo', 'value' => '%s'],
            'includes' => ['compare' => 'like', 'value' => '%%%s%%'],
            'starts' => ['compare' => 'like', 'value' => '%s%%'],
            'ends' => ['compare' => 'like', 'value' => '%%%s']
        ];
        
        foreach ($params as $col => $value)
        {
            $parts = $this->getValueAndCompare($value, $col, $config);
            if (! array_key_exists($parts->compare, $compareMap)) $parts->compare = 'includes';
            $compArray = $compareMap[$parts->compare];
            $where[$col] = [
                'compare' => $compArray['compare'],
                'value' => sprintf($compArray['value'], $parts->value)
            ];
        }
        if (count($where)) $config['where'] = $where;
 
        return $config;
    }


    /**
     * Removes unwanted key entries from the $config array and returns it
     *
     * @param mixed[] $config
     * @return mixed[]
     */
    private function cleanConfig(array $config): array
    {
        $unusedKeys = ['compares', 'params'];
        foreach ($unusedKeys as $key) {
            if (array_key_exists($key, $config)) unset($config[$key]);
        }
        return $config;
    }


    /**
     * Builds the sql command based on the config property
     *
     * Looks for various keys in $config and turns those into zend-db
     * crud methods. For example, 'columns' becomes $sql->select->columns()
     */
    private function constructCrud(array $config, SqlInterface $crud): SqlInterface
    {
        foreach ($config as $key => $value) {
            if (! method_exists($crud, $key)) continue;
            
            // Dont do anything with empty arrays
            if (is_array($value)) {
                if (! count($value)) continue;
    
            // Dont do anything with empty strings either
            } elseif ($value === null) {
                continue;
    
            // Sometimes the array comes in as an object, so switch it
            } elseif (is_object($value)) {
                $value = (array) $value;
            }
    
            // Some config keys require special processing
            switch ($key)
            {
                case 'where':
                    $crud = $this->where($crud, $value);
                    break;
    
                default:
                    $crud->{$key}($value);
            }
        }
        
        return $crud;
    }
    

    /**
     * Copies parameter keys from the data object to the config array.
     *
     * To move all data to config logic in this class, the ApiAction
     * classes will often create a 'param' property in the $data object.
     * This allows the common ones to be copied from the $data object
     * to the $config array appropriately.
     *
     * @param string $key
     * @param \stdClass $data
     * @param mixed[] $config
     * @return mixed[]
     */
    private function copyKeyFromParamsToConfig(string $key, array $data, array $config): array
    {
        if (! array_key_exists('params', $data)) return $config;
        $params = $data['params'];
        if (! array_key_exists($key, $params)) return $config;
        $config[$key] = explode(':', $params[$key]);
        return $config;
    }


    /**
     * Determines the type of container
     * 
     * If the type of container is specified in the config array, use it.
     * Otherwise, determine the type of container based on the class name
     * of the container object. For example, an Entity object will use 'entity'.
     * 
     * @param array $config
     * @return string
     */
    private function determineContainerType(array $config): string
    {
        // If the type of container is specified, use that
        if (array_key_exists('type', $config)) return $config['type'];
        
        // Look for 'entity' or 'collection' in the container class name
        $container = strtolower($config['container']);
        if (strpos($container, 'entity')) return 'entity';
        if (strpos($container, 'collection')) return 'collection';
        return 'boolean';
    }
    
    
    /**
     * Returns the comparison string and the value for a column as an object
     *
     * $value can take the form of 'search' or 'search:comparison'. If the
     * comparison is specified in the query string, use it. If not, use the
     * corresponding entry in apiactions.{SERVICE}.php. If it's not there,
     * default to 'equals'.
     *
     * The object has two string properties only:
     *  - value     The value to use in the search string
     *  - compare   The type of comparison to be used (equals, includes, etc).
     *
     * @param string $value
     * @param string $col
     * @param mixed[] $config
     * @return object
     */
    private function getValueAndCompare(string $value, string $col, array $config): \stdClass
    {
        $parts = explode(':', $value);
        $value = $parts[0];
        $compare = $parts[1] ?? $config['compares'][$col] ?? 'equals';
        return (object) ['value' => $value, 'compare' => $compare];
    }


    /**
     * Converts $data object properties into $config array entries
     *
     * The config array is gathered from apiactions.{SERVICE}.php. The
     * data object is created during the construction of the ApiAction
     * class. The information from $data is then transferred into the
     * $config array and the array is returned.
     *
     * Whatever information is left in the $data object is appended to
     * the end of the $config array.
     *
     * @param \stdClass $data
     * @param mixed[] $config
     * @return mixed[]
     */
    private function mergeDataAndConfig(array $data, array $config): array
    {
        $data = $this->moveIdToParams($data, $config);
        $config = $this->copyKeyFromParamsToConfig('order', $data, $config);
        $config = $this->normalizeConfigOrder($config);
        $config = $this->copyKeyFromParamsToConfig('columns', $data, $config);
        $data = $this->trimParamsOfKeys($data, ['order', 'columns']);
        $config = $this->buildFilter($data, $config);
        $data = (array) $data;
        return array_merge($config, $data);
    }


    /**
     * Moves an 'id' property from in $data into the 'param' property
     *
     * Some actions, such as fetch($id) or patch($id, $data) send $id as
     * a separate entry. However, the ApiAction class does not know the
     * appropriate name of the id column during it's construction. To
     * get around this, an 'id' property is added to the $data object.
     *
     * This method then converts that property into a standard 'param'
     * property. When the 'param' property is parsed, the appropriate
     * WHERE filter will be added.
     *
     * @param \stdClass $data
     * @param mixed[] $config
     * @return \stdClass
     */
    private function moveIdToParams(array $data, array $config): array
    {
        if (array_key_exists('id', $data)) {
            $idColumn = $config['id_column'] ?? 'id';
            $data['params'][$idColumn] = $data['id'] . ':equals';
            unset($data['id']);
        }
        return $data;
    }


    /**
     * Turns raw order entries into something zend-db sql can use
     *
     * zend-db Sql can use strings, arrays, etc to build it's ORDER
     * command. Since a query string might have a direction along
     * with the columns, it needs to be able to handle that.
     *
     * @param array $config
     * @return array
     */
    private function normalizeConfigOrder(array $config): array
    {
        if (! array_key_exists('order', $config)) return $config;
        $sort = [];
        $order = $config['order'];
        if (is_string($order)) $order = [$order];
        foreach ($order as $entry) {
            $direction = 'ASC';
            if (substr($entry, 0, 1) == '-') {
                $direction = 'DESC';
                $entry = substr($entry, 1);
            }
            $sort[] = "$entry $direction";
        }
        $config['order'] = $sort;
        return $config;
    }

    
    /**
     * Extracts the configuration array for this module and service
     *
     * The array resides in module/{module}/config/apiactions.{SERVICE}.php.
     * @param object $method
     * @return mixed[]
     */
    private function loadConfigFile(\stdClass $method): array
    {
        $configFile = "module/$method->module/config/apiactions.$method->service.php";
        $config = include $configFile;
        return $config[$method->action];
    }


    /**
     * Break down the method to find the necessary parts
     *
     * Returns an object with the following properties:
     *  - module    The primary module calling this factory
     *  - action    The api action being taken (fetch, fetchAll, create)
     *  - service   The api service name
     *
     * @param string $method
     * @return object
     */
    private function parseMethod(string $method): \stdClass
    {
        $return = [];
        $method = explode('\\', $method);
        $return['module'] = $method[0];
        $return['action'] = explode('::', end($method))[1];
        $return['service'] = strtolower(prev($method));
        return (object) $return;
    }


    /**
     * Removes specified entries from the 'params' property of the $data object
     *
     * The 'params' property is a catch-all property initialized when the
     * ApiAction class is constructed. At the time, that class is unaware of
     * possible configuration options, so it just shoves any extra GET
     * parameters, etc into this property.
     *
     * Later on, once all the known keys are processed, anything left is
     * assumed to be a filter on a column. This allows the $data->params to
     * be trimmed of known keys, so that works efficiently.
     *
     * @param \stdClass $data
     * @param array $keys
     * @return object
     */
    private function trimParamsOfKeys(array $data, array $keys): array
    {
        if (! array_key_exists('params', $data)) return $data;
        foreach ($keys as $key) {
            if (! array_key_exists($key, $data['params'])) continue;
            unset($data['params'][$key]);
        }
        return $data;
    }
    
    
    /**
     * Adds any WHERE clauses to the sql command
     *
     * $crud->where uses an additional command to build the sql
     * so I have to use a separate function. In addition, some
     * actions, such as IsNull or IsNotNull use a different
     * number of arguments as well.
     */
    private function where($crud, $array)
    {
        foreach ($array as $col => $data) {
            $compare = $data['compare'];
            $value = $data['value'];
            $crud->where->{$compare}($col, $value);
        }
        return $crud;
    } 
}

