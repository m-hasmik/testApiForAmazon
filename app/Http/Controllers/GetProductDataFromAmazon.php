<?php

namespace App\Http\Controllers;

class GetProductDataFromAmazon extends Controller
{
    private $pageData;
    private $product;
    private $pageURL = 'https://www.amazon.co.uk/Winning-Moves-29612-Trivial-Pursuit/dp/B075716WLM/';

    /**
     * get content from url by curl request
     */
    public function getData(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->pageURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $this->pageData = curl_exec($ch);
        curl_close($ch);

        $this->product['asin'] = $this->getASIN();
        $this->product['title'] = $this->getTitle();
        $this->product['price'] = $this->getPrice();
        $this->product['description'] = $this->getDescription();
        $this->product['specifications'] = $this->getSpecifications();
        $this->product['images'] = $this->getImages();
        $this->createJsonFromData($this->product);
    }

    /**
     * this method return product asin
     * @return mixed
     */
    private function getASIN()
    {
        preg_match_all('/dp\/(.*?)\//s', $this->pageURL, $asin);
        $asin = $asin[1][0];
        return $asin;
    }

    /**
     * this method return product title
     * @return string
     */
    private function getTitle()
    {
        preg_match_all('/span id="productTitle" class="a-size-large">(.*?)<\/span>/s', $this->pageData, $title);
        $title = trim($title[1][0]);
        return $title;
    }

    /**
     * this method return product price
     * @return mixed
     */
    private function getPrice()
    {
        preg_match_all('/id="olp-sl-new-used(.*?)span class="a-color-price">(.*?)class=\'p13n-sc-price\'>(.*?)<\/span>/s', $this->pageData, $prices);

        if(!empty($prices[3][0])) {
            $price = $prices[3][0];
        } else {
            preg_match_all('/id="priceblock_ourprice(.*?)a-color-price">(.*?)<\/span>/s', $this->pageData, $price);
            $price = $price[2][0];
        }

        return $price;
    }

    /**
     * this method return product description
     * @return string
     */
    private function getDescription()
    {
        preg_match_all('/h3>Product Description(.*?)p>(.*?)<\/p>/s', $this->pageData, $description);
        $description = trim($description[2][0]);
        return $description;
    }

    /**
     * this method return product specifiaction
     * @return array
     */
    private function getSpecifications()
    {
        preg_match_all('/id="prodDetails(.*?)(tbody>)(.*?)(<\/tbody)/s', $this->pageData, $specification);
        preg_match_all('/td class="value">(.*?)<\/td/s', $specification[3][0], $specifications);
        $specifications_values = $specifications[1];
        preg_match_all('/td class="label">(.*?)<\/td/s', $specification[3][0], $specifications_keys);
        $specifications_keys = $specifications_keys[1];
        $specifications = array_combine($specifications_keys,$specifications_values);
        return $specifications;
    }

    /**
     * this method return product images
     * @return mixed
     */
    private function getImages()
    {
        preg_match_all('/id="altImages(.*?)(\/ul>)/s', $this->pageData, $image);
        preg_match_all('/img alt="" src="(.*?)\"\>/s',$image[1][0], $images);
        $images = $images[1];
        array_pop($images);
        return $images;
    }

    /** this method create json file from product data...*/
    private function createJsonFromData($param){
        file_put_contents('product.json',json_encode($param,JSON_PRETTY_PRINT));
    }


}
