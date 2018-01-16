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

namespace Webkul\Sso\Controller\Adminhtml\Sso;

class Delete extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $_resultPageFactory;
    
    /** @var \Webkul\Sso\Model\IntegrationsFactory */
    protected $_integrationFactory;

   /**
    * @param \Magento\Backend\App\Action\Context         $context
    * @param \Magento\Framework\View\Result\PageFactory  $resultPageFactory
    * @param \Webkul\Sso\Model\IntegrationsFactory       $integrationFactory
    * @param \Magento\Framework\Message\ManagerInterface $messageManager
    */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Sso\Model\IntegrationsFactory $integrationFactory
    ) {
    
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_integrationFactory = $integrationFactory;
    }

    public function execute()
    {
        $post = $this->getRequest()->getParams();
        if (isset($post['id'])) {
            try {
                $this->_integrationFactory->create()->load($post['id'])->delete();
                $this->messageManager->addSuccess(__("Deleted Successfully"));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }
        $this->_redirect("sso/sso/index");
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Sso::menu');
    }
}
