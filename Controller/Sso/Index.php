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
use Magento\Framework\App\Response\RedirectInterface;
use Webkul\Sso\Helper\Firebase\JWT\JWT;

/**
 * Webkul Sso Landing page Index Controller.
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    Protected $_customerLoadedModel;

    /**
     * @param Context                                              $context
     * @param PageFactory                                          $resultPageFactory
     * @param PageFactory                                          $resultPageFactory
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param \Magento\Framework\Json\Helper\Data                  $jsonHelper
     * @param \Magento\Customer\Model\Authentication               $customerAuthentication
     * @param \Magento\Customer\Model\CustomerFactory              $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Framework\Session\SessionManagerInterface   $session
     * @param \Webkul\Sso\Model\SsoFactory                         $uvdeskSso
     * @param \Webkul\Sso\Model\IntegrationsFactory                $integrations
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $phpEnv
     * @param RedirectInterface $redirect
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
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Webkul\Sso\Model\SsoFactory $uvdeskSso,
        \Webkul\Sso\Model\IntegrationsFactory $integrations,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $phpEnv,
        RedirectInterface $redirect
    ) 
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_json = $jsonHelper;
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_customerAuthentication = $customerAuthentication;
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_redirect = $redirect;
        $this->_session = $session;
        $this->_uvdeskSso = $uvdeskSso;
        $this->_integrations = $integrations;
        $this->_phpEnv = $phpEnv;
        parent::__construct($context);
    }

    public function execute()
    {
        $redirectUrl = $this->_dataHelper->getRedirectUrl();   
        $post = $this->getRequest()->getParams();
        if (isset($post['redirect_uri'])) {
            $redirectUrl = $post['redirect_uri'];
        }
        $resultPage = $this->_resultPageFactory->create();   
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $autorizationToken = $this->getRequest()->getParam('client_id');
        $isAuthorized = $this->_dataHelper->isAuthorized($autorizationToken);
        $isLoggedIn = $this->_dataHelper->isLoggedIn();
        if (isset($post['client_id'])&& !isset($post['login']) && !isset($post['authorization'])) {
            if (!$isAuthorized ){
                $this->messageManager->addError(__('Unauthorized client'));
                // $resultRedirect = $this->resultFactory->create($this->_redirect->getRedirectUrl());
                // $resultRedirect->setPath('sso/sso/index',['_current' => true]);
                // return $resultRedirect;
            }
            if($isAuthorized){
                $this->messageManager->addSuccess(__('Authorized client'));
            }
        }
        // if ($isLoggedIn && $isAuthorized) {
        //     $this->messageManager->addSuccess(__('Authorized token'));
        //     $this->messageManager->addSuccess(__('User details has been sent'));
        // }
        if (isset($post['form_key']) && isset($post['login'])) {
            $customerModel = $this->_customerFactory->create();
            $customerModel->setWebsiteId($this->_storeManager->getStore()->getWebsiteId());
            $customerData = $customerModel->loadByEmail($post['login']['username']);
            $this->_customerLoadedModel = $customerData;
            $customerId = $customerData->getEntityId();
            try {
            $isAutorizedUser = $this->_customerAuthentication->authenticate($customerId,$post['login']['password']);
            }catch(\Exception $e){
                $this->messageManager->addError(__('Invalid Username or password'));
                return $this->resultRedirectFactory->create()->setPath('sso/sso/index',['_current' => true]);
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
                    $integrationsModel = $this->_integrations->create()->load($post['client_id'],'client_id');
                    $redirectUrl = $integrationsModel->getUrl();
                    if (isset($redirectUrl) && $redirectUrl!="") {
                        $resultRedirect->setUrl($redirectUrl);
                        return $resultRedirect;
                    }else {
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
