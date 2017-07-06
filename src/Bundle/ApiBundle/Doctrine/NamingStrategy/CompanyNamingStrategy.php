<?php

namespace ApiBundle\Doctrine\NamingStrategy;

use Doctrine\ORM\Mapping\NamingStrategy;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class CompanyNamingStrategy implements NamingStrategy
{
    protected $map;

    protected $prefix;

    public function __construct(KernelInterface $kernel)
    {
        $this->map = $this->getNamingMap($kernel);
        $this->prefix = $kernel->getContainer()->getParameter('doctrine_table_prefix');
    }

    private function getNamingMap(KernelInterface $kernel)
    {
        $map = [];
        /**
         * @var BundleInterface $bundle ;
         */
        foreach ($kernel->getBundles() as $bundle) {
            $bundleNamespace = (new \ReflectionClass(get_class($bundle)))->getNamespaceName();
            $bundleName = $bundle->getName();
            if (isset($configuration['map'][$bundleName])) {
                $map[$this->underscore($configuration['map'][$bundleName])] = $bundleNamespace;
                continue;
            }
            $bundleName = preg_replace('/Bundle$/', '', $bundleName);
            if (isset($configuration['map'][$bundleName])) {
                $map[$this->underscore($configuration['map'][$bundleName])] = $bundleNamespace;
                continue;
            }
            $map[$this->underscore($bundleName)] = $bundleNamespace;
        }

        return $map;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function underscore($string)
    {
        $string = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string);

        return strtolower($string);
    }

    /**
     * Returns a column name for an embedded property.
     *
     * @param string $propertyName
     * @param string $embeddedColumnName
     *
     * @return string
     */
    public function embeddedFieldToColumnName(
        $propertyName,
        $embeddedColumnName,
        $className = null,
        $embeddedClassName = null
    ) {
        return $this->underscore($propertyName).'_'.$this->underscore($embeddedColumnName);
    }

    /**
     * Returns a column name for a property.
     *
     * @param string $propertyName A property name.
     * @param string|null $className The fully-qualified class name.
     *
     * @return string A column name.
     */
    public function propertyToColumnName($propertyName, $className = null)
    {
        return $this->underscore($propertyName);
    }

    /**
     * Returns a join column name for a property.
     *
     * @param string $propertyName A property name.
     *
     * @return string A join column name.
     */
    public function joinColumnName($propertyName)
    {
        return $this->underscore($propertyName).'_'.$this->referenceColumnName();
    }

    /**
     * Returns the default reference column name.
     *
     * @return string A column name.
     */
    public function referenceColumnName()
    {
        return 'id';
    }

    /**
     * Returns a join table name.
     *
     * @param string $sourceEntity The source entity.
     * @param string $targetEntity The target entity.
     * @param string|null $propertyName A property name.
     *
     * @return string A join table name.
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        $tableName = $this->classToTableName($sourceEntity).'_'.$this->underscore(
                substr($targetEntity, strrpos($targetEntity, '\\') + 1)
            );

        return
            $tableName;
    }

    /**
     * Returns a table name for an entity class.
     *
     * @param string $className The fully-qualified class name.
     *
     * @return string A table name.
     */
    public function classToTableName($className)
    {
        $prefix = str_replace('app_verk_', '', $this->getTableNamePrefix($className));

        return $this->prefix.$prefix.'_'.$this->underscore(substr($className, strrpos($className, '\\') + 1));
    }

    protected function getTableNamePrefix($className)
    {
        $className = ltrim($className, '\\');
        foreach ($this->map as $prefix => $namespace) {
            if (strpos($className, $namespace) === 0) {
                return $prefix.'_';
            }
        }

        return '';
    }

    /**
     * Returns the foreign key column name for the given parameters.
     *
     * @param string $entityName An entity.
     * @param string|null $referencedColumnName A property.
     *
     * @return string A join column name.
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        return $this->classToTableName($entityName).'_'.
            ($referencedColumnName ? $this->underscore($referencedColumnName) : $this->referenceColumnName());
    }
}