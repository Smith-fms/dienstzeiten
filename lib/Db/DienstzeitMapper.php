<?php

namespace OCA\DienstzeitenApp\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class DienstzeitMapper extends QBMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'dienstzeiten_entries', Dienstzeit::class);
    }

    /**
     * @param int $id
     * @return Entity|Dienstzeit
     * @throws DoesNotExistException
     */
    public function find($id): Entity {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
           ->from($this->getTableName())
           ->where(
               $qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
           );
        
        return $this->findEntity($qb);
    }

    /**
     * @param string $userId
     * @return array
     */
    public function findAllForUser(string $userId): array {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
           ->from($this->getTableName())
           ->where(
               $qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR))
           )
           ->orderBy('service_date', 'DESC')
           ->addOrderBy('created_at', 'DESC');
        
        return $this->findEntities($qb);
    }

    /**
     * @param int $id
     * @param string $token
     * @return Entity|Dienstzeit
     * @throws DoesNotExistException
     */
    public function findByIdAndToken(int $id, string $token): Entity {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
           ->from($this->getTableName())
           ->where(
               $qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
           )
           ->andWhere(
               $qb->expr()->eq('token', $qb->createNamedParameter($token, IQueryBuilder::PARAM_STR))
           );
        
        return $this->findEntity($qb);
    }

    /**
     * @param string $status
     * @return array
     */
    public function findAllWithStatus(string $status): array {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
           ->from($this->getTableName())
           ->where(
               $qb->expr()->eq('status', $qb->createNamedParameter($status, IQueryBuilder::PARAM_STR))
           )
           ->orderBy('service_date', 'ASC');
        
        return $this->findEntities($qb);
    }
}
