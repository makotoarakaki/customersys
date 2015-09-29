<?php

class HttpInvalidInputException extends HttpException {
	public function response(){
		$response = Request::forge('error/invalid')
			->execute(array($this->geMessage()))->response();
		return $response;
	}
}

