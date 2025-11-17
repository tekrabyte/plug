import React from "react";
import { useCart } from "../store/CartStore";
import { money } from "../utils";

export default function CartItem({ item }) {
  const { update, remove } = useCart();

  const handleQtyChange = (newQty) => {
    if (newQty < 1) return;
    if (newQty > item.stock) {
      alert(`Only ${item.stock} units available in stock`);
      return;
    }
    update(item.id, newQty);
  };

  return (
    <div className="bg-gray-50 border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between mb-2">
        <div className="flex-1">
          <h4 className="font-semibold text-gray-800 text-sm">{item.name}</h4>
          {item.variant_name && (
            <p className="text-xs text-gray-500 mt-0.5">{item.variant_name}</p>
          )}
        </div>
        <button
          onClick={() => remove(item.id)}
          className="text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full p-1 transition-colors"
          title="Remove item"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-2 bg-white border border-gray-200 rounded-lg">
          <button
            onClick={() => handleQtyChange(item.qty - 1)}
            className="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-l-lg transition-colors"
            disabled={item.qty <= 1}
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
            </svg>
          </button>
          <input
            type="number"
            value={item.qty}
            onChange={(e) => handleQtyChange(parseInt(e.target.value) || 1)}
            className="w-12 text-center border-0 focus:outline-none font-semibold"
            min="1"
            max={item.stock}
          />
          <button
            onClick={() => handleQtyChange(item.qty + 1)}
            className="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-r-lg transition-colors"
            disabled={item.qty >= item.stock}
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
          </button>
        </div>

        <div className="text-right">
          <p className="text-xs text-gray-500">@{money(item.price)}</p>
          <p className="font-bold text-blue-600">{money(item.price * item.qty)}</p>
        </div>
      </div>

      {item.stock <= 10 && (
        <div className="mt-2 text-xs text-yellow-600 bg-yellow-50 px-2 py-1 rounded">
          ⚠️ Only {item.stock} left in stock
        </div>
      )}
    </div>
  );
}
