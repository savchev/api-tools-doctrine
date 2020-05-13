<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-doctrine for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-doctrine/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-doctrine/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Doctrine\Server\Query\Provider;

use Doctrine\Persistence\ObjectManager as PersistenceObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Laminas\ApiTools\Doctrine\Server\Paginator\Adapter\DoctrineOrmAdapter;
use Laminas\ApiTools\Rest\ResourceEvent;
use Laminas\Paginator\Adapter\AdapterInterface;

abstract class AbstractQueryProvider implements ObjectManagerAwareInterface, QueryProviderInterface
{
    /**
     * @var PersistenceObjectManager
     */
    protected $objectManager;

    /**
     * Set the object manager
     *
     * @param PersistenceObjectManager $objectManager
     */
    public function setObjectManager(PersistenceObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get the object manager
     *
     * @return PersistenceObjectManager
     */
    public function getObjectManager(): PersistenceObjectManager
    {
        return $this->objectManager;
    }

    /**
     * @param ResourceEvent $event
     * @param string $entityClass
     * @param array $parameters
     * @return mixed This will return an ORM or ODM Query\Builder
     */
    abstract public function createQuery(ResourceEvent $event, $entityClass, $parameters);

    /**
     * @param $queryBuilder
     * @return AdapterInterface
     */
    public function getPaginatedQuery($queryBuilder)
    {
        return new DoctrineOrmAdapter($queryBuilder->getQuery(), false);
    }

    /**
     * @param $entityClass
     * @return int
     */
    public function getCollectionTotal($entityClass)
    {
        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $cmf = $this->getObjectManager()->getMetadataFactory();
        $entityMetaData = $cmf->getMetadataFor($entityClass);

        $identifier = $entityMetaData->getIdentifier();
        $queryBuilder
            ->select('count(row.' . $identifier[0] . ')')
            ->from($entityClass, 'row');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
