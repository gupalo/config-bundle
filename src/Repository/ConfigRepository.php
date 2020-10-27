<?php

namespace Gupalo\ConfigBundle\Repository;

use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Gupalo\ConfigBundle\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Gupalo\DateUtils\DateUtils;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
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

    private AbstractAdapter $cache;

    public function __construct(ManagerRegistry $registry, array $defaults)
    {
        $this->defaults = $defaults;
        $this->cache = new FilesystemAdapter('config_bundle', 60);

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
            $this->saveCache($name, $value);

            $this->getEntityManager()->getConnection()->update(
                'config',
                ['name' => $name, 'value' => $value],
                ['name' => $name]
            );
        }
    }

    /**
     * @param string $name
     * @param string $default
     * @return array|bool|DateTimeInterface|float|int|string
     * @noinspection CallableParameterUseCaseInTypeContextInspection
     */
    public function getValue(string $name, $default = '')
    {
        $defaultValue = $this->defaults[$name] ?? $default;
        if (is_int($defaultValue)) {
            if ((string)$default === '') {
                $default = 0;
            }
            $result = $this->getIntValue($name, (int)$default);
        } elseif (is_float($defaultValue)) {
            if ((string)$default === '') {
                $default = 0.0;
            }
            $result = $this->getFloatValue($name, (float)$default);
        } elseif (is_bool($defaultValue)) {
            if ((string)$default === '') {
                $default = false;
            }
            $result = $this->getBoolValue($name, (bool)$default);
        } elseif (is_array($defaultValue)) {
            if ((string)$default === '') {
                $default = [];
            }
            $result = $this->getArrayValue($name, (array)$default);
        } elseif ($defaultValue instanceof DateTimeInterface) {
            if ((string)$default === '') {
                $default = '1970-01-01';
            }
            $result = $this->getDateTimeValue($name, $default);
        } else {
            $result = $this->getStringValue($name, $default);
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
        return (bool)$this->getStringValue($name, $default);
    }

    public function getArrayValue(string $name, array $default = []): array
    {
        try {
            $result = json_decode($this->getStringValue($name, json_encode($default, JSON_THROW_ON_ERROR)), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            $result = $default;
        }

        return $result;
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

        $value = $qb->execute()->fetchOne();
        if ($value === false) {
            $value = (string)($this->defaults[$name] ?? $default);
            $this->getEntityManager()->getConnection()->insert('config', [
                'name' => $name,
                'value' => $value,
            ]);
        }

        return $value;
    }

    public function getStringValueCached(string $name, $default = ''): string
    {
        try {
            $result = $this->cache->get($name, function (ItemInterface $item) use ($name, $default) {
                return $this->getStringValue($name, $default);
            });
        } catch (InvalidArgumentException $e) {
            $result = $this->getStringValue($name, $default);
        }

        return $result;
    }

    public function remove(string $name): void
    {
        $this->deleteCache($name);

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

        foreach ($names as $name) {
            $this->deleteCache($name);
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
        // important: cannot remove cache

        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->from('config')
            ->delete()
            ->andWhere('name LIKE :expr')
            ->setParameter('expr', $likeExpression);

        $qb->execute();
    }

    private function saveCache(string $name, bool $value): void
    {
        try {
            $cacheItem = $this->cache->getItem($name);
            $cacheItem->set($value);
            $this->cache->save($cacheItem);
        } catch (InvalidArgumentException $e) {
        }
    }

    private function deleteCache(string $name): void
    {
        try {
            $this->cache->delete($name);
        } catch (InvalidArgumentException $e) {
        }
    }
}
