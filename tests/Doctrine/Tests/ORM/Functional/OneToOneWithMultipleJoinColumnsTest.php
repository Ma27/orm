<?php

namespace Doctrine\Tests\Functional;

use Doctrine\Tests\OrmFunctionalTestCase;

class OneToOneWithMultipleJoinColumnsTest extends OrmFunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();

        try {
            $this->_schemaTool->createSchema(
                [
                    $this->_em->getClassMetadata(OneToOneRevision::class),
                    $this->_em->getClassMetadata(OneToOneReference::class),
                ]
            );
        } catch (\Exception $e) {
        }
    }

    public function testQueryOneToOneRelationWithMultipleJoinColumns()
    {
        $revision = new OneToOneRevision();
        $revision->setId(1);
        $reference = new OneToOneReference();
        $reference->setId(1);
        $reference->setRevision($revision);

        $this->_em->persist($revision);
        $this->_em->persist($reference);
        $this->_em->flush();
        $this->_em->clear();

        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('rev')
            ->from(OneToOneRevision::class, 'rev')
            ->where($qb->expr()->eq('rev.id', 1));

        self::assertNotNull($result = $qb->getQuery()->getResult());
        self::assertSame($result[0]->getReference()->getId(), 1);
    }
}

/**
 * @Entity
 */
class OneToOneRevision
{
    /**
     * @var int
     *
     * @Column(type="integer", nullable=false)
     * @Id
     */
    private $id;

    /**
     * @var int
     *
     * @Column(type="integer", nullable=false)
     * @Id
     */
    private $revisionId = 1;

    /**
     * @var int
     *
     * @Column(type="integer", nullable=false)
     * @Id
     */
    private $docId = 1;

    /**
     * @var OneToOneReference
     *
     * @OneToOne(targetEntity="OneToOneReference", mappedBy="revision")
     */
    private $reference;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getRevisionId(): int
    {
        return $this->revisionId;
    }

    /**
     * @param int $revisionId
     */
    public function setRevisionId(int $revisionId): void
    {
        $this->revisionId = $revisionId;
    }

    /**
     * @return int
     */
    public function getDocId(): int
    {
        return $this->docId;
    }

    /**
     * @param int $docId
     */
    public function setDocId(int $docId): void
    {
        $this->docId = $docId;
    }

    /**
     * @return OneToOneReference
     */
    public function getReference(): OneToOneReference
    {
        return $this->reference;
    }

    /**
     * @param OneToOneReference $reference
     */
    public function setReference(OneToOneReference $reference): void
    {
        $this->reference = $reference;
    }
}

/**
 * @Entity
 */
class OneToOneReference
{
    /**
     * @var int
     *
     * @Column(type="integer", nullable=false)
     * @Id
     */
    private $id;

    /**
     * @var OneToOneRevision
     *
     * @OneToOne(targetEntity="OneToOneRevision", inversedBy="reference")
     * @JoinColumns({
     *      @JoinColumn(name="objectId", referencedColumnName="id"),
     *      @JoinColumn(name="docId", referencedColumnName="docId"),
     *      @JoinColumn(name="revId", referencedColumnName="revisionId")
     * })
     */
    private $revision;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return OneToOneRevision
     */
    public function getRevision(): OneToOneRevision
    {
        return $this->revision;
    }

    /**
     * @param OneToOneRevision $revision
     */
    public function setRevision(OneToOneRevision $revision): void
    {
        $this->revision = $revision;
    }
}
