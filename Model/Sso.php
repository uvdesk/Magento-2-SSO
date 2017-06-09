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
namespace Webkul\Sso\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class Sso extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Webkul\Sso\Model\ResourceModel\Sso');
    }

    // public function loadByAuthorizationCode($field,$value)
    // {
    //     $id = $this->getResource()->loadByAuthorizationCode($field,$value);
    //     return $this->load($id);
    // }
}