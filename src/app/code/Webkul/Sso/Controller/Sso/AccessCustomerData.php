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
use \Firebase\JWT\JWT;

class AccessCustomerData extends Action
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
     * @param Context                                               $context
     * @param \Magento\Framework\Controller\Result\JsonFactory      $jsonResultFactory
     * @param \Magento\Customer\Model\Session                       $customerSession
     * @param \Webkul\Sso\Helper\Data                               $dataHelper
     * @param \Webkul\Sso\Model\SsoFactory                          $uvdeskSso
     * @param \Magento\Framework\Session\SessionManagerInterface    $session
     * @param \Webkul\Sso\Model\IntegrationsFactory                 $integrationFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Sso\Helper\Data $dataHelper,
        \Webkul\Sso\Model\SsoFactory $uvdeskSso,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Webkul\Sso\Model\IntegrationsFactory $integrationFactory
    ) {
    
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->_dataHelper = $dataHelper;
        $this->_uvdeskSso = $uvdeskSso;
        $this->_session = $session;
        $this->_integrationFactory = $integrationFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->_jsonResultFactory->create();
        $response = [];
        $post = $this->getRequest()->getParams();
        $customerSessionAuthToken = $this->_uvdeskSso->create();
        if (isset($post['client_id'])) {
            $IntegrationModel = $this->_integrationFactory->create();
            $IntegrationModel->load($post['client_id'], 'client_id');
            if (sizeof($IntegrationModel->getData())>0) {
                if (isset($post['authToken'])) {
                    $model = $this->_uvdeskSso->create();
                    $model->load($post['authToken'], 'authorization_code');
                    if ($post['authToken'] == $model->getAuthorizationCode()) {
                        // $key = $this->_dataHelper->getSecretket();
                        $key = $IntegrationModel->getClientSecretKey();
                        if ($key == "") {
                            $response['error'] = "Invalid key";
                        } else {
                            $payload = [
                                "exp" => strtotime("+2 minutes"),
                                "email" => $model->getCustomerEmail(),
                                "name" => $model->getCustomerName()
                            ];
                            try {
                                if (!class_exists('\Firebase\JWT\JWT')) {
                                    throw new \Exception('Firebase jwt library not included at magento end');
                                }
                                $jwt = JWT::encode($payload, $key);
                                $response['accessToken'] = $jwt;
                                $model->delete();
                            } catch (\Exception $e) {
                                $response['error'] = $e->getMessage();
                            }
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
        return $result->setData(['response' => $response]);
    }
}
