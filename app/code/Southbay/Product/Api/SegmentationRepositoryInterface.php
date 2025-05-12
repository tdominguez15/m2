<?php
namespace Southbay\Product\Api;

use Southbay\Product\Api\Data\SegmentationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface SegmentationRepositoryInterface
{
public function save(SegmentationInterface $segmentation);
public function getById($id);
public function delete(SegmentationInterface $segmentation);

}
