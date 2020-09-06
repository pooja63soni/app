<?php 
namespace Catalog\CustomColumn\Ui\DataProvider\Product; 

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class EstimatedProfit extends \Magento\Ui\Component\Listing\Columns\Column 
{ 
	
	protected $_productloader;  
	protected $StockStateInterface;
    protected $localeCurrency;


    public function __construct(
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\CatalogInventory\Api\StockStateInterface $StockStateInterface,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []

    ) {
    	parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
    	$this->StockStateInterface = $StockStateInterface;
        $this->localeCurrency = $localeCurrency;
		$this->_productloader = $_productloader;
    }
    public function prepareDataSource(array $dataSource)
    {
    	if (isset($dataSource['data']['items'])) {

            $store = $this->storeManager->getStore(
                $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );
            $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $product = $this->_productloader->create()->load($item["entity_id"]); // Get Product
                $ProductStock = $this->StockStateInterface->getStockQty($item["entity_id"]); // Get Qty in stock
                $EstimatedProfit = 0;
                if($product->getCost()){
                    $EstimatedProfit = ($product->getPrice() - $product->getCost()) * $ProductStock; // Estimated Price Calculation
                }
                
                $item[$fieldName] = $currency->toCurrency(sprintf("%f", $EstimatedProfit));
            }
        }
        
        return $dataSource;
    }
}