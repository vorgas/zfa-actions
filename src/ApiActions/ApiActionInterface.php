<?php
namespace vorgas\ZfaApiActions\ApiActions;

use Zend\Db\Sql\SqlInterface;

interface ApiActionInterface
{
    /**
     * Converts $data and $id into a well-formed data array
     *
     * $data may be a list of get parameters, an id number, post
     * body updates, etc. This method must convert $data and $id
     * into valid config array entries.
     *
     * @param mixed $data
     * @param mixed $id
     * @return mixed[]
     */
    public function convertData($data, $id): array;
    
    
    /**
     * Sets the sql property to the supplied object
     * 
     * An object that implements the ApiActionInterface must track the Sql object used
     * to prepare and execute statements. If not a Zend\Db\Sql object, it needs to mimic
     * the same methods and properties.
     * 
     * This is most likely done by setting a property named sql, but may implement any other
     * form of storage. To that end, the $data parameter is included to allow you to pass in
     * any other additional information.
     * 
     * @param Sql $sql
     * @param mixed[] $data
     */
    public function setSql($sql, $data = null);
    
    
    /**
     * Sets the crud property to the supplied object
     * 
     * An object that implements the ApiActionInterface must track the CRUD object used
     * to prepare and execute statements. The crud object needs to implement the
     * Zend\Db\Sql\SqlInterface.
     * 
     * This is most likely done by setting a property named crud, but may implement any other
     * form of storage. To that end, the $data parameter is included to allow you to pass in
     * any other additional information.
     * 
     * @param SqlInterface $crud
     * @param mixed[] $data
     */
    public function setCrud(SqlInterface $crud, $data = null);
    
    
    /**
     * Sets the container property to the supplied object
     * 
     * An object that implements the ApiActionInterface must track the type of result
     * container used for handling the result. This is often an entity, a collection, a
     * boolean, etc.
     * 
     * This is most likely done by setting a property named container, but may implement any
     * other form of storage. To that end, the $data parameter is included to allow you to
     * pass in any other additional information.
     * 
     * @param unknown $container
     * @param mixed[] $data
     */
    public function setContainer($container, $data = null);
    
    
    /**
     * Sets the exception property to the supplied callable
     * 
     * An object that implements the ApiActionInterface must track the callback in the event
     * of a sql error. The callback must only accept two parameters. The first is the 
     * exception object thrown when executing the sql statement. The second is the name of
     * the class calling the error.
     * 
     * This is most likely done by setting a property named exception, but may implement any
     * other form of storage. To that end, the $data parameter is included to allow you to
     * pass in any other additional information.
     * 
     * @param callable $exception
     * @param mixed[] $data
     */
    public function setException(callable $exception, $data = null);
    
    
    
    /**
     * Retrieves the object responsible for performing basic sql operations
     * 
     * This is typically a Zend\Db\Sql object, but may be something else that mimics the
     * same functionality. Unfortunately, Zend\Db\Sql does not implement an interface,
     * or extend an abstract class, so the return type can't be specified.
     * 
     * @return Zend\Db\Sql
     */
    public function getSql();
    
    
    /**
     * Retrieves the crud object responsible for building the actual sql statement
     * 
     * @return SqlInterface
     */
    public function getCrud(): SqlInterface;
    
    
    /**
     * Retrieves the object used to handle a successful query result
     * 
     * Depending on the type of query, this may be a collection, entity, or boolean
     * 
     * @return mixed
     */
    public function getContainer();
    
    
    /**
     * Retrieves the callback function used to handle a sql error
     * 
     * @return callable
     */
    public function getException(): callable;
    
    
    /**
     * A shortcut for setting all four of the primary properties at the same time
     * 
     * Please see the description for set{Property} for more information about each of
     * the properties. With this method, however, additional data cannot be sent along.
     * Ergo, this method cannot be used if extra information is required to set the
     * property.
     * 
     * @param Sql $sql
     * @param SqlInterface $crud
     * @param unknown $container
     * @param callable $exception
     */
    public function prepare($sql, SqlInterface $crud, $container, callable $exception);
    
    
    /**
     * Executes the crud statement and returns either the result or an error
     * 
     * All four of the main properties (sql, crud, container, exception) must be set for
     * this method to work. If any of them are not set, this method must return a \stdClass
     * object containing one property 'invalid' set to the name of the unset property.
     * 
     * If the sql action is a success, it must return the appropriate container object.
     * 
     * If the sql action results in an error, it should return an ApiError object with
     * corresponding information.
     * 
     * @return mixed
     */
    public function execute();  
}
