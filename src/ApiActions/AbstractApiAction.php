<?php
namespace vorgas\ZfaApiActions\ApiActions;

use Zend\Db\Sql\SqlInterface;
use ZF\ApiProblem\ApiProblem;
use function GuzzleHttp\json_encode;

/**
 *
 * @author pastech
 *        
 */
abstract class AbstractApiAction implements ApiActionInterface
{
    protected $container;
    protected $crud;
    protected $exception;
    protected $sql;
    
    /**
     */
    public function __construct()
    {
        $this->exception = 'vorgas\ZfaApiActions\PdoProblemFactory::build';
    }

    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::execute()
     *
     */
    public function execute()
    {
        // I think this was making sure everything was set
        /* It doesn't really seem to be applicapble anymore. Maybe try to
            cause problems and see what happens? 
        $invalidProperties = $this->validateProperties();
        if (! is_bool($invalidProperties)) return $invalidProperties;
        */
        $statement = $this->sql->prepareStatementForSqlObject($this->crud);
        try {
            $result = $statement->execute();
        } catch (\Exception $e) {
            return call_user_func($this->exception, $e, get_class($this));
        }
        return $this->resultToContainer($result);
    }

    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::getContainer()
     *
     */
    public function getContainer()
    {
        return $this->container;
    }

    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::getCrud()
     *
     */
    public function getCrud(): SqlInterface
    {
        return $this->crud;
    }

    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::getException()
     *
     */
    public function getException(): callable
    {
        return $this->exception;
    }

    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::getSql()
     *
     */
    public function getSql()
    {
        return $this->sql;
    }

    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::prepare()
     *
     */
    public function prepare($sql = null, SqlInterface $crud = null, $container = null, callable $exception = null)
    {
        if (! is_null($sql)) $this->setSql($sql);
        if (! is_null($crud)) $this->setCrud($crud);
        if (! is_null($container)) $this->setContainer($container);
        if (! is_null($exception)) $this->setException($exception);
    }

    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::setContainer()
     *
     */
    public function setContainer($container, $data = null)
    {
        $this->container = $container;
    }

    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::setCrud()
     *
     */
    public function setCrud(SqlInterface $crud, $data = null)
    {
        $this->crud = $crud;
    }

    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::setException()
     *
     */
    public function setException(callable $exception, $data = null)
    {
        $this->exception = $exception;
    }

    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::setSql()
     *
     */
    public function setSql($sql, $data = null)
    {
        $this->sql = $sql;
    }
    
    
    /**
     * I think this was to help make sure everything was set before
     * allowing execution, it doesn't seem to work nor does it seem
     * to be needed :)
     * 
     * @return StdClass|boolean
     */
    private function validateProperties()
    {
        foreach (get_class_vars(get_class($this)) as $key => $value) {
            if (is_null($this->$key)) return (object) ['invalid' => $key];
        }
        return true;
    }
    
    
    /**
     * (non-PHPdoc)
     *
     * @see \vorgas\ZfaApiActions\ApiActions\ApiActionInterface::convertData()
     *
     */
    abstract public function convertData($data, $id): array;
    
    
    /**
     * Must return the correct result container
     * 
     * @param mixed $result
     */
    abstract protected function resultToContainer($result);
}

