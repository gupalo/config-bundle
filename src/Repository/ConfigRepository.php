<?php

namespace Gupalo\ConfigBundle\Repository;

use Gupalo\ConfigBundle\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Throwable;

/**
 * @method Config|null find($id, $lockMode = null, $lockVersion = null)
 * @method Config|null findOneBy(array $criteria, array $orderBy = null)
 * @method Config[]    findAll()
 * @method Config[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigRepository extends ServiceEntityRepository
{
    private array $defaults;

    public function __construct(ManagerRegistry $registry, array $defaults)
    {
        $this->defaults = $defaults;

        parent::__construct($registry, Config::class);
    }

    /**
     * @param string $name
     * @param $value
     * @throws Throwable
     */
    public function setValue(string $name, $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_THROW_ON_ERROR, 512);
        } elseif (is_bool($value)) {
            $value = $value ? 1 : 0;
        } else {
            $value = (string)$value;
        }

        if ($value !== $this->getValue($name)) { // it will create value if not exist
            $this->getEntityManager()->getConnection()->update(
                'config',
                ['name' => $name, 'value' => $value],
                ['name' => $name]
            );
        }
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Throwable
     */
    public function getValue(string $name)
    {
        $defaultValue = $this->defaults[$name];
        if (is_int($defaultValue)) {
            $result = $this->getIntValue($name);
        } elseif (is_float($defaultValue)) {
            $result = $this->getFloatValue($name);
        } elseif (is_bool($defaultValue)) {
            $result = $this->getBoolValue($name);
        } elseif (is_array($defaultValue)) {
            $result = $this->getArrayValue($name);
        } else {
            $result = $this->getStringValue($name);
        }

        return $result;
    }

    public function getIntValue(string $name): int
    {
        return (int)$this->getStringValue($name);
    }

    public function getFloatValue(string $name): float
    {
        return (float)$this->getStringValue($name);
    }

    public function getBoolValue(string $name): bool
    {
        return (float)$this->getStringValue($name);
    }

    /**
     * @param string $name
     * @return array
     * @throws Throwable
     */
    public function getArrayValue(string $name): array
    {
        return json_decode($this->getStringValue($name), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getStringValue(string $name): string
    {
        if (!array_key_exists($name, $this->defaults)) {
            throw new InvalidArgumentException(sprintf('cannot find config "%s"', $name));
        }

        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->from('config')
            ->select('value')
            ->andWhere('name = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1);

        $value = $qb->execute()->fetchColumn(0);
        if ($value === false) {
            $value = (string)$this->defaults[$name];
            $this->getEntityManager()->getConnection()->insert('config', [
                'name' => $name,
                'value' => $value,
            ]);
        }

        return $value;
    }
}
