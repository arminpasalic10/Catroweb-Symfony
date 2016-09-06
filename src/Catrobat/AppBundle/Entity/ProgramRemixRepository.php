<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\DBAL\Types\Type;


/**
 * ProgramRemixRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProgramRemixRepository extends EntityRepository
{
    /**
     * @param int[] $descendant_program_ids
     * @return ProgramRemixRelation[]
     */
    public function getAncestorRelations(array $descendant_program_ids)
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->select('r')
            ->where('r.descendant_id IN (:descendant_ids)')
            ->setParameter('descendant_ids', $descendant_program_ids)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $descendant_program_ids
     * @return int[]
     */
    public function getAncestorIds(array $descendant_program_ids)
    {
        $parents_catrobat_ancestor_relations = $this->getAncestorRelations($descendant_program_ids);
        return array_unique(array_map(function($r) { return $r->getAncestorId(); }, $parents_catrobat_ancestor_relations));
    }

    /**
     * @param int[] $descendant_program_ids
     * @return ProgramRemixRelation[]
     */
    public function getParentAncestorRelations(array $descendant_program_ids)
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->select('r')
            ->where('r.descendant_id IN (:descendant_ids)')
            ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
            ->setParameter('descendant_ids', $descendant_program_ids)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $ancestor_program_ids_to_exclude
     * @param int[] $descendant_program_ids
     * @return ProgramRemixRelation[]
     */
    public function getDirectAndIndirectDescendantRelations(array $ancestor_program_ids_to_exclude, array $descendant_program_ids)
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->select('r')
            ->where('r.ancestor_id NOT IN (:ancestor_program_ids_to_exclude)')
            ->andWhere('r.descendant_id IN (:descendant_program_ids)')
            ->setParameter('ancestor_program_ids_to_exclude', $ancestor_program_ids_to_exclude)
            ->setParameter('descendant_program_ids', $descendant_program_ids)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $ancestor_program_ids_to_exclude
     * @param int[] $descendant_program_ids
     * @return int[]
     */
    public function getDirectAndIndirectDescendantIds(array $ancestor_program_ids_to_exclude, array $descendant_program_ids)
    {
        $direct_and_indirect_descendant_relations = $this
            ->getDirectAndIndirectDescendantRelations($ancestor_program_ids_to_exclude, $descendant_program_ids);

        return array_unique(array_map(function($r) { return $r->getAncestorId(); }, $direct_and_indirect_descendant_relations));
    }

    /**
     * @param int[] $program_ids
     * @return int[]
     */
    public function getRootProgramIds(array $program_ids)
    {
        $qb = $this->createQueryBuilder('r');

        $result_data = $qb
            ->select('r.ancestor_id')
            ->innerJoin('AppBundle:Program', 'p', Join::WITH, $qb->expr()->eq('r.ancestor_id', 'p.id'))
            ->where('r.descendant_id IN (:program_ids)')
            ->andWhere($qb->expr()->eq('p.remix_root', $qb->expr()->literal(true)))
            ->setParameter('program_ids', $program_ids)
            ->distinct()
            ->getQuery()
            ->getResult();

        return array_unique(array_map(function ($row) { return $row['ancestor_id']; }, $result_data));
    }

    /**
     * @param int[] $ancestor_program_ids
     * @return ProgramRemixRelation[]
     */
    public function getDescendantRelations(array $ancestor_program_ids)
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->select('r')
            ->where('r.ancestor_id IN (:ancestor_program_ids)')
            ->setParameter('ancestor_program_ids', $ancestor_program_ids)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $ancestor_program_ids
     * @return int[]
     */
    public function getDescendantIds(array $ancestor_program_ids)
    {
        $catrobat_root_descendant_relations = $this->getDescendantRelations($ancestor_program_ids);
        return array_unique(array_map(function ($r) { return $r->getDescendantId(); }, $catrobat_root_descendant_relations));
    }

    /**
     * @param array $edge_start_program_ids
     * @param array $edge_end_program_ids
     * @return ProgramRemixRelation[]
     */
    public function getDirectEdgeRelationsBetweenProgramIds(array $edge_start_program_ids, array $edge_end_program_ids)
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->select('r')
            ->where('r.ancestor_id IN (:edge_start_program_ids)')
            ->andWhere('r.descendant_id IN (:edge_end_program_ids)')
            ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
            ->setParameter('edge_start_program_ids', $edge_start_program_ids)
            ->setParameter('edge_end_program_ids', $edge_end_program_ids)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $ancestor_program_ids
     * @param array $descendant_program_ids
     */
    public function removeRelationsBetweenProgramIds(array $ancestor_program_ids, array $descendant_program_ids)
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->delete()
            ->where('r.ancestor_id IN (:ancestor_program_ids)')
            ->andWhere('r.descendant_id IN (:descendant_program_ids)')
            ->setParameter('ancestor_program_ids', $ancestor_program_ids)
            ->setParameter('descendant_program_ids', $descendant_program_ids)
            ->getQuery()
            ->execute();
    }

    public function removeAllRelations()
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->delete()
            ->getQuery()
            ->execute();
    }

    /**
     * @param User $user
     * @return ProgramRemixRelation[]
     */
    public function getUnseenDirectDescendantRelationsOfUser(User $user)
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->select('r')
            ->innerJoin('r.ancestor', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.ancestor_id = p.id')
            ->innerJoin('r.descendant', 'p2', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.descendant_id = p2.id')
            ->where($qb->expr()->eq('p.user', ':user'))
            ->andWhere($qb->expr()->neq('p2.user', 'p.user'))
            ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
            ->andWhere($qb->expr()->isNull('r.seen_at'))
            ->orderBy('r.created_at', 'DESC')
            ->setParameter('user', $user)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \DateTime $seen_at
     */
    public function markAllUnseenRelationsAsSeen(\DateTime $seen_at)
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->update()
            ->set('r.seen_at', ':seen_at')
            ->setParameter(':seen_at', $seen_at)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $program_id
     * @return int
     */
    public function remixCount($program_id)
    {
        $qb = $this->createQueryBuilder('r');

        $result = $qb
            ->select('r')
            ->where($qb->expr()->eq('r.ancestor_id', ':program_id'))
            ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
            ->setParameter('program_id', $program_id)
            ->distinct()
            ->getQuery()
            ->getResult();

        return count($result);
    }
}
