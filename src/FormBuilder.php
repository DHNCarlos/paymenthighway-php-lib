<?php namespace Solinor\PaymentHighway;


use Solinor\PaymentHighway\Model\Form;
use Solinor\PaymentHighway\Model\SecureSigner;

class FormBuilder {

    static $METHOD_POST = "POST";
    static $SPH_ACCOUNT = "sph-account";
    static $SPH_MERCHANT = "sph-merchant";
    static $SPH_AMOUNT = "sph-amount";
    static $SPH_CURRENCY = "sph-currency";
    static $SPH_ORDER = "sph-order";
    static $SPH_SUCCESS_URL = "sph-success-url";
    static $SPH_FAILURE_URL = "sph-failure-url";
    static $SPH_CANCEL_URL = "sph-cancel-url";
    static $SPH_REQUEST_ID = "sph-request-id";
    static $SPH_TIMESTAMP = "sph-timestamp";
    static $LANGUAGE = "language";
    static $DESCRIPTION = "description";
    static $SIGNATURE = "signature";

    static $ADD_CARD_URI = "/form/view/add_card";
    static $PAYMENT_URI = "/form/view/pay_with_card";
    static $ADD_AND_PAY_URI = "/form/view/add_and_pay_with_card";


    private $method = 'POST';
    private $baseUrl = null;
    private $signatureKeyId = null;
    private $signatureSecret = null;
    private $account = null;
    private $merchant = null;
    private $successUrl = null;
    private $failureUrl = null;
    private $cancelUrl = null;
    private $language = null;

    /**
     * @param string $method
     * @param string $signatureKeyId
     * @param string $signatureSecret
     * @param string $account
     * @param string $merchant
     * @param string $baseUrl
     * @param string $successUrl
     * @param string $failureUrl
     * @param string $cancelUrl
     * @param string $language
     */
    public function __construct( $method, $signatureKeyId, $signatureSecret, $account,
                                 $merchant, $baseUrl, $successUrl, $failureUrl,
                                 $cancelUrl, $language)
    {
        $this->method = $method;
        $this->signatureKeyId = $signatureKeyId;
        $this->signatureSecret = $signatureSecret;
        $this->account = $account;
        $this->merchant = $merchant;
        $this->baseUrl = $baseUrl;
        $this->successUrl = $successUrl;
        $this->failureUrl = $failureUrl;
        $this->cancelUrl = $cancelUrl;
        $this->language = $language;
    }

    /**
     * Get parameters for add card request
     *
     * @return Form
     */
    public function generateAddCardParameters()
    {
        $commonParameters = $this->createFormParameterArray();

        ksort($commonParameters, SORT_DESC);

        $signature = $this->createSecureSign(self::$ADD_CARD_URI, $commonParameters);

        $commonParameters[self::$LANGUAGE] = $this->language;
        $commonParameters[self::$SIGNATURE] = $signature;

        return new Form(self::$METHOD_POST, $this->baseUrl, self::$ADD_CARD_URI, $commonParameters);
    }

    /**
     * Get parameters for Payment request.
     *
     * @param string $amount
     * @param string $currency
     * @param string $orderId
     * @param string $description
     * @return Form
     */
    public function generatePaymentParameters($amount, $currency, $orderId, $description)
    {
        $commonParameters = $this->createFormParameterArray();

        $commonParameters[self::$SPH_AMOUNT] = $amount;
        $commonParameters[self::$SPH_CURRENCY] = $currency;
        $commonParameters[self::$SPH_ORDER] = $orderId;

        ksort($commonParameters, SORT_DESC);

        $signature = $this->createSecureSign(self::$PAYMENT_URI, $commonParameters);

        $commonParameters[self::$DESCRIPTION] = $description;
        $commonParameters[self::$LANGUAGE] = $this->language;
        $commonParameters[self::$SIGNATURE] = $signature;

        return new Form(self::$METHOD_POST, $this->baseUrl, self::$PAYMENT_URI, $commonParameters);

    }

    /**
     * Get parameters for adding and paying with card request.
     *
     * @param string $amount
     * @param string $currency
     * @param string $orderId
     * @param string $description
     * @return Form
     */
    public function generateAddCardAndPaymentParameters($amount, $currency, $orderId, $description)
    {
        $commonParameters = $this->createFormParameterArray();

        $commonParameters[self::$SPH_AMOUNT] = $amount;
        $commonParameters[self::$SPH_CURRENCY] = $currency;
        $commonParameters[self::$SPH_ORDER] = $orderId;

        ksort($commonParameters, SORT_DESC);

        $signature = $this->createSecureSign(self::$ADD_AND_PAY_URI, $commonParameters);

        $commonParameters[self::$DESCRIPTION] = $description;
        $commonParameters[self::$LANGUAGE] = $this->language;
        $commonParameters[self::$SIGNATURE] = $signature;

        return new Form(self::$METHOD_POST, $this->baseUrl, self::$ADD_AND_PAY_URI, $commonParameters);
    }

    /**
     * @return array
     */
    private function createFormParameterArray()
    {
        $parameterArray = array(
            self::$SPH_SUCCESS_URL => $this->successUrl,
            self::$SPH_FAILURE_URL => $this->failureUrl,
            self::$SPH_CANCEL_URL => $this->cancelUrl,
            self::$SPH_ACCOUNT => $this->account,
            self::$SPH_MERCHANT => $this->merchant,
            self::$SPH_TIMESTAMP => PaymentHighwayUtility::getDate(),
            self::$SPH_REQUEST_ID => PaymentHighwayUtility::createRequestId(),
        );

        return $parameterArray;
    }

    /**
     * @param string $uri
     * @param array $sphNameValuePairs
     * @return string formatted signature
     */
    private function createSecureSign($uri, $sphNameValuePairs = array())
    {
        $parsedSphParameters = PaymentHighwayUtility::parseSphParameters($sphNameValuePairs);
        $secureSigner = new SecureSigner($this->signatureKeyId, $this->signatureSecret);

        return $secureSigner->createSignature(self::$METHOD_POST, $uri, $parsedSphParameters);

    }

}