import React from "react";
import { useCart } from "../store/CartStore";
import CartItem from "./CartItem";
import { calcSubtotal, money } from "../utils";

export default function CartPanel({ onCheckout }) {
  const { cart } = useCart();
  const subtotal = calcSubtotal(cart);
  const tax = subtotal * 0.10; // 10% tax (configurable)
  const total = subtotal + tax;

  return (
    <div className="flex flex-col h-full">
      <div className="flex items-center justify-between mb-4">
        <h2 className="text-2xl font-bold text-gray-800">Cart</h2>
        <span className="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
          {cart.length} {cart.length === 1 ? 'item' : 'items'}
        </span>
      </div>

      {/* Cart Items */}
      <div className="flex-1 overflow-y-auto max-h-[400px] pr-2 space-y-2">
        {cart.length === 0 ? (
          <div className="text-center py-12">
            <svg
              className="w-16 h-16 mx-auto text-gray-300 mb-3"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
            <p className="text-gray-500">Cart is empty</p>
            <p className="text-gray-400 text-sm mt-1">Add products to start</p>
          </div>
        ) : (
          cart.map((item) => <CartItem key={item.id} item={item} />)
        )}
      </div>

      {/* Cart Summary */}
      {cart.length > 0 && (
        <div className="mt-6 pt-4 border-t-2 border-gray-200 space-y-3">
          <div className="flex justify-between text-gray-600">
            <span>Subtotal</span>
            <span className="font-semibold">{money(subtotal)}</span>
          </div>
          <div className="flex justify-between text-gray-600">
            <span>Tax (10%)</span>
            <span className="font-semibold">{money(tax)}</span>
          </div>
          <div className="flex justify-between text-xl font-bold text-gray-800 pt-3 border-t border-gray-200">
            <span>Total</span>
            <span className="text-blue-600">{money(total)}</span>
          </div>
          
          <button
            onClick={onCheckout}
            className="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-bold text-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center space-x-2"
          >
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Checkout</span>
          </button>
        </div>
      )}
    </div>
  );
}
