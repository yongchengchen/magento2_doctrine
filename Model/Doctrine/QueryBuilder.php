<?php
namespace Yong\Doctrine\Model\Doctrine;

use Doctrine\ORM\QueryBuilder as OriginQueryBuilder;

class QueryBuilder {
    private $originBuilder;
    private $hasWhere = false;
    private $extra_tb_name = 'dttm';
    public function __construct(OriginQueryBuilder $originBuilder) {
        $this->originBuilder = $originBuilder;
    }

    public function from($table, $extra=null) {
        $this->extra_tb_name = $extra ? $extra : 'dttm';
        $this->originBuilder->from($table, $this->extra_tb_name);
        return $this;
    }

    public function select(array $fields) {
        $myfields =[];
        foreach($fields as $field) {
            $myfields[] = $this->extra_tb_name . '.' . $field;
        }

        $this->originBuilder->select($myfields);
        return $this;
    }

    public function where($field, $cond, $value, $and='and') {
        $action = ($and =='or') ? 'orWhere' : ( $this->hasWhere ? 'andWhere' : 'where');
        $this->originBuilder->{$action}($this->originBuilder->expr()->{$cond}($this->extra_tb_name . '.' . $field, $value));
        $this->hasWhere = true;
        return $this;
    }

    public function whereLike($field, $value, $and = 'and') {
        $action = ($and =='or') ? 'orWhere' : ( $this->hasWhere ? 'andWhere' : 'where');
        $this->originBuilder->{$action}(sprintf('%s.%s like :%s', $this->extra_tb_name, $field, $field));
        $this->originBuilder->setParameter($field, $value);
        $this->hasWhere = true;
        return $this;
    }
    
    public function orWhere($field, $cond, $value) {
        return $this->where($field, $cond, $value, 'or');
    }

    public function orderBy($field, $sort='ASC') {
        $this->originBuilder->orderBy($this->extra_tb_name . '.' . $field, $sort);
        return $this;
    }

    public function __call($method, $argv) {
        $result = $this->originBuilder->{$method}(...$argv);
        if ($result instanceof OriginQueryBuilder) {
            return $this;
        }
        return $result;
    }

    public function get($limit = 0) {
        $query = $this->originBuilder->getQuery();
        if ($limit > 0) {
            $query->setMaxResults($limit);
        }
        return $query->getScalarResult();
    }

    public function first() {
        return $this->originBuilder->getQuery()->getSingleResult();
    }

    public function pagination($currentPage, $pageSize) {
        // echo $this->originBuilder->getQuery()->getSql();die;
        $queryText = sprintf('select count(id_0) from (%s)', $this->originBuilder->getQuery()->getSql());
        $myqb = clone $this->originBuilder;
        $selectParts = $myqb->getDQLPart('select');
        $selectParts = $selectParts[0]->getParts();
        $myqb->select(sprintf('count(%s) as paginator_total', $selectParts[0]));
        $ret = $myqb->getQuery()->getSingleResult();

        $paginator  = new \Doctrine\ORM\Tools\Pagination\Paginator($this->originBuilder->getQuery());
        $totalItems = $ret['paginator_total'];
        $pagesCount = ceil($totalItems / $pageSize);

        // now get one page's items:
        $collection = $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($currentPage-1)) // set the offset
            ->setMaxResults($pageSize)
            ->getScalarResult();
        
        return [
            'total' => $totalItems, 
            'page_count' => $pagesCount, 
            'current_page' => $currentPage,
            'items' => $collection
        ];
    }
}