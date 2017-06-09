<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Sso
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Sso\Controller\Sso;
use Magento\Framework\Controller\ResultFactory; 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul Sso Landing page Index Controller.
 */
class Test extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\Authentication $customerAuthentication,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) 
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_json = $jsonHelper;
        $this->_customerSession = $customerSession;
        $this->_customerAuthentication = $customerAuthentication;
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Sso Landing page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();   
        return $resultPage;
    }
}
