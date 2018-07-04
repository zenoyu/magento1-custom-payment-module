<?php
/**
 * Class Showpo_Payment_Model_Showpo
 * @author Zeno Yu <zeno.yu@gmail.com>
 */
class Showpo_Payment_Model_Showpo extends Mage_Payment_Model_Method_Abstract {

    // API Endpoint (Todo: move to configurable from admin)
    const ENDPOINT = "https://uat.showpo.com/test-api.php";

    // this payment code
    protected $_code = 'showpo';

    // Backend admin only
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;

    protected $_canCapture              = true;

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function capture(Varien_Object $payment, $amount){

        if (!$this->canCapture()) {
            Mage::throwException($this->_getHelper()->__('Capture action is not available.'));
        }

        // Get Info
        $amount = $this->_getAmount();
        $customerId = $this->_getCustomerId();
        $quoteId = $this->_getQuoteId();

        // Submit Data to ShowPo
        $txnRef = $this->_submitData($amount, $customerId, $quoteId);
        // Error handling
        if (!$txnRef) {
            throw new Mage_Payment_Model_Info_Exception($this->_getHelper()->__('Showpo payment error'));
        }

        // Save the Showpo Reference
        $payment->setShowpoRef($txnRef);

        return $this;
    }

    /**
     * Submit Data to ShowPo
     *
     * @param $amount
     * @param $customerId
     * @param $quoteId
     * @return string
     */
    private function _submitData($amount, $customerId, $quoteId) {
        // Params
        $params = array(
            "amount" => $amount,
            "customer_id" => $customerId,
            "reference" => $quoteId);
        // JSON raw body header
        $headers = array(
            'Content-Type: text/plain'
        );
        try {

            // Curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::ENDPOINT);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);
        } catch (Exception $ex) {
            return false;
        }
        // TODO: network failure / timeout handling
        if ($response) {
            $jsonData = json_decode($response,true);

            if (isset($jsonData['status']) && $jsonData['status'] == 'Success') {
                $txnRef = $jsonData['txn_ref'];
                return $txnRef;
            }
        }
        return false;
    }


    /**
     * Grand total getter
     *
     * @return string
     */
    private function _getAmount()
    {
        $info = $this->getInfoInstance();
        if ($this->_isPlaceOrder()) {
            return (double)$info->getOrder()->getQuoteBaseGrandTotal();
        } else {
            return (double)$info->getQuote()->getBaseGrandTotal();
        }
    }

    /**
     * Quote ID getter
     *
     * @return string
     */
    private function _getQuoteId()
    {
        $info = $this->getInfoInstance();
        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getQuoteId();
        } else {
            return $info->getQuote()->getId();
        }
    }

    /**
     * Customer ID getter
     *
     * @return string
     */
    private function _getCustomerId()
    {
        $info = $this->getInfoInstance();
        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getCustomerId();
        } else {
            return $info->getQuote()->getCustomer()->getId();
        }
    }

    /**
     * Whether current operation is order placement
     *
     * @return bool
     */
    private function _isPlaceOrder()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return false;
        } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
            return true;
        }
    }
}
