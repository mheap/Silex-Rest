<?php

namespace mheap\Rest;

class JSONResponse extends \Symfony\Component\HttpFoundation\Response{
	
	public function __construct($data, $http_code=200){
		$data = is_null($data) ? null : json_encode($data);
		return parent::__construct($data, $http_code, array(
			"content-type" => "application/json"
		));
	}
}