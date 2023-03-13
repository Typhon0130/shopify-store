<?php

namespace App\Http\Controllers;

use App\Exports\ShipmentarchiveExport;
use Illuminate\Http\Request;
use App\Models\Rejected;
use App\Models\Order;
use App\Models\OrderData;
use App\Models\User;
use App\Models\Plan;
use App\Models\merchant_fee;
use App\Models\Ps_payment;
use App\Models\Sku;
use Intervention\Image\ImageManagerStatic as Image;
use \Statickidz\GoogleTranslate;
use Illuminate\Support\Facades\Auth;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

use Alexcherniatin\DHL\DHL24;
use Alexcherniatin\DHL\Utils;
use Alexcherniatin\DHL\Structures\Address;
use Alexcherniatin\DHL\Structures\PaymentData;
use Alexcherniatin\DHL\Structures\Piece;
use Alexcherniatin\DHL\Structures\ReceiverAddress;
use Alexcherniatin\DHL\Structures\ServiceDefinition;
use Alexcherniatin\DHL\Structures\ShipmentFullData;
use Alexcherniatin\DHL\Structures\ItemToPrint;
use App\Models\Customsku;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Contracts\Support\Renderable
	 */
	public function index()
	{
		return view('welcome');
	}


	public function simpleUpload()
	{
		return view('silent.simpleupload');
	}


	public function processUpload(Request $request)
	{
		$orderName = $request->ordername;
		$imageNumber = $request->imagenumber;
		$file = $request->file('orderimage')->getRealPath();

		Image::make($file)
			->save(public_path('archive/' . '#omv' . $orderName . '/' . $orderName . '_' . ($imageNumber - 1) . '.png'))
			->resize(150, null, function ($constraint) {
				$constraint->aspectRatio();
			})
			->save(public_path('uploads/thumbs/' . $orderName . '_' . ($imageNumber - 1) . '.png'));
		$order = Order::where('name', '#omv' . $orderName)->first();
		$orderDatas = OrderData::where('order_id', $order->id)->get();
		$orderDataId = $orderDatas[$imageNumber - 1]->id;
		$orderData = OrderData::where('id', $orderDataId)->first();
		if ($orderData->image === null)
			OrderData::where('id', $orderData->id)->update(['image' => $orderName . '_' . ($imageNumber - 1) . '.png']);
		return view('silent.simpleupload');
	}


	public function shipmentArchive(Request $request)
	{

		$orders = isset($request->from)
			? Order::where('calledDHL_at', '>=', $request->from)
			: Order::where('calledDHL_at', '>=', '2022-01-01');

		$orders = $orders->where(function ($query) use ($request) {
			if (isset($request->to))
				$query->whereDate('calledDHL_at', '<=', $request->to);

			if (isset($request->search))
				$query->where('shipment_order', 'like', '%' . $request->search . '%')
					->orWhere('first_name',   'like', '%' . $request->search . '%')
					->orWhere('last_name',    'like', '%' . $request->search . '%')
					->orWhere('name',         'like', '%' . $request->search . '%');
		});

		$orders = $orders->paginate(50);

		return view('shipment-archive', ['title' => "Shipment archive", 'orders' => $orders]);
	}


	public function downloadExcel(Request $request, $type)
	{
		return Excel::download(new ShipmentarchiveExport($request->search, $request->from, $request->to), 'orders.xls');
	}

	public function bestsubProducts()
	{
		$products = \App\Models\Foreigndata::take(3)->get();
		return view('bestsub_products', ['title' => 'Product catalogue', 'products' => $products]);
	}


	public function appFees()
	{
		$appFees = \App\Models\merchant_fee::where('user_id', Auth::User()->id)->orderBy('id', 'desc')->paginate(30);
		return view('app_fees', ['title' => 'Application fees', 'appFees' => $appFees]);
	}

	public function validateTransaction($cid)
	{
		//Get token from paypal
		$auth    = env('PAYPAL_CLIENT_ID_FR', '') . ':' . env('PAYPAL_CLIENT_SECRET_FR', '');
		$headers = ['Content-Type: application/x-www-form-urlencoded'];
		$data    = 'grant_type=client_credentials';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, env('PAYPAL_TOKEN_URL', ''));
		curl_setopt($curl, CURLOPT_USERPWD, $auth);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($curl);
		curl_close($curl);
		$op = json_decode($output);

		//Validate transaction
		$headers = ['Content-Type:application/json', 'Authorization:Bearer ' . $op->access_token];
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, env('PAYPAL_ORDER_VERIFY_URL', '') . $cid);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($curl);
		curl_close($curl);
		$res = json_decode($output);

		// Get user id
		$userid = $res->purchase_units[0]->items[0]->description;
		$amount = $res->purchase_units[0]->amount->value;
		$user = \App\Models\User::where('id', $userid)->first();
		// Add balance to current balance
		User::where('id', $userid)->update(['balance' => $user->balance + $amount]);
		\App\Models\merchant_fee::create(['user_id' => $userid, 'order_name' => 'Fill balance ', 'fee' => $amount]);

		/*** CHECK ANY 1) MONTH MEMBERSHIP AND 2) UNPAID ORDERS */
		// first check if user has selected any plan
		if ($user->current_plan) {
			$balance  = $user->balance + $amount;
			$plan     = Plan::where('id', $user->current_plan)->first();
			$monthFee = $plan->month_fee;
			$perc     = $plan->percentage;

			// first check if there is any unpaid orders from last paid month
			if ($this->payAppFees($user->company_id, $user->id, mnthSer2YrMnth($user->last_pay_month)['year'], mnthSer2YrMnth($user->last_pay_month)['month'], $balance, $perc)) {

				// check all month starting from last paid + 1 till current
				for ($i = $user->last_pay_month + 1; $i <= date('Y') * 12 + date('m'); $i++) {
					$currMonth = mnthSer2YrMnth($i)['month'];
					$currYear  = mnthSer2YrMnth($i)['year'];
					
					if($this->payMonthFees($user->id, $currYear, $currMonth, $balance, $monthFee)){
						$this->payAppFees($user->company_id, $user->id, $currYear, $currMonth, $balance, $perc);
					}

				}
			}
		}
		return redirect()->route('profile');
	}

	private function payAppFees($companyId, $userId, $year, $month, &$usrBalance, $perc)
	{
		$paidAll = true;
		$orders = Order::where('paid_FR', 0)->where('company_id', $companyId)->whereYear('created_at', $year)->whereMonth('created_at', $month)->get();
		foreach ($orders as $order) {
			if (!$order->paid_FR) {
				if ($usrBalance >= round($order->total_price * $perc / 100, 2)) {
					$usrBalance = $usrBalance - round($order->total_price * $perc / 100, 2);
					User::where('id', $userId)->update(['balance' => $usrBalance]);
					Order::where('id', $order->id)->update(['paid_FR' => 1]);
					merchant_fee::create(['user_id' => $userId, 'order_name' => 'Order fee ' . $order->name, 'shop_price' => $order->total_price, 'rate' => $perc, 'fee' => -round($order->total_price * $perc / 100, 2)]);
				} else {
					$paidAll = false;
				}
			}
		}
		return $paidAll;
	}

	private function payMonthFees($userId, $year, $month, &$usrBalance, $monthFee){
		if ($usrBalance >= $monthFee) {
			$usrBalance = $usrBalance - $monthFee;
			User::where('id', $userId)->update(['balance' => $usrBalance, 'last_pay_month' => $year * 12 + $month]);
			merchant_fee::create(['user_id' => $userId, 'order_name' => 'Month fee ' . $year . ', ' . date('F', mktime(0, 0, 0, $month, 10)), 'fee' => -$monthFee]);
			return true;
		} else {
			return false;
		}
	}



	public function payPrintfee(Request $request)
	{
		$orders = $request->orders;
		$orders = array_unique($orders);
		$orders = Order::whereIn('id', $orders)->with('files')->get();

		$ord            = [];       // Filtered orders merchant will pay for
		$filteredIds    = [];       // Store order_ids
		$totalPrice     = 0;        // Total price merchant has to pay
		$transactionId  = null;

		foreach ($orders as $order) {
			if (!$order->paid_PS && $order->paid_FR) {   //if order is not paid yet AND paid to FR
				$complete   = true; // Flag we get or not all prices and print prices
				$price      = 0;    // sum all prices
				$printPrice = 0;    // sum all print prices
				$weightFee  = 0;    // weight fee
				foreach ($order->files as $orderItem) {
					if ($orderItem->price != null && $orderItem->print_price != null && $order->weight_fee > 0) {
						$price      += $orderItem->price * $orderItem->quantity;
						$printPrice += $orderItem->print_price * $orderItem->quantity;
					} else {
						$complete = false;
					}
				}
				$weightFee = $order->weight_fee;

				if ($complete) {
					array_push($ord, [
						'id'         => $order->id,
						'orderName'  => $order->name,
						'price'      => $price,
						'printPrice' => $printPrice,
						'weightFee'  => $weightFee,
						'complete'   => $complete
					]);
					$totalPrice = $totalPrice + $price + $printPrice + $weightFee;
					array_push($filteredIds, $order->id);
				}
			}
		}

		if ($filteredIds) {
			$ps_payment = Ps_payment::create(['user_id' => Auth::user()->id, 'orders_array' => json_encode($filteredIds)]);
			return view('pay-print-service', ['orders' => $ord, 'totalPrice' => $totalPrice, 'paymentId' => $ps_payment->id]);
		}
		return redirect()->back();
	}


	public function validatePsTransaction($transId)
	{
		//Get token from paypal
		$auth    = env('PAYPAL_CLIENT_ID_PS', '') . ':' . env('PAYPAL_CLIENT_SECRET_PS', '');
		$headers = ['Content-Type: application/x-www-form-urlencoded'];
		$data    = 'grant_type=client_credentials';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, env('PAYPAL_TOKEN_URL', ''));
		curl_setopt($curl, CURLOPT_USERPWD, $auth);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($curl);
		curl_close($curl);
		$op = json_decode($output);

		//Validate transaction
		$headers = ['Content-Type:application/json', 'Authorization:Bearer ' . $op->access_token];
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, env('PAYPAL_ORDER_VERIFY_URL', '') . $transId);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($curl);
		curl_close($curl);
		$res = json_decode($output);

		// Get payment id
		if ($res->purchase_units[0]->items[0]->description) {
			$paymentId = $res->purchase_units[0]->items[0]->description;
			$payment = Ps_payment::where('id', $paymentId)->first();
			$orders = json_decode($payment->orders_array);
			Order::whereIn('id', $orders)->update(['paid_PS' => 1]);
			Ps_payment::where('id', $paymentId)->update(['transaction_id' => $transId]);
		}
		return redirect()->route('orders', 'home');
	}












}
