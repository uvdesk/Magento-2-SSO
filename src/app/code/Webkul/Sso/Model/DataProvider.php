<?php
namespace Webkul\Sso\Model;
 
use Webkul\Sso\Model\ResourceModel\Integrations\CollectionFactory;
 
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $_loadedData;
 
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $integrationsCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $integrationsCollectionFactory->create();
        $this->collection->addFieldToSelect('*');
    }
 
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $integrationItem) {
            $this->_loadedData[$integrationItem->getId()]['client_details'] = $integrationItem->getData();
            $this->_loadedData[$integrationItem->getId()]['client_credentials'] = $integrationItem->getData();
        }
        return $this->_loadedData;
    }
}
