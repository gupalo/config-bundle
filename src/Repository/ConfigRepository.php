<?php

namespace Gupalo\ConfigBundle\Repository;

use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Gupalo\ConfigBundle\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Gupalo\DateUtils\DateUtils;
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
        } elseif ($value instanceof DateTimeInterface) {
            $value = DateUtils::format($value);
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
     * @param mixed $default
     * @return mixed
     * @throws Throwable
     */
    public function getValue(string $name, $default = '')
    {
        $defaultValue = $this->defaults[$name] ?? $default;
        if (is_int($defaultValue)) {
            $result = $this->getIntValue($name);
        } elseif (is_float($defaultValue)) {
            $result = $this->getFloatValue($name);
        } elseif (is_bool($defaultValue)) {
            $result = $this->getBoolValue($name);
        } elseif (is_array($defaultValue)) {
            $result = $this->getArrayValue($name);
        } elseif ($defaultValue instanceof DateTimeInterface) {
            $result = $this->getDateTimeValue($name);
        } else {
            $result = $this->getStringValue($name);
        }

        return $result;
    }

    public function getIntValue(string $name, int $default = 0): int
    {
        return (int)$this->getStringValue($name, $default);
    }

    public function getFloatValue(string $name, float $default = 0.0): float
    {
        return (float)$this->getStringValue($name, $default);
    }

    public function getBoolValue(string $name, bool $default = false): bool
    {
        return (float)$this->getStringValue($name, $default);
    }

    /**
     * @param string $name
     * @param array $default
     * @return array
     * @throws Throwable
     */
    public function getArrayValue(string $name, array $default = []): array
    {
        return json_decode($this->getStringValue($name, json_encode($default, JSON_THROW_ON_ERROR)), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getDateTimeValue(string $name, $default = '1970-01-01'): DateTimeInterface
    {
        return DateUtils::create($this->getStringValue($name, $default));
    }

    public function getStringValue(string $name, $default = ''): string
    {
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->from('config')
            ->select('value')
            ->andWhere('name = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1);

        $value = $qb->execute()->fetchColumn(0);
        if ($value === false) {
            $value = (string)($this->defaults[$name] ?? $default);
            $this->getEntityManager()->getConnection()->insert('config', [
                'name' => $name,
                'value' => $value,
            ]);
        }

        return $value;
    }

    public function remove(string $name): void
    {
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->from('config')
            ->delete()
            ->andWhere('name = :name')
            ->setParameter('name', $name);

        $qb->execute();
    }

    public function removeBulk(array $names): void
    {
        if (!$names) {
            return;
        }

        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->from('config')
            ->delete()
            ->andWhere('name IN (:names)')
            ->setParameter('names', $names, Connection::PARAM_STR_ARRAY);

        $qb->execute();
    }

    public function removeLike(string $likeExpression): void
    {
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->from('config')
            ->delete()
            ->andWhere('name LIKE :expr')
            ->setParameter('expr', $likeExpression);

        $qb->execute();
    }
}
