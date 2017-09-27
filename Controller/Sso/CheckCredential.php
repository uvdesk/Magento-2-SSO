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
use Webkul\Sso\Helper\Firebase\JWT\JWT;

class CheckCredential extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    Protected $_customerLoadedModel;

    /**
     * @param Context                                             $context
     * @param PageFactory                                         $resultPageFactory
     * @param \Magento\Customer\Model\Session                     $customerSession
     * @param \Magento\Framework\Json\Helper\Data                 $jsonHelper   
     * @param \Webkul\Sso\Helper\Data                             $dataHelper
     * @param \Magento\Customer\Model\Authentication              $customerAuthentication
     * @param \Magento\Customer\Model\CustomerFactory             $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface          $storeManager
     * @param \Webkul\Sso\Model\SsoFactory                        $uvdeskSso
     * @param \Magento\Framework\Session\SessionManagerInterface  $session
     * @param \Webkul\Sso\Model\IntegrationsFactory               $integrationFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,     
        \Webkul\Sso\Helper\Data $dataHelper,
        \Magento\Customer\Model\Authentication $customerAuthentication,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Sso\Model\SsoFactory $uvdeskSso,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Webkul\Sso\Model\IntegrationsFactory $integrationFactory
    ) 
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_json = $jsonHelper;
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_customerAuthentication = $customerAuthentication;
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_uvdeskSso = $uvdeskSso;
        $this->_session = $session;
        $this->_integrationFactory = $integrationFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $response = ['success' => false, 'message' => "No client id provided"];
        $post = $this->getRequest()->getParams();
        if (isset($post['client_id']) ) {
            $IntegrationModel = $this->_integrationFactory->create();
            $IntegrationModel->load($post['client_id'],'client_id');
            if (sizeof($IntegrationModel->getData())>0) {
                if (isset($post['client_secret_key']) ) {
                        if ($post['client_secret_key'] == $IntegrationModel->getClientSecretKey()) {
                            $response['success'] = true;
                            $response['message'] = "Client Verified";
                        } else {
                            $response['message'] = "Invalid client secret key provided";
                        }
                } else {
                    $response['message'] = "No client secret key provided";
                }
            } else {
                $response['message'] = "Invalid client id provided";
            }
        }
        
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($this->_json->jsonEncode(['response' => $response]));
    }
}