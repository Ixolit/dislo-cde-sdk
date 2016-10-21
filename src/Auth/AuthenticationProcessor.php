<?php

namespace Ixolit\Dislo\CDE\Auth;

use Ixolit\Dislo\CDE\Exceptions\CookieNotSetException;
use Ixolit\Dislo\CDE\Interfaces\RequestAPI;
use Ixolit\Dislo\CDE\Interfaces\ResponseAPI;
use Ixolit\Dislo\Client;
use Ixolit\Dislo\Exceptions\AuthenticationException;
use Ixolit\Dislo\Exceptions\AuthenticationInvalidCredentialsException;
use Ixolit\Dislo\Exceptions\AuthenticationRateLimitedException;
use Ixolit\Dislo\Exceptions\InvalidTokenException;
use Ixolit\Dislo\Exceptions\ObjectNotFoundException;

class AuthenticationProcessor {
	/**
	 * @var RequestAPI
	 */
	private $requestApi;

	/**
	 * @var ResponseAPI
	 */
	private $responseApi;
	/**
	 * @var int
	 */
	private $tokenTimeout;

	/**
	 * @param RequestAPI  $requestApi
	 * @param ResponseAPI $responseApi
	 * @param int         $tokenTimeout
	 */
	public function __construct(RequestAPI $requestApi, ResponseAPI $responseApi, $tokenTimeout = 2592000) {
		$this->requestApi  = $requestApi;
		$this->responseApi = $responseApi;
		$this->tokenTimeout = $tokenTimeout;
	}

	/**
	 * Authenticate a user. If successful, the authentication token is set into a cookie, and also returned.
	 *
	 * @param string $uniqueUserField
	 * @param string $password
	 *
	 * @return string
	 *
	 * @throws AuthenticationRateLimitedException
	 * @throws AuthenticationInvalidCredentialsException
	 * @throws AuthenticationException
	 */
	public function authenticate($uniqueUserField, $password) {
		$apiClient = new Client();
		$authenticationResponse = $apiClient->userAuthenticate(
			$uniqueUserField,
			$password,
			$this->requestApi->getRemoteAddress()->__toString(),
			$this->tokenTimeout
		);
		$this->responseApi->setCookie('auth-token', $authenticationResponse->getAuthToken(), $this->tokenTimeout);
		return $authenticationResponse->getAuthToken();
	}

	/**
	 * Invalidate the current authentication token.
	 */
	public function deauthenticate() {
		$authToken = $this->requestApi->getCookie('auth-token');
		if ($authToken) {
			$apiClient = new Client();
			try {
				$apiClient->userDeauthenticate($authToken);
			} catch (ObjectNotFoundException $e) {
			}
		}
	}

	/**
	 * Checks and extends a token from cookie.
	 *
	 * @return string
	 *
	 * @throws AuthenticationRequiredException
	 */
	public function extendToken() {
		try {
			$authToken = $this->requestApi->getCookie('auth-token')->getValue();
		} catch (CookieNotSetException $e) {
			throw new AuthenticationRequiredException();
		}
		$apiClient = new Client();
		try {
			$apiClient->userUpdateToken($authToken, 'x', $this->requestApi->getRemoteAddress());
			$this->responseApi->setCookie('auth-token', $authToken, $this->tokenTimeout);
			return $authToken;
		} catch (ObjectNotFoundException $e) {
			throw new AuthenticationRequiredException();
		} catch (InvalidTokenException $e) {
			throw new AuthenticationRequiredException();
		}
	}
}
