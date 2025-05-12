<?php
namespace Southbay\Product\Api\Data;

interface SegmentationInterface
{
public function getId();
public function getCode();
public function setCode($code);
public function getLabel();
public function setLabel($label);
}
