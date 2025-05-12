<?php

namespace Southbay\ReturnProduct\Block\Frontend;

use Magento\Customer\Model\Session as CustomerSession;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;
use Southbay\ReturnProduct\Block\Adminhtml\Form\Grid\ReturnTypeOptionsProvider;
use Southbay\ReturnProduct\Block\Adminhtml\Config\Form\StatusOptionsProvider;
use Magento\Framework\View\Element\Template;
use Southbay\ReturnProduct\Block\Adminhtml\Config\Form\CountryOptionsProvider;

class Dashboard extends MyReturns
{
    protected $returnTypeOptionsProvider;
    protected $statusOptionsProvider;
    protected $countryOptionsProvider;
    private $context;

    public function __construct(
        Template\Context $context,
        array $data ,
        CustomerSession $customerSession,
        SouthbayReturnProductRepository $repository,
        ReturnTypeOptionsProvider $returnTypeOptionsProvider,
        StatusOptionsProvider $statusOptionsProvider,
        CountryOptionsProvider $countryOptionsProvider
    ) {
        parent::__construct($context, $data, $customerSession, $repository);
        $this->returnTypeOptionsProvider = $returnTypeOptionsProvider;
        $this->statusOptionsProvider = $statusOptionsProvider;
        $this->countryOptionsProvider = $countryOptionsProvider;
    }

    public function getName()
    {
        return 'my_hostorical_returns';
    }

    public function getFilteredCollection( )
    {

        return $this->getAllCollection();
    }

    public function getReturnTypeOptions()
    {
        return $this->returnTypeOptionsProvider->toOptionArray();
    }

    public function getStatusOptions()
    {
        return $this->statusOptionsProvider->toOptionArray();
    }
    public function getCountryOptions()
    {
        $options = $this->countryOptionsProvider->toOptionArray();

        return array_values($options);
    }

}
