<?php

namespace App\Http\Controllers;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Models\Order;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Stripe\Charge;
use Stripe\Stripe;

class StripePaymentController extends Controller
{

    public function paymentProcess()
    {
        $order = Order::with(['details'])->where(['id' => session('order_id')])->first();
        $tr_ref = Str::random(6) . '-' . rand(1, 1000);

        try {
            $config = Helpers::get_business_settings('stripe');
            Stripe::setApiKey($config['api_key']);
            $token = $_POST['stripeToken'];
            $payment = Charge::create([
                'amount' => $order->order_amount * 100,
                'currency' => Helpers::currency_code(),
                'description' => $tr_ref,
                'source' => $token
            ]);
        } catch (\Exception $ex) {
            Toastr::error('Your currency is not supported by Stripe.');
            return Redirect::back();
        }

        if ($payment->status == 'succeeded') {
            DB::table('orders')
                ->where('id', $order['id'])
                ->update(['order_status' => 'confirmed', 'transaction_reference' => $tr_ref, 'payment_method' => 'stripe', 'payment_status' => 'paid', 'confirmed'=>now()]);

            $fcm_token = $order->customer->cm_firebase_token;
            $value = Helpers::order_status_update_message('confirmed');

            try {
                OrderLogic::create_transaction($order, 'admin', null);
                if ($value) {
                    $data = [
                        'title' => 'Order',
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type'=>'order_status'
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                    DB::table('user_notifications')->insert([
                        'data'=> json_encode($data),
                        'user_id'=>$order->customer->id,
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }
            } catch (\Exception $e) {
            }
            if ($order->callback != null) {
                return redirect($order->callback . '&status=success');
            } else {
                return \redirect()->route('payment-success');
            }
        } else {
            if ($order->callback != null) {
                return redirect($order->callback . '/fail');
            } else {
                return \redirect()->route('payment-fail');
            }
        }
    }
}
