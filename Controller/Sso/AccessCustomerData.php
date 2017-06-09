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

class AccessCustomerData extends Action
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
     * @param RedirectInterface                                   $redirect
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
        RedirectInterface $redirect,
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
        $this->_redirect = $redirect;
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
        $response = [];
        $post = $this->getRequest()->getParams();
        $customerSessionAuthToken = $this->_uvdeskSso->create();
        if (isset($post['client_id']) ) {
            $IntegrationModel = $this->_integrationFactory->create();
            $IntegrationModel->load($post['client_id'],'client_id');
            if (sizeof($IntegrationModel->getData())>0) {
                if (isset($post['authToken']) ) {
                    $model = $this->_uvdeskSso->create();
                    $model->load($post['authToken'],'authorization_code');
                    if ($post['authToken'] == $model->getAuthorizationCode()) {
                        // $key = $this->_dataHelper->getSecretket();
                        $key = $IntegrationModel->getClientSecretKey();
                        if ($key == "") {
                            $response['error'] = "Invalid key";
                        } else {
                            $payload = array(
                                "exp" => strtotime("+2 minutes"),
                                "email" => $model->getCustomerEmail(),
                                "name" => $model->getCustomerName()
                            );
                            $jwt = JWT::encode($payload, $key);
                            $response['accessToken'] = $jwt;
                            $model->delete();
                        }
                    } else {
                        $response['error'] = "Invalid authorization token provided";
                    }
                } else {
                    $response['error'] = "No authorization token provided";
                }
            } else {
                $response['error'] = "Invalid client id token provided";
            }
        } else {
            $response['error'] = "No client id provided";
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($this->_json->jsonEncode(['response' => $response]));
    }
}