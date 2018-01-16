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

class Index extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Webkul\Sso\Helper\Data
     */
    protected $_dataHelper;
   
    /**
     * @var  \Magento\Customer\Model\Authentication
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
     * @var \Webkul\Sso\Model\IntegrationsFactory
     */
    protected $_integrations;
    
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_phpEnv;
    
    /**
     * __construct function
     *
     * @param Context                                               $context
     * @param PageFactory                                           $resultPageFactory
     * @param \Magento\Customer\Model\Session                       $customerSession
     * @param \Webkul\Sso\Helper\Data                               $dataHelper
     * @param \Magento\Customer\Model\Authentication                $customerAuthentication
     * @param \Magento\Customer\Model\CustomerFactory               $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \Webkul\Sso\Model\SsoFactory                          $uvdeskSso
     * @param \Webkul\Sso\Model\IntegrationsFactory                 $integrations
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress  $phpEnv
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Sso\Helper\Data $dataHelper,
        \Magento\Customer\Model\Authentication $customerAuthentication,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Sso\Model\SsoFactory $uvdeskSso,
        \Webkul\Sso\Model\IntegrationsFactory $integrations,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $phpEnv
    ) {
    
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_customerAuthentication = $customerAuthentication;
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_uvdeskSso = $uvdeskSso;
        $this->_integrations = $integrations;
        $this->_phpEnv = $phpEnv;
        parent::__construct($context);
    }

    public function execute()
    {
        $redirectUrl = "";
        $post = $this->getRequest()->getParams();
        if (!isset($post['redirect_uri']) || $post['redirect_uri'] == "") {
            $this->messageManager->addError(__('No redirect url provided'));
            return $this->resultRedirectFactory->create()->setPath($this->_storeManager->getStore()->getBaseUrl());
        }
        if (!isset($post['client_id']) || $post['client_id'] == "") {
            $this->messageManager->addError(__('No client id provided'));
            return $this->resultRedirectFactory->create()->setPath($this->_storeManager->getStore()->getBaseUrl());
        }
        if (isset($post['redirect_uri'])) {
            $redirectUrl = $post['redirect_uri'];
        }
        $resultPage = $this->_resultPageFactory->create();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $autorizationToken = $this->getRequest()->getParam('client_id');
        $isAuthorized = $this->_dataHelper->isAuthorized($autorizationToken);
        $isLoggedIn = $this->_dataHelper->isLoggedIn();
        if (isset($post['client_id']) && !isset($post['login']) && !isset($post['authorization'])) {
            if (!$isAuthorized) {
                $this->messageManager->addError(__('Unauthorized client'));
                return $this->resultRedirectFactory->create()->setPath($this->_storeManager->getStore()->getBaseUrl());
            }
        }
        if (isset($post['form_key']) && isset($post['login'])) {
            $customerModel = $this->_customerFactory->create();
            $customerModel->setWebsiteId($this->_storeManager->getStore()->getWebsiteId());
            $customerData = $customerModel->loadByEmail($post['login']['username']);
            $this->_customerLoadedModel = $customerData;
            $customerId = $customerData->getEntityId();
            try {
                $isAutorizedUser = $this->_customerAuthentication->authenticate($customerId, $post['login']['password']);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Invalid Username or password'));
                return $this->resultRedirectFactory->create()->setPath('sso/sso/index', ['_current' => true]);
            }
            if ($isAutorizedUser) {
                $this->_customerSession->loginById($customerId);
            } else {
                $this->messageManager->addSuccess(__('Invalid User'));
            }
        }
        if (isset($post['authorization'])) {
                $token = bin2hex(openssl_random_pseudo_bytes(16));
                $customerData = $this->_dataHelper->getLoggedInUserDetail();
                $model = $this->_uvdeskSso->create();
                $model->setAuthorizationCode($token);
                $model->setCustomerId($customerData['entity_id']);
                $model->setCustomerEmail($customerData['email']);
                $model->setCustomerName($customerData['name']);
                $model->setIpAddress($this->_phpEnv->getRemoteAddress());
                $model->save();
                $resultRedirect->setUrl($redirectUrl."?token=".$token);
                return $resultRedirect;
        }
        if (isset($post['cancel']) && $post['cancel']=='true') {
                $baseRediredtUrl = $this->_storeManager->getStore()->getBaseUrl();
            if (isset($post['client_id']) && $post['client_id']!="") {
                $integrationsModel = $this->_integrations->create()->load($post['client_id'], 'client_id');
                $redirectUrl = $integrationsModel->getUrl();
                if (isset($redirectUrl) && $redirectUrl!="") {
                    $resultRedirect->setUrl($redirectUrl);
                    return $resultRedirect;
                } else {
                    $resultRedirect->setUrl($baseRediredtUrl);
                    return $resultRedirect;
                }
            } else {
                $resultRedirect->setUrl($baseRediredtUrl);
                return $resultRedirect;
            }
        }
        return $resultPage;
    }
}
