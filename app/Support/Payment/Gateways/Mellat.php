<?php

namespace App\Support\Payment\Gateways;

use App\Models\Order;
use App\Support\Payment\Gateways\GatewayInterface;
use DateTime;
use Illuminate\Http\Request;
use nusoap_client;
use SoapClient;

class Mellat implements GatewayInterface
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
        $terminalId        = config('services.payment.mellat.terminal_id');                    // Terminal ID
        $userName        = config('services.payment.mellat.username');                       // Username
        $userPassword    = config('services.payment.mellat.user_password');                    // Password
        $orderId        = $order->code;                                                        // Order ID
        $amount         = $amount * 10;                                                        // Price / Rial
        $localDate        = date('Ymd');                                                        // Date
        $localTime        = date('Gis');                                                        // Time
        $additionalData    = auth()->user()->phone_number;                                      // 
        $callBackUrl    = $this->callback;                // Callback URL
        $payerId        = 0;

        $parameters = array(
            'terminalId'         => $terminalId,
            'userName'             => $userName,
            'userPassword'         => $userPassword,
            'orderId'             => $orderId,
            'amount'             => $amount,
            'localDate'         => $localDate,
            'localTime'         => $localTime,
            'additionalData'     => $additionalData,
            'callBackUrl'         => $callBackUrl,
            'payerId'             => $payerId
        );

        $client = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
        $namespace = 'http://interfaces.core.sw.bps.com/';
        $result     = $client->call('bpPayRequest', $parameters, $namespace);
        // echo "<form id='mellatpayment' action='https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl' method='post'>
        //     <input type='hidden' name='terminalId' value='{$terminalId}'/>
        //     <input type='hidden' name='userName' value='{$userName}'/>
        //     <input type='hidden' name='userPassword' value='{$userPassword}'/>
        //     <input type='hidden' name='orderId' value='{$order->id}'>
        //     <input type='hidden' name='amount' value='{$amount}' />
        //     <input type='hidden' name='localDate' value='{$localDate}'>
        //     <input type='hidden' name='localTime' value='{$localTime}'>
        //     <input type='hidden' name='additionalData' value='{$additionalData}'>
        //     <input type='hidden' name='callBackUrl' value='{$callBackUrl}'/>
        //     <input type='hidden' name='payerId' value='{$payerId}'/>
        //     </form><script>document.forms['mellatpayment'].submit()</script>";
        if ($client->fault) {
            echo "There was a problem connecting to Bank";
            exit;
        } else {
            $err = $client->getError();
            if ($err) {
                echo "Error : " . $err;
                exit;
            } else {
                $res         = explode(',', $result);
                $ResCode     = $res[0];
                if ($ResCode == "0") {
                    //-- ???????????? ???? ?????????? ????????????
                    echo '<form name="myform" action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat" method="POST">
                            <input type="hidden" id="RefId" name="RefId" value="' . $res[1] . '">
                        </form>
                        <script type="text/javascript">window.onload = formSubmit; function formSubmit() { document.forms[0].submit(); }</script>';
                    exit;
                } else {
                    //-- ?????????? ??????
                    echo "Error : " . $result;
                    exit;
                }
            }
        }
    }
    public function verify(Request $request)
    {
        // dd($request->all());
        if ($request->input('ResCode') == '0') {
            //--???????????? ???? ???????? ???????????????? ????????
            $client = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
            $namespace = 'http://interfaces.core.sw.bps.com/';

            $terminalId                = config('services.payment.mellat.terminal_id');                    // Terminal ID
            $userName                = config('services.payment.mellat.username');                        // Username
            $userPassword            = config('services.payment.mellat.user_password');                    // Password
            $orderId                 = $request->input('SaleOrderId');        // Order ID

            $verifySaleOrderId         = $request->input('SaleOrderId');
            $verifySaleReferenceId     = $request->input('SaleReferenceId');

            $parameters = array(
                'terminalId' => $terminalId,
                'userName' => $userName,
                'userPassword' => $userPassword,
                'orderId' => $orderId,
                'saleOrderId' => $verifySaleOrderId,
                'saleReferenceId' => $verifySaleReferenceId
            );
            // Call the SOAP method
            $result = $client->call('bpVerifyRequest', $parameters, $namespace);
            $order = $this->getOrder($request->input('SaleOrderId'));
            if ($result == '0') {
                //-- ???????????? ???? ?????????? ?????????? ?????? ?????????????? ?????????? ??????
                // Call the SOAP method
                $result = $client->call('bpSettleRequest', $parameters, $namespace);
                if ($result == '0') {
                    //-- ???????? ?????????? ???????????? ???? ?????????? ?????????? ????.
                    //-- ?????????? ???????? ??????????
                    return $this->transactionSuccess($order, $request->input('RefId'), $result);
                } else {
                    //-- ???? ?????????????? ?????????? ?????? ???????? ???? ???????? ??????. ?????????????? ???????????? ?????? ???????? ??????.
                    $client->call('bpReversalRequest', $parameters, $namespace);
                    return $this->transactionFailed($result , $request->input('SaleOrderId'));
                }
            } else {
                //-- ???????????? ???? ???????? ?????????? ?????????? ?????????? ?????? ?? ???????????? ?????? ????????
                $client->call('bpReversalRequest', $parameters, $namespace);
                return $this->transactionFailed($result , $request->input('SaleOrderId'));
                echo 'Error : ' . $result;
            }
        } else {
            //-- ???????????? ???? ?????? ?????????? ????????
            if ($request->input('ResCode') == '17') {
                // return 17;
                return $this->transactionFailed($request->input('ResCode') , $request->input('SaleOrderId'));
            }
            return $this->transactionFailed($request->input('ResCode') , $request->input('SaleOrderId'));
        }
    }
    public function getName(): string
    {
        return "mellat";
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
    private function transactionFailed($status , $sale)
    {
        return [
            'status' => (int)$status,
            'sale' => (int)$sale,
        ];
    }
}
