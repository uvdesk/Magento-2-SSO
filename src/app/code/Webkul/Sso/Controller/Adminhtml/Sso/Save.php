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

class Save extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $_resultPageFactory;
    
    /** @var \Webkul\Sso\Model\IntegrationsFactory */
    protected $_integrationsFactory;

   /**
    * @param \Magento\Backend\App\Action\Context        $context
    * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
    * @param \Webkul\Sso\Model\IntegrationsFactory      $integrationsFactory
    */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Sso\Model\IntegrationsFactory $integrationsFactory
    ) {
    
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_integrationsFactory = $integrationsFactory;
    }

    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('New Integrations'));
        $model = $this->_integrationsFactory->create();
        if (isset($post['client_details']['entity_id'])) {
            $model->load($post['client_details']['entity_id']);
        } else {
            $model->setClientId(md5(uniqid(rand(), true)));
            $model->setClientSecretKey(md5(uniqid(rand(), true)));
        }
        $model->setName($post['client_details']['name']);
        $model->setUrl($post['client_details']['url']);
        // $model->setEmail($post['client_details']['email']);
        $model->setEmail("");
        $model->save();
        $this->_redirect("sso/sso/index");
        return $resultPage;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Sso::menu');
    }
}
