<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Payment Controller
 *
 * Handles payment processing, installment management, and payment-related operations
 * in the multi-tenant e-commerce system.
 */
class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of payments.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request)
    {
        // Handle DataTables server-side processing
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $query = Payment::with(['order', 'user']);

                // Apply custom filters
                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('payment_number', 'like', "%{$search}%")
                          ->orWhere('transaction_id', 'like', "%{$search}%")
                          ->orWhereHas('order', function ($orderQuery) use ($search) {
                              $orderQuery->where('order_number', 'like', "%{$search}%");
                          });
                    });
                }

                if ($request->has('status') && !empty($request->status)) {
                    $query->where('status', $request->status);
                }

                if ($request->has('payment_type') && !empty($request->payment_type)) {
                    $query->where('payment_type', $request->payment_type);
                }

                if ($request->has('payment_method') && !empty($request->payment_method)) {
                    $query->where('payment_method', $request->payment_method);
                }

                if ($request->has('date_from') && !empty($request->date_from)) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }

                if ($request->has('date_to') && !empty($request->date_to)) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }

                // For customers, only show their payments
                if (!Auth::user()->isAdmin()) {
                    $query->where('user_id', Auth::id());
                }

                // Handle DataTables parameters
                $totalRecords = $query->count();

                // Apply ordering
                if ($request->has('order') && isset($request->order[0])) {
                    $orderColumn = $request->order[0]['column'];
                    $orderDir = $request->order[0]['dir'];

                    $columns = ['payment_number', 'transaction_id', 'order_id', 'user_id', 'payment_type', 'payment_method', 'amount', 'status', 'created_at'];
                    if (isset($columns[$orderColumn])) {
                        if ($columns[$orderColumn] === 'order_id') {
                            $query->join('orders', 'payments.order_id', '=', 'orders.id')
                                  ->orderBy('orders.order_number', $orderDir);
                        } elseif ($columns[$orderColumn] === 'user_id') {
                            $query->join('users', 'payments.user_id', '=', 'users.id')
                                  ->orderBy('users.name', $orderDir);
                        } else {
                            $query->orderBy($columns[$orderColumn], $orderDir);
                        }
                    }
                } else {
                    $query->orderBy('created_at', 'desc');
                }

                // Apply pagination
                $start = $request->get('start', 0);
                $length = $request->get('length', 15);
                $payments = $query->skip($start)->take($length)->get();

                return response()->json([
                    'draw' => intval($request->get('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $payments
                ]);
            } catch (\Exception $e) {
                Log::error('Error fetching payments: ' . $e->getMessage());
                return response()->json([
                    'draw' => intval($request->get('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Failed to load payments.'
                ], 500);
            }
        }

        // Regular view response
        try {
            $query = Payment::with(['order', 'user']);

            // For customers, only show their payments
            if (!Auth::user()->isAdmin()) {
                $query->where('user_id', Auth::id());
            }

            $payments = $query->orderBy('created_at', 'desc')->paginate(15);

            return view('payments.index', compact('payments'));
        } catch (\Exception $e) {
            Log::error('Error fetching payments: ' . $e->getMessage());
            return back()->with('error', 'Failed to load payments.');
        }
    }

    /**
     * Process instant payment
     */
    public function processInstantPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:credit_card,debit_card,bank_transfer,cash,digital_wallet',
            'card_number' => 'required_if:payment_method,credit_card,debit_card|string',
            'expiry_month' => 'required_if:payment_method,credit_card,debit_card|integer|between:1,12',
            'expiry_year' => 'required_if:payment_method,credit_card,debit_card|integer|min:' . date('Y'),
            'cvv' => 'required_if:payment_method,credit_card,debit_card|string|size:3',
            'cardholder_name' => 'required_if:payment_method,credit_card,debit_card|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = Order::findOrFail($request->order_id);

        // Check if user can make payment for this order
        if (!Auth::user()->isAdmin() && $order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to order.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Simulate payment processing
            $paymentResult = $this->simulatePaymentGateway($request->all(), $order->total_amount);

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $order->total_amount,
                'payment_method' => $request->payment_method,
                'payment_type' => 'full',
                'status' => 'completed',
                'transaction_id' => $paymentResult['transaction_id'],
                'gateway_response' => $paymentResult['gateway_response'],
                'payment_details' => $paymentResult['details'],
                'processed_at' => now(),
            ]);

            // Update order status
            $order->update(['status' => 'processing']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully!',
                'data' => $payment->load(['order', 'user']),
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process installment payment
     */
    public function processInstallmentPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'installment_id' => 'required|exists:installments,id',
            'payment_method' => 'required|in:credit_card,debit_card,bank_transfer,cash,digital_wallet',
            'card_number' => 'required_if:payment_method,credit_card,debit_card|string',
            'expiry_month' => 'required_if:payment_method,credit_card,debit_card|integer|between:1,12',
            'expiry_year' => 'required_if:payment_method,credit_card,debit_card|integer|min:' . date('Y'),
            'cvv' => 'required_if:payment_method,credit_card,debit_card|string|size:3',
            'cardholder_name' => 'required_if:payment_method,credit_card,debit_card|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $installment = Installment::with('order')->findOrFail($request->installment_id);

        // Check if user can make payment for this installment
        if (!Auth::user()->isAdmin() && $installment->order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to installment.',
            ], 403);
        }

        if ($installment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This installment has already been processed.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Simulate payment processing
            $paymentResult = $this->simulatePaymentGateway($request->all(), $installment->total_amount);

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }

            // Create payment record
            $payment = Payment::create([
                'order_id' => $installment->order_id,
                'user_id' => $installment->order->user_id,
                'amount' => $installment->total_amount,
                'payment_method' => $request->payment_method,
                'payment_type' => 'installment',
                'status' => 'completed',
                'transaction_id' => $paymentResult['transaction_id'],
                'gateway_response' => $paymentResult['gateway_response'],
                'payment_details' => $paymentResult['details'],
                'processed_at' => now(),
            ]);

            // Update installment
            $installment->update([
                'payment_id' => $payment->id,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Check if all installments are paid
            $remainingInstallments = Installment::where('order_id', $installment->order_id)
                ->where('status', 'pending')
                ->count();

            if ($remainingInstallments === 0) {
                $installment->order->update(['status' => 'processing']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Installment payment processed successfully!',
                'data' => $payment->load(['order', 'user']),
                'remaining_installments' => $remainingInstallments,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending installments for a user
     */
    public function getPendingInstallments(Request $request): JsonResponse
    {
        $query = Installment::with(['order', 'order.orderItems.product'])
            ->where('status', 'pending');

        // For customers, only show their installments
        if (!Auth::user()->isAdmin()) {
            $query->whereHas('order', function ($orderQuery) {
                $orderQuery->where('user_id', Auth::id());
            });
        }

        $installments = $query->orderBy('due_date', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $installments,
        ]);
    }

    /**
     * Get payment statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $query = Payment::query();

        // Filter by date range if provided
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // For customers, only their payments
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $stats = [
            'total_payments' => $query->count(),
            'completed_payments' => (clone $query)->where('status', 'completed')->count(),
            'pending_payments' => (clone $query)->where('status', 'pending')->count(),
            'failed_payments' => (clone $query)->where('status', 'failed')->count(),
            'total_amount' => (clone $query)->where('status', 'completed')->sum('amount'),
            'instant_payments' => (clone $query)->where('payment_type', 'full')->count(),
            'installment_payments' => (clone $query)->where('payment_type', 'installment')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Simulate payment gateway processing
     */
    private function simulatePaymentGateway(array $paymentData, float $amount): array
    {
        // Simulate payment processing delay
        // sleep(1);

        // Simulate 95% success rate
        $success = rand(1, 100) <= 95;

        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'TXN_' . strtoupper(uniqid()),
                'gateway_response' => 'Payment completed successfully',
                'details' => [
                    'gateway' => 'simulation',
                    'amount' => $amount,
                    'currency' => 'USD',
                    'processed_at' => now()->toISOString(),
                    'payment_method' => $paymentData['payment_method'] ?? 'unknown',
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Payment declined by gateway',
                'transaction_id' => 'TXN_FAILED_' . strtoupper(uniqid()),
                'gateway_response' => 'Insufficient funds or card declined',
            ];
        }
    }

    /**
     * Show payment form for order
     */
    public function showPaymentForm(Order $order)
    {
        // Check if user can make payment for this order
        if (!Auth::user()->isAdmin() && $order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order.');
        }

        $order->load(['orderItems.product', 'payments', 'installments']);

        return view('payments.form', compact('order'));
    }

    /**
     * Show installment payment form
     */
    public function showInstallmentForm(Installment $installment)
    {
        $installment->load(['order.orderItems.product']);

        // Check if user can make payment for this installment
        if (!Auth::user()->isAdmin() && $installment->order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to installment.');
        }

        if ($installment->status !== 'pending') {
            return redirect()->route('orders.show', $installment->order_id)
                ->with('error', 'This installment has already been processed.');
        }

        return view('payments.installment-form', compact('installment'));
    }

    /**
     * Process refund
     */
    public function processRefund(Request $request, Payment $payment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($payment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed payments can be refunded.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create refund payment record
            $refundPayment = Payment::create([
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => -$request->amount, // Negative amount for refund
                'payment_method' => $payment->payment_method,
                'payment_type' => 'refund',
                'status' => 'completed',
                'transaction_id' => 'REFUND_' . strtoupper(uniqid()),
                'gateway_response' => 'Refund processed',
                'payment_details' => [
                    'original_payment_id' => $payment->id,
                    'refund_reason' => $request->reason,
                    'refund_amount' => $request->amount,
                ],
                'processed_at' => now(),
            ]);

            // Update original payment status if full refund
            if ($request->amount >= $payment->amount) {
                $payment->update(['status' => 'refunded']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully!',
                'data' => $refundPayment,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment methods for dropdown
     */
    public function getPaymentMethods(): JsonResponse
    {
        $methods = [
            ['value' => 'credit_card', 'label' => 'Credit Card', 'icon' => 'fab fa-cc-visa'],
            ['value' => 'debit_card', 'label' => 'Debit Card', 'icon' => 'fas fa-credit-card'],
            ['value' => 'bank_transfer', 'label' => 'Bank Transfer', 'icon' => 'fas fa-university'],
            ['value' => 'digital_wallet', 'label' => 'Digital Wallet', 'icon' => 'fas fa-wallet'],
            ['value' => 'cash', 'label' => 'Cash', 'icon' => 'fas fa-money-bill'],
        ];

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    /**
     * Show payment details
     */
    public function show(Payment $payment, Request $request)
    {
        // Check if user can view this payment
        if (!Auth::user()->isAdmin() && $payment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to payment.');
        }

        $payment->load(['order.orderItems.product', 'user']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $payment,
            ]);
        }

        return view('payments.show', compact('payment'));
    }
}
