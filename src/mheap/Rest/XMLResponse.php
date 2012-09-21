<?php

namespace mheap\Rest;

class XMLResponse extends \Symfony\Component\HttpFoundation\Response{
	
	public function __construct($data, $http_code=200, $template){
		// Build the response. If a twig template is provided, use it
		// @TODO Implement Twig XML generation

		// Otherwise use SimpleXML
		$xml = new \SimpleXMLElement('<response/>');
		$this->array_to_xml($data, $xml);
		$data = $xml->asXML();

		return parent::__construct($data, $http_code, array(
			"content-type" => "application/xml"
		));
	}
	private function array_to_xml(array $arr, \SimpleXMLElement $xml)
	{
	    foreach ($arr as $k => $v) {
	        if (is_array($v)){
	        	$this->array_to_xml($v, $xml->addChild($k));
	        }
	        else
	        {
	        	$newElement = $xml->addChild($k, $v);
	        	if (is_bool($v)){ $newElement->addAttribute("boolean", "true"); }
	        }
	    }
	    return $xml;
	}
}