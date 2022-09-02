<?php

namespace App\Support\Payment\Gateways;

use App\Models\Order;
use App\Support\Payment\Gateways\GatewayInterface;
use DateTime;
use Illuminate\Http\Request;
use nusoap_client;
use SoapClient;

class Sepehr implements GatewayInterface
{
    private $merchantID;
    private $callback;
    protected $serverUrl = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';
    public function __construct()
    {
        $this->merchantID = mt_rand(10000, 99999);

        $this->callback = route('payment.verify', ['gateway' => $this->getName()]);
    }
    public function pay($order, int $amount)
    {
        $this->redirectToBank($order, $amount);
    }
    private function redirectToBank($order, $amount)
    {
        $terminalId        = config('services.payment.mellat.terminal_id');
        $amount         = $amount * 10;
        // $additionalData    = auth()->user()->phone_number; 
        $amount             = $amount * 10; // Rial
        $invoiceNumber         = $order->code; // شماره سفارشی که در دیتابیس ذخیره می کنید
        $redirectAddress     = $this->callback; // آدرس بازگشت به سایت برای تایید تراکنش

        // Post Data 
        $data = array(
            'Amount' => $amount,
            'callbackURL' => $redirectAddress,
            'invoiceID' => $invoiceNumber,
            'terminalID' => $terminalId,
            'payload' => ''
        );

        $dataQuery = http_build_query($data);
        $AddressServiceToken = "https://sepehr.shaparak.ir:8081/V1/PeymentApi/GetToken";

        // Get Token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $AddressServiceToken);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataQuery);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $TokenArray = curl_exec($ch);
        curl_close($ch);
        $decode_TokenArray = json_decode($TokenArray);

        $Status = $decode_TokenArray->Status;
        $AccessToken = $decode_TokenArray->Accesstoken;


        if (!empty($AccessToken) && $Status == 0) {
            $AddressIpgPay = "https://sepehr.shaparak.ir:8080/pay";

            echo '<form id="paymentUTLfrm" action="' . $AddressIpgPay . '" method="POST">
            <input type="hidden" id="TerminalID" name="TerminalID" value="' . $terminalId . '">
            <input type="hidden" id="Amount" name="Amount" value="' . $amount . '">
            <input type="hidden" id="callbackURL" name="callbackURL" value="' . $redirectAddress . '">
            <input type="hidden" id="InvoiceID" name="InvoiceID" value="' . $invoiceNumber . '">
            <input type="hidden" id="Payload" name="Payload" value="">
            <script>document.forms["paymentUTLfrm"].submit()</script>
        </form>';
        } else echo "با خطا مواجه شد به عقب برگشته و درگاه دیگری انتخاب کنید";
    }
    public function verify(Request $request)
    {


        $terminal = config('services.payment.mellat.terminal_id'); // شماره ترمینال (TID)

        $invoiceNumber = $request->input('invoiceid');
        $digitalreceipt = $request->input('digitalreceipt');
        $refNum = $request->input('tracenumber');
        $order = $this->getOrder($invoiceNumber);
        $amount = $order->payment->amount * 10; // Rial

        if ($request->input('respcode') == '0') {
            $params = "digitalreceipt={$digitalreceipt}&Tid={$terminal}";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://sepehr.shaparak.ir:8081/V1/PeymentApi/Advice');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($res, true);

            if (strtoupper($result['Status']) == 'OK') {
                if (floatval($result['ReturnId']) == floatval($amount)) {
                    return $this->transactionSuccess($order, $refNum, 0);
                    // echo "Transaction Successful - invoice Number : {$invoiceNumber} -    reference Id : {$digitalreceipt}";
                } else {
                    return $this->transactionFailed(1000, $invoiceNumber);
                    echo "مبلغ واریز با قیمت محصول برابر نیست ، مبلغ واریزی : {$result['ReturnId']}";
                }
            } else {
                switch ($result['ReturnId']) {
                    case '-1':
                        $err = 'تراکنش پیدا نشد';
                        return $this->transactionFailed(1000, $invoiceNumber);
                        break;
                    default:
                        $err = 'خطای ناشناس : ' . $result['ReturnId'];
                        return $this->transactionFailed(1000, $invoiceNumber);
                        break;
                }
                // echo $err;
            }
        } else {
            return $this->transactionFailed(1000, $invoiceNumber);
            $resultCode = 'برگشت ناموفق از درگاه';
        }
    }
    public function getName(): string
    {
        return "sepehr";
    }
    private function transactionSuccess($order, $refNum, $status)
    {
        return [
            'status' => (int)$status,
            'order' => $order,
            'refNum' => $refNum,
            'gateway' => $this->getName()
        ];
    }
    private  function getOrder($resNum)
    {
        return Order::where('code', $resNum)->firstOrFail();
    }
    private function transactionFailed($status, $sale)
    {
        return [
            'status' => (int)$status,
            'sale' => (int)$sale,
        ];
    }
}
