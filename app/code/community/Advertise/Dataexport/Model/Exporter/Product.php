<?php
/**
 * Image.php
 * 
 * @package     Dataexport
 */
class Advertise_Dataexport_Model_Exporter_Product extends Varien_Object implements Advertise_Dataexport_Model_Exporter_Interface
{
    /**
     * Get the collection
     * 
     * @return 
     */
    public function getCollection()
    {
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addFieldToFilter('status',Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToSelect('*')
        ;
        return $collection;
    }
    
    /**
     * Write any items to the given writer
     * 
     * @param   XMLWriter 
     * @return  int
     */
     public function write(XMLWriter $writer)
     {
        // First, check whether or not Colors module is installed
        $advColorsInstalled = false;
        $modules = Mage::getConfig()->getNode('modules')->children();
        foreach ($modules as $mod) {
            if (stripos($mod->getName(), "Advertise_Importer", 0) === 0) {
                $advColorsInstalled = true;
            }
        }
                
        // Export product data
        $writer->startElement('feedItems');
        $ourversion = Mage::getConfig()->getModuleConfig("Advertise_Dataexport")->version;
        $date = date('Y-m-d');
        $writer->startAttribute('date');
          $writer->text($date);
        $writer->endAttribute();
        $writer->startAttribute('version');
          $writer->text($ourversion);
        $writer->endAttribute();
        $count = 0;
        foreach($this->getCollection() as $product) {
            /* @var $product Mage_Catalog_Model_Product */
            
            // Get the data we need for product feed
            //$data = $product->toArray();
            $data = array();
            $prodName = $product->getName();
            $pid = $product->getId();
            $produrl = $product->getProductUrl();
            $prodsku = $product->getSku();
            $prodcatstr = "";
            $prodcats = $product->getCategoryCollection();
            $categs = $prodcats->exportToArray();
            $categsToLinks = array();
            # Get categories names
            foreach($categs as $cat){
                $categsToLinks [] = Mage::getModel('catalog/category')->load($cat['entity_id'])->getName();
            }
            foreach($categsToLinks as $ind=>$cat){
                $prodcatstr .= $cat.'|';
            }
            if (strrpos($prodcatstr, '|') > 0) {
                $prodcatstr = substr($prodcatstr, 0, strrpos($prodcatstr, '|'));
            }
            $baseprice = $product->getPrice();
            $currencycode = Mage::app()->getStore()->getCurrentCurrencyCode();
            $currencysymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
            $prodprice = Mage::helper('core')->currency($baseprice, true, false);
            $description = $product->getDescription();
            $prodvis = $product->getVisibility();
            switch ($prodvis) {
                case Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE :
                    $prodvis = "NOT_VISIBLE";
                    break;
                case Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG :
                    $prodvis = "CATALOG";
                    break;
                case Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH :
                    $prodvis = "SEARCH";
                    break;
                case Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH :
                    $prodvis = "CATALOG,SEARCH";
                    break;
            }
            $prodtype = $product->getTypeID();
            if ($product->getResource()->getAttribute('color')) {
                $prodcolor = $product->getResource()->getAttribute('color')->getFrontend()->getValue($product);
                if ($prodcolor == "No") {
                    $prodcolor == "";
                }
            } else {
                $prodcolor == "";
            }
            
            if ($advColorsInstalled) {
                $prodadvswatchcolor = $product->getResource()->getAttribute('advertise_swatch_colors')->getFrontend()->getValue($product);
                if ($prodadvswatchcolor == "No") {
                    $prodadvswatchcolor == "";
                }
                $prodadvautocolor = $product->getResource()->getAttribute('advertise_auto_colors')->getFrontend()->getValue($product);
                if ($prodadvautocolor == "No") {
                    $prodadvautocolor == "";
                }
            } else {
                // Advertise_Colors not installed so these attributes do not exist in database
                $prodadvswatchcolor = "";
                $prodadvautocolor = "";
            }
            $stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
            $specialprice = $product->getSpecialPrice();
            $prodweight = $product->getWeight();
            if ($product->getResource()->getAttribute('manufacturer')) {
                $prodman = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
            } else {
                $prodman = "";
            }
            if ($product->getResource()->getAttribute('gender')) {
                $prodgender = $product->getResource()->getAttribute('gender')->getFrontend()->getValue($product);
            } else {
                $prodgender = "";
            }
            $metatitle = $product->getMetaTitle();
            $metadesc = $product->getMetaDescription();
            $metakw = $product->getMetaKeyword();
            
            if ($product->getResource()->getAttribute('shoe_size')) {
                $size1 = $product->getResource()->getAttribute('shoe_size')->getFrontend()->getValue($product);
            } else {
                $size1 = "";
            }
            if ($product->getResource()->getAttribute('shirt_size')) {
                $size2 = $product->getResource()->getAttribute('shirt_size')->getFrontend()->getValue($product);
            } else {
                $size2 = "";
            }
            
            // Set the data to be written
            $data['prodname'] = $prodName;
            $data['productid'] = $pid;
            $data['url'] = $produrl;
            $data['sku'] = $prodsku;
            $data['category'] = $prodcatstr;
            if ($prodcolor != "No") {
                $data['color'] = $prodcolor;
            }
            if ($prodadvswatchcolor != "No") {
                $data['advertiseSwatchColor'] = $prodadvswatchcolor;
            }
            if ($prodadvautocolor != "No") {
                $data['advertiseAutoColor'] = $prodadvautocolor;
            }
            $data['price'] = $baseprice;
            if($specialprice){
                $data['priceSale'] = $specialprice;
            }
            $data['currencycode'] = $currencycode;
            $data['currencysymbol'] = $currencysymbol;
            $data['formattedprice'] = $prodprice;
            $data['description'] = $description;
            if($size1 != "No") {
                $data['size'] = $size1;
            } else if ($size2 != "No") {
                $data['size'] = $size2;
            }
            $data['magentoVisibility'] = $prodvis;
            $data['type'] = $prodtype;
            $data['quantity'] = $stocklevel;
            $data['shippingWeight'] = $prodweight;
            if($prodman != "No") {
                $data['manufacturer'] = $prodman;
            }
            if($prodgender != "No") {
                $data['gender'] = $prodgender;
            }
            $data['metaTitle'] = $metatitle;
            $data['metaDescription'] = $metadesc;
            $data['metaKeywords'] = $metakw;
            
            $prod = Mage::helper('catalog/product')->getProduct($product->getId(), null, null);
            $galleryData = $prod->getData('media_gallery');
            $i=0; 
            foreach ($galleryData['images'] as $image) {
                $i++;
                if ($i > 10) break;
                $url = (string)(Mage::helper('catalog/image')->init($prod, NULL , $image['file']));
                $data['image_'.$i] = $url;
            }

            $writer->writeArray($data, 'feeditem');
            $count++;
        }
        $writer->endElement(); // Products
        return $count;
    }
}

