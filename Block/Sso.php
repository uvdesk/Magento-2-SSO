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

namespace Webkul\Sso\Block;

class Sso extends \Magento\Framework\View\Element\Template
{

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Sso\Helper\Data                          $dataHelper
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param array                                            $data
     */
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Sso\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dataHelper = $dataHelper;
        $this->_customerSession = $customerSession;
    }

    public function getLoggedInUserDetail(){
        $customerDetal = $this->_dataHelper->getLoggedInUserDetail();
        return $customerDetal;

    }

    public function isAuthorized()
    {
        $autorizationToken = $this->getRequest()->getParams();
        if (isset($autorizationToken)) {
            return $this->_dataHelper->isAuthorized($autorizationToken);
        }
        return false;
    }
    public function isLoggedIn() 
    {
        $isLoggedIn = $this->_dataHelper->isLoggedIn();
        return $isLoggedIn ;
    }
}