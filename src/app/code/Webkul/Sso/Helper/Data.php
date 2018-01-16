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

namespace Webkul\Sso\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

/**
 * Sso data helper.
 */

class Data extends AbstractHelper
{


    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Webkul\Sso\Model\ResourceModel\Integrations\Collection
     */
    protected $_integrationCollection;

    /**
     * @param \Magento\Framework\App\Helper\Context                   $context
     * @param \Magento\Config\Model\ResourceModel\Config              $resourceConfig
     * @param \Magento\Customer\Model\Session                         $customerSession
     * @param \Webkul\Sso\Model\ResourceModel\Integrations\Collection $integrationCollection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Sso\Model\ResourceModel\Integrations\Collection $integrationCollection
    ) {
    
        $this->_resourceConfig = $resourceConfig;
        $this->_customerSession = $customerSession;
        $this->_integrationCollection = $integrationCollection;
        parent::__construct($context);
    }
    
    /**
     * authoriationUVdeskToken function check weather the client(UvDesk) is registerd or not.
     *
     * @param string $autorizationToken
     * @return int
     */
    public function authoriationUVdeskToken($autorizationToken = "")
    {
        $collection = $this->_integrationCollection->addFieldToFilter('client_id', ['eq'=>$autorizationToken]);
        return  $collection->getSize();
    }

    /**
     * isAuthorized function check weather the client(UvDesk) is registerd or not.
     *
     * @param string $autorizationToken
     * @return boolean
     */
    public function isAuthorized($autorizationToken = "")
    {
        if (!isset($autorizationToken) || !($this->authoriationUVdeskToken($autorizationToken))) {
            return false;
        }
        return true;
    }

    /**
     * Return the status of customer log in.
     *
     * @return Boolean.
     */
    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }
    public function getLoggedInUserDetail()
    {
        $customerDetal = [];
        $customerDetal['entity_id'] = $this->_customerSession->getCustomer()->getEntityId();
        $customerDetal['email'] = $this->_customerSession->getCustomer()->getEmail();
        $customerDetal['name'] = $this->_customerSession->getCustomer()->getName();
        return $customerDetal;
    }
    
    /**
     * Return the secret key for encoding of customer data for SSO.
     *
     * @return String.
     */
    public function getSecretket()
    {
        $secretkey =  $this->scopeConfig
                                 ->getValue(
                                     'sso_conn/sso_config/sso_secret_key',
                                     \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                 );
        return $secretkey;
    }

    /**
     * Return the redirecting url for SSO.
     *
     * @return String.
     */
    public function getRedirectUrl()
    {
        $url =  $this->scopeConfig
                                 ->getValue(
                                     'sso_conn/sso_config/sso_redirect_url',
                                     \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                 );
        return $url;
    }
}
