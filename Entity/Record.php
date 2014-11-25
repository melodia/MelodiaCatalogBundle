<?php

/*
 * This file is part of the Melodia Catalog Bundle
 *
 * (c) Alexey Ryzhkov <alioch@yandex.ru>
 */

namespace Melodia\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="RecordRepository")
 * @ORM\Table(name="catalog_records")
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Record
{
    const REPOSITORY = 'MelodiaCatalogBundle:Record';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"getAllRecords", "getOneRecord", "getRecordId"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Catalog", inversedBy="records", cascade={"persist"})
     * @ORM\JoinColumn(name="catalog_id", referencedColumnName="id")
     *
     * @Groups({"getOneRecord"})
     */
    protected $catalog;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"getAllRecords", "getOneRecord"})
     */
    protected $data;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"getAllRecords", "getOneRecord"})
     */
    protected $keyword;

    /**
     * @ORM\Column(name="`order`", type="integer")
     *
     * @Groups({"getAllRecords", "getOneRecord"})
     */
    protected $order;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set data
     *
     * @param string $data
     * @return Record
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set keyword
     *
     * @param string $keyword
     * @return OrderedRecord
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set order
     *
     * @return Record
     */
    public function setOrder($order = null)
    {
        if ($order !== null) {
            $this->order = $order;
        } else if ($this->catalog !== null) {
            $this->order = count($this->catalog->getRecords());
        }
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set catalog
     *
     * @param \Melodia\CatalogBundle\Entity\Catalog $catalog
     * @return Record
     */
    public function setCatalog(\Melodia\CatalogBundle\Entity\Catalog $catalog = null)
    {
        $this->catalog = $catalog;
        if ($catalog) {
            $catalog->addRecord($this);
        }

        return $this;
    }

    /**
     * Get catalog
     *
     * @return \Melodia\CatalogBundle\Entity\Catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }
}
