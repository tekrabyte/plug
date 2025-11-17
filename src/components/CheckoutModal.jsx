import React, { useState } from "react";
import toast from "react-hot-toast";
import { useCart } from "../store/CartStore";
import { useTenant } from "../store/TenantStore";
import { createOrder } from "../api";
import { calcSubtotal, money } from "../utils";

const PAYMENT_METHODS = [
  { id: 'cash', name: 'Cash', icon: '💵', description: 'Pay with cash' },
  { id: 'card', name: 'Credit/Debit Card', icon: '💳', description: 'Pay with card' },
  { id: 'qris', name: 'QRIS', icon: '📱', description: 'Scan QR code' },
  { id: 'transfer', name: 'Bank Transfer', icon: '🏦', description: 'Transfer to bank account' },
  { id: 'ewallet', name: 'E-Wallet', icon: '📲', description: 'OVO, GoPay, Dana, etc.' },
];

export default function CheckoutModal({ onClose }) {
  const { cart, clear } = useCart();
  const { tenant } = useTenant();
  
  const [selectedPayment, setSelectedPayment] = useState('');
  const [amountReceived, setAmountReceived] = useState('');
  const [customerName, setCustomerName] = useState('');
  const [customerPhone, setCustomerPhone] = useState('');
  const [notes, setNotes] = useState('');
  const [processing, setProcessing] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const [orderId, setOrderId] = useState(null);

  const subtotal = calcSubtotal(cart);
  const taxRate = 0.10; // 10% - should come from settings
  const tax = subtotal * taxRate;
  const total = subtotal + tax;
  const change = amountReceived ? parseFloat(amountReceived) - total : 0;

  const handleCheckout = async () => {
    if (!selectedPayment) {
      setError('Please select a payment method');
      return;
    }

    if (selectedPayment === 'cash' && parseFloat(amountReceived) < total) {
      setError('Insufficient payment amount');
      return;
    }

    setProcessing(true);
    setError('');

    try {
      const payload = {
        tenant_id: tenant?.id || 1,
        items: cart.map(item => ({
          product_id: item.id,
          name: item.name,
          quantity: item.qty,
          price: item.price,
          subtotal: item.price * item.qty,
        })),
        payment_method: selectedPayment,
        amount_received: selectedPayment === 'cash' ? parseFloat(amountReceived) : total,
        customer_name: customerName,
        customer_phone: customerPhone,
        notes: notes,
        subtotal: subtotal,
        tax: tax,
        total: total,
      };

      const result = await createOrder(payload);
      
      setSuccess(true);
      setOrderId(result.order_id || result.id);
      clear();
      
      // Auto close after 3 seconds
      setTimeout(() => {
        onClose();
      }, 3000);
      
    } catch (err) {
      setError(err.message || 'Failed to process checkout');
    } finally {
      setProcessing(false);
    }
  };

  const handlePrintReceipt = () => {
    if (orderId) {
      window.open(`/wp-json/erp/v1/receipt/${orderId}`, '_blank');
    }
  };

  if (success) {
    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center">
          <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 className="text-3xl font-bold text-gray-800 mb-2">Payment Successful!</h2>
          <p className="text-gray-600 mb-2">Order #{orderId}</p>
          <p className="text-4xl font-bold text-green-600 mb-6">{money(total)}</p>
          
          {selectedPayment === 'cash' && change > 0 && (
            <div className="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 mb-6">
              <p className="text-sm text-gray-600 mb-1">Change</p>
              <p className="text-2xl font-bold text-blue-600">{money(change)}</p>
            </div>
          )}
          
          <button
            onClick={handlePrintReceipt}
            className="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-all mb-3 flex items-center justify-center space-x-2"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Print Receipt</span>
          </button>
          
          <p className="text-sm text-gray-500">Closing automatically...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
      <div className="bg-white rounded-2xl shadow-2xl max-w-4xl w-full my-8">
        {/* Header */}
        <div className="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <h2 className="text-2xl font-bold">Checkout</h2>
              <p className="text-sm text-blue-100">{cart.length} items in cart</p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="w-8 h-8 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-all flex items-center justify-center"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div className="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Left Column - Order Summary */}
          <div>
            <h3 className="text-lg font-bold text-gray-800 mb-4">Order Summary</h3>
            <div className="bg-gray-50 rounded-xl p-4 max-h-64 overflow-y-auto mb-4">
              {cart.map((item) => (
                <div key={item.id} className="flex justify-between items-center py-2 border-b border-gray-200 last:border-0">
                  <div className="flex-1">
                    <p className="font-medium text-gray-800">{item.name}</p>
                    {item.variant_name && (
                      <p className="text-xs text-gray-500">{item.variant_name}</p>
                    )}
                  </div>
                  <div className="text-right">
                    <p className="text-sm text-gray-600">x{item.qty}</p>
                    <p className="font-semibold text-gray-800">{money(item.price * item.qty)}</p>
                  </div>
                </div>
              ))}
            </div>

            {/* Totals */}
            <div className="space-y-2 bg-white border-2 border-gray-200 rounded-xl p-4">
              <div className="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span className="font-semibold">{money(subtotal)}</span>
              </div>
              <div className="flex justify-between text-gray-600">
                <span>Tax ({(taxRate * 100).toFixed(0)}%)</span>
                <span className="font-semibold">{money(tax)}</span>
              </div>
              <div className="flex justify-between text-2xl font-bold text-gray-800 pt-3 border-t-2 border-gray-200">
                <span>Total</span>
                <span className="text-blue-600">{money(total)}</span>
              </div>
            </div>

            {/* Customer Info (Optional) */}
            <div className="mt-4 space-y-3">
              <h4 className="font-semibold text-gray-700 text-sm">Customer Info (Optional)</h4>
              <input
                type="text"
                placeholder="Customer Name"
                value={customerName}
                onChange={(e) => setCustomerName(e.target.value)}
                className="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none"
              />
              <input
                type="tel"
                placeholder="Phone Number"
                value={customerPhone}
                onChange={(e) => setCustomerPhone(e.target.value)}
                className="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none"
              />
              <textarea
                placeholder="Notes (optional)"
                value={notes}
                onChange={(e) => setNotes(e.target.value)}
                rows={2}
                className="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none resize-none"
              />
            </div>
          </div>

          {/* Right Column - Payment */}
          <div>
            <h3 className="text-lg font-bold text-gray-800 mb-4">Payment Method</h3>
            <div className="space-y-3 mb-6">
              {PAYMENT_METHODS.map((method) => (
                <button
                  key={method.id}
                  onClick={() => setSelectedPayment(method.id)}
                  className={`w-full p-4 rounded-xl border-2 transition-all text-left ${
                    selectedPayment === method.id
                      ? 'border-blue-500 bg-blue-50 shadow-md'
                      : 'border-gray-200 hover:border-gray-300 bg-white'
                  }`}
                >
                  <div className="flex items-center space-x-3">
                    <div className="text-3xl">{method.icon}</div>
                    <div className="flex-1">
                      <p className="font-semibold text-gray-800">{method.name}</p>
                      <p className="text-sm text-gray-500">{method.description}</p>
                    </div>
                    {selectedPayment === method.id && (
                      <div className="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                        <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                    )}
                  </div>
                </button>
              ))}
            </div>

            {/* Cash Payment - Amount Received */}
            {selectedPayment === 'cash' && (
              <div className="bg-green-50 border-2 border-green-200 rounded-xl p-4 mb-4">
                <label className="block text-sm font-semibold text-gray-700 mb-2">
                  Amount Received
                </label>
                <input
                  type="number"
                  value={amountReceived}
                  onChange={(e) => setAmountReceived(e.target.value)}
                  placeholder="Enter amount received"
                  className="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-green-500 focus:outline-none text-lg font-semibold"
                  step="0.01"
                />
                {amountReceived && (
                  <div className="mt-3 pt-3 border-t border-green-300">
                    <div className="flex justify-between items-center">
                      <span className="text-gray-700 font-medium">Change:</span>
                      <span className={`text-2xl font-bold ${
                        change >= 0 ? 'text-green-600' : 'text-red-600'
                      }`}>
                        {money(Math.abs(change))}
                      </span>
                    </div>
                    {change < 0 && (
                      <p className="text-sm text-red-600 mt-2">⚠️ Insufficient amount</p>
                    )}
                  </div>
                )}
              </div>
            )}

            {/* Error Message */}
            {error && (
              <div className="bg-red-50 border-2 border-red-200 rounded-lg p-3 mb-4 flex items-center space-x-2">
                <svg className="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p className="text-red-700 text-sm">{error}</p>
              </div>
            )}

            {/* Action Buttons */}
            <div className="space-y-3">
              <button
                onClick={handleCheckout}
                disabled={processing || !selectedPayment}
                className={`w-full py-4 rounded-xl font-bold text-lg transition-all flex items-center justify-center space-x-2 ${
                  processing || !selectedPayment
                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                    : 'bg-gradient-to-r from-green-500 to-green-600 text-white hover:shadow-lg transform hover:-translate-y-0.5'
                }`}
              >
                {processing ? (
                  <>
                    <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                    <span>Processing...</span>
                  </>
                ) : (
                  <>
                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Complete Payment</span>
                  </>
                )}
              </button>
              
              <button
                onClick={onClose}
                disabled={processing}
                className="w-full py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
