<?php

namespace mheap\Rest;

class ResponseFactory {

	public static function build($request, $resource, $data=array(), $http_code=200) {

		// Manipulate our data
		if (is_object($data) && method_exists($data, "expose")){
			$data = $data->expose();
		}

		// Should it be in data, or in error?
		if (count($data)){
			$resp = array();
			if (intval($http_code/100) == 2){
				$resp['data'] = $data;
			}
			else
			{
				$resp['error'] = $data;
			}
		}
		else
		{
			$resp = null;
		}

		// Send it in the correct format
		$contentType = $request->getRequestFormat();
		if ($contentType == "html"){ $contentType = "application/json"; } // Default to JSON

		if ($contentType == "application/json"){
			return new JSONResponse($resp, $http_code);
		}

		if ($contentType == "application/xml"){
			return new XMLResponse($resp, $http_code, $resource);
		}

		throw new \Exception("Unknown format: ".$contentType);
	}

}