<?php

namespace App\Traits;

use Closure;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function Symfony\Component\String\u;

trait PaginationQueryBuilder
{
    /** @var QueryBuilder */
    private QueryBuilder $query;

    /** @var array */
    private array $scopes;

    /**
     * @param QueryBuilder $query
     * @return self
     */
    public function setQuery(QueryBuilder $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param array $scopes
     * @return self
     */
    public function setScope(string $key, Closure $closure): self
    {
        $this->scopes[$key] = $closure;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function scopeHandler(string $key, $value)
    {
        if (array_key_exists($key, $this->scopes)) {
            $this->query = $this->scopes[$key]($this->query, $value);
            return true;
        }

        return false;
    }

    /**
     * @param array $filters
     * @return self
     */
    public function filters($filters): self
    {
        if (!is_null($filters)) {
            foreach ($filters as $key => $value) {
                if ($this->scopeHandler($key, $value)) {
                    continue;
                }
                
                $prepare = u('h.')
                    ->append($key)
                    ->append(' = :')
                    ->append($key)
                    ->toString();

                $this->query
                    ->andWhere($prepare)
                    ->setParameter($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param int $page
     * @param int $size
     * @return array
     */
    public function paginate(int $page, int $size)
    {
        $paginator = new Paginator($this->query->getQuery());
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $size);

        $paginator
            ->getQuery()
            ->setFirstResult($size * ($page-1))
            ->setMaxResults($size);

        return [
            'data' => $paginator->getIterator()->getArrayCopy(),            
            'total' => $totalItems,
            'per_page' => $size,
            'current_page' => $page,
            'last_page' => $pagesCount
        ];
    }
}
