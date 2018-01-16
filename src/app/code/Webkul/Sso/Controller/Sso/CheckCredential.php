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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class CheckCredential extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonResultFactory;
    
    /**
     * @var \Webkul\Sso\Helper\Data
     */
    protected $_dataHelper;
    
    /**
     * @var \Magento\Customer\Model\Authentication
     */
    protected $_customerAuthentication;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Webkul\Sso\Model\SsoFactory
     */
    protected $_uvdeskSso;
    
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;
    
    /**
     * @var \Webkul\Sso\Model\IntegrationsFactory
     */
    protected $_integrationFactory;

    /**
     * __construct function
     *
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory      $jsonResultFactory
     * @param \Webkul\Sso\Helper\Data                               $dataHelper
     * @param \Magento\Customer\Model\Authentication                $customerAuthentication
     * @param \Magento\Customer\Model\CustomerFactory               $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \Webkul\Sso\Model\SsoFactory                          $uvdeskSso
     * @param \Magento\Framework\Session\SessionManagerInterface    $session
     * @param \Webkul\Sso\Model\IntegrationsFactory                 $integrationFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Webkul\Sso\Helper\Data $dataHelper,
        \Magento\Customer\Model\Authentication $customerAuthentication,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Sso\Model\SsoFactory $uvdeskSso,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Webkul\Sso\Model\IntegrationsFactory $integrationFactory
    ) {
    
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->_dataHelper = $dataHelper;
        $this->_customerAuthentication = $customerAuthentication;
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_uvdeskSso = $uvdeskSso;
        $this->_session = $session;
        $this->_integrationFactory = $integrationFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->_jsonResultFactory->create();
        $response = ['success' => false, 'message' => "No client id provided"];
        $post = $this->getRequest()->getParams();
        if (isset($post['client_id'])) {
            $IntegrationModel = $this->_integrationFactory->create();
            $IntegrationModel->load($post['client_id'], 'client_id');
            if (sizeof($IntegrationModel->getData())>0) {
                if (isset($post['client_secret_key'])) {
                    if ($post['client_secret_key'] == $IntegrationModel->getClientSecretKey()) {
                        $response['success'] = "true";
                        $response['message'] = "Client Verified";
                    } else {
                        $response['message'] = "Invalid client secret key provided";
                    }
                } else {
                    $response['message'] = "No client secret key provided";
                }
            } else {
                $response['error'] = "Invalid client id provided";
            }
        } else {
            $response['error'] = "No client id provided";
        }
        return $result->setData(['response' => $response]);
    }
}
