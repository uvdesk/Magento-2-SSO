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

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Add extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory */    
    protected $_resultPageFactory;

   /**
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     */      
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) 
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_registry = $registry;
    }

    public function execute()
    {
        $post = $this->getRequest()->getParams();
        if (isset($post['entity_id'])) {
            $this->_registry->register('id',$post['entity_id']);
        }
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('New Integrations'));
        if (isset($post['entity_id'])) {
            $resultPage->getConfig()->getTitle()->prepend(__('Edit "#Id-%1" Integrations',$post['entity_id']));
        }
        return $resultPage;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Sso::sso_add');
    }
}