<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * WC_PostFinanceCheckout_Service_Abstract Class.
 */
abstract class WC_PostFinanceCheckout_Service_Abstract {
	private static $instances = array();

	/**
	 * 
	 * @return static
	 */
	public static function instance(){
		$class = get_called_class();
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class();
		}
		return self::$instances[$class];
	}

	/**
	 * Converts a DatabaseTranslatedString into a serializable array.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\DatabaseTranslatedString $translatedString
	 * @return string[]
	 */
	protected function get_translations_array(\PostFinanceCheckout\Sdk\Model\DatabaseTranslatedString $translatedString){
		$translations = array();
		foreach ($translatedString->getItems() as $item) {
			$translation = $item->getTranslation();
			if (!empty($translation)) {
				$translations[$item->getLanguage()] = $item->getTranslation();
			}
		}
		
		return $translations;
	}
	
	/**
	 * Returns the resource part of the resolved url
	 * 
	 * @param String $resolved_url
	 * @return string
	 */
	protected function get_resource_path($resolved_url) {
		if(empty($resolved_url)){
			return $resolved_url;
		}
		$index = strpos($resolved_url, 'resource/');
		if($index === false){
		    return $resolved_url;
		}
		return substr($resolved_url, $index + strlen('resource/'));
	}
	
	protected function get_resource_base($resolved_url){
	    if(empty($resolved_url)){
	        return $resolved_url;
	    }
	    $parts = parse_url($resolved_url);
	    return $parts['scheme']."://".$parts['host']."/";
	}

	/**
	 * Returns the fraction digits for the given currency.
	 *
	 * @param string $currency_code
	 * @return number
	 */
	protected function get_currency_fraction_digits($currency_code){
	    return WC_PostFinanceCheckout_Helper::instance()->get_currency_fraction_digits($currency_code);
	}

	/**
	 * Rounds the given amount to the currency's format.
	 *
	 * @param float $amount
	 * @param string $currencyCode
	 * @return number
	 */
	protected function round_amount($amount, $currency_code){
		return round($amount, $this->get_currency_fraction_digits($currency_code));
	}

	/**
	 * Creates and returns a new entity filter.
	 *
	 * @param string $field_name
	 * @param mixed $value
	 * @param string $operator
	 * @return \PostFinanceCheckout\Sdk\Model\EntityQueryFilter
	 */
	protected function create_entity_filter($field_name, $value, $operator = \PostFinanceCheckout\Sdk\Model\CriteriaOperator::EQUALS){
	    $filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
	    $filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::LEAF);
		$filter->setOperator($operator);
		$filter->setFieldName($field_name);
		$filter->setValue($value);
		return $filter;
	}

	/**
	 * Creates and returns a new entity order by.
	 *
	 * @param string $field_name
	 * @param string $sort_order
	 * @return \PostFinanceCheckout\Sdk\Model\EntityQueryOrderBy
	 */
	protected function create_entity_order_by($field_name, $sort_order = \PostFinanceCheckout\Sdk\Model\EntityQueryOrderByType::DESC){
	    $order_by = new \PostFinanceCheckout\Sdk\Model\EntityQueryOrderBy();
		$order_by->setFieldName($field_name);
		$order_by->setSorting($sort_order);
		return $order_by;
	}

	/**
	 * Changes the given string to have no more characters as specified.
	 *
	 * @param string $string
	 * @param int $max_length
	 * @return string
	 */
	protected function fix_length($string, $max_length){
		return mb_substr($string, 0, $max_length, 'UTF-8');
	}

	/**
	 * Removes all non printable ASCII chars
	 * 
	 * @param String $string
	 * @return $string
	 */
	protected function remove_non_ascii($string){
		return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $string);
	}
}