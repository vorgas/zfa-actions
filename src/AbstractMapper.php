<?php
namespace vorgas\ZfaActions;

use Zend\Db\Adapter\AdapterInterface;
use vorgas\ZfaActions\ApiActions\ApiActionFactoryInterface;

/**
 *
 * @author pastech
 *        
 */
class AbstractMapper
{
    protected $adapter;
    protected $apiActionFactory;
    private $child;
        
    public function __construct(AdapterInterface $adapter, ApiActionFactoryInterface $apiActionFactory)
    {
        $this->adapter = $adapter;
        $this->apiActionFactory = $apiActionFactory;
        $this->child = get_class($this);
    }
    
    public function create($data)
    {
        $action = $this->apiActionFactory->build($this->adapter, $this->child . '::create', $data);
        return $action->execute();
    }
    
    public function delete($id)
    {
        $action = $this->apiActionFactory->build($this->adapter, $this->child . '::delete', $id);
        return $action->execute();
    }
    
    public function fetch($id)
    {
        $action = $this->apiActionFactory->build($this->adapter, $this->child . '::fetch', $id);
        return $action->execute();
    }
    
    public function fetchAll($params)
    {
        $action = $this->apiActionFactory->build($this->adapter, $this->child . '::fetchAll', $params);
        return $action->execute();
    }
    
    public function patch($id, $data)
    {
        $action = $this->apiActionFactory->build($this->adapter, __METHOD__, $data, $id);
        return $action->execute();
    }
}

