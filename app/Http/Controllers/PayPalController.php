<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PayPalController extends Controller
{
    private $apiContext;

    public function __construct()
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                env('PAYPAL_CLIENT_ID'),
                env('PAYPAL_SECRET')
            )
        );
        $this->apiContext->setConfig(['mode' => env('PAYPAL_MODE')]);
    }

public function createPayment(Request $request)
{
    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    $amount = new Amount();
    $amount->setCurrency('USD');
    $amount->setTotal(number_format((float) $request->total_amount, 2, '.', ''));

    $transaction = new Transaction();
    $transaction->setAmount($amount);
    $transaction->setDescription("Payment for Booking #" . $request->booking_id);

    $redirectUrls = new RedirectUrls();
    $redirectUrls->setReturnUrl(route('paypal.success'))
                 ->setCancelUrl(route('paypal.cancel'));

    $payment = new Payment();
    $payment->setIntent('sale');
    $payment->setPayer($payer);
    $payment->setRedirectUrls($redirectUrls);
    $payment->setTransactions([$transaction]); // Make sure this is an array of Transaction objects

    try {
        $payment->create($this->apiContext);
        return redirect()->away($payment->getApprovalLink());
    } catch (\Exception $ex) {
        \Log::error('PayPal Error: ' . $ex->getMessage());
        return redirect()->back()->with('error', 'Something went wrong with PayPal: ' . $ex->getMessage());
    }
}


    public function success(Request $request)
    {
        $paymentId = $request->paymentId;
        $payment = Payment::get($paymentId, $this->apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($request->PayerID);

        try {
            $result = $payment->execute($execution, $this->apiContext);

            // Handle post-payment logic (store record in database, etc.)
            return redirect()->back()->with('success', 'Payment successful');
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', 'Payment failed');
        }
    }

    public function cancel()
    {
        return redirect()->back()->with('error', 'Payment was canceled');
    }
}



?>