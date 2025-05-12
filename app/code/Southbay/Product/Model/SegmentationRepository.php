<?php

namespace Southbay\Product\Model;

use Southbay\Product\Api\SegmentationRepositoryInterface;
use Southbay\Product\Api\Data\SegmentationInterface;
use Southbay\Product\Model\ResourceModel\Segmentation as SegmentationResource;
use Southbay\Product\Model\ResourceModel\Segmentation\CollectionFactory as SegmentationCollectionFactory;


use Magento\Framework\Exception\NoSuchEntityException;

class SegmentationRepository implements SegmentationRepositoryInterface
{
    protected $segmentationResource;
    protected $segmentationFactory;
    protected $segmentationCollectionFactory;

    public function __construct(
        SegmentationResource          $segmentationResource,
        SegmentationFactory           $segmentationFactory,
        SegmentationCollectionFactory $segmentationCollectionFactory
    )
    {
        $this->segmentationResource = $segmentationResource;
        $this->segmentationFactory = $segmentationFactory;
        $this->segmentationCollectionFactory = $segmentationCollectionFactory;
    }

    public function save(SegmentationInterface $segmentation)
    {
        try {
            $this->segmentationResource->save($segmentation);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save the segmentation: %1', $e->getMessage())
            );
        }
        return $segmentation;
    }

    public function getById($id)
    {
        $segmentation = $this->segmentationFactory->create();
        $this->segmentationResource->load($segmentation, $id);
        if (!$segmentation->getId()) {
            throw new NoSuchEntityException(__('Segmentation with ID "%1" does not exist.', $id));
        }
        return $segmentation;
    }

    public function delete(SegmentationInterface $segmentation)
    {
        try {
            $this->segmentationResource->delete($segmentation);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __('Could not delete the segmentation: %1', $e->getMessage())
            );
        }
        return true;
    }


}
