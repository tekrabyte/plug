import React, { useState } from "react";
import { Toaster } from "react-hot-toast";
import ProductGrid from "./components/ProductGrid";
import CartPanel from "./components/CartPanel";
import CheckoutModal from "./components/CheckoutModal";
import TransactionHistory from "./components/TransactionHistory";
import BarcodeScanner from "./components/BarcodeScanner";
import SalesDashboard from "./components/SalesDashboard";

import { ProductProvider } from "./store/ProductStore";
import { CartProvider } from "./store/CartStore";
import { TenantProvider } from "./store/TenantStore";

export default function App() {
  const [activeView, setActiveView] = useState('pos'); // pos, transactions, dashboard
  const [showCheckout, setShowCheckout] = useState(false);
  const [showScanner, setShowScanner] = useState(false);

  return (
    <TenantProvider>
      <ProductProvider>
        <CartProvider>
          <div className="min-h-screen bg-gradient-to-br from-slate-100 to-slate-200">
            {/* Top Navigation Bar */}
            <nav className="bg-white shadow-lg border-b-4 border-blue-600">
              <div className="container mx-auto px-4">
                <div className="flex items-center justify-between h-16">
                  <div className="flex items-center space-x-3">
                    <div className="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                    </div>
                    <div>
                      <h1 className="text-xl font-bold text-gray-800">ERP POS System</h1>
                      <p className="text-xs text-gray-500">Multi-tenant Point of Sale</p>
                    </div>
                  </div>
                  
                  <div className="flex space-x-2">
                    <button
                      onClick={() => setActiveView('pos')}
                      className={`px-4 py-2 rounded-lg font-medium transition-all ${
                        activeView === 'pos'
                          ? 'bg-blue-600 text-white shadow-lg'
                          : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                      }`}
                    >
                      <svg className="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                      POS
                    </button>
                    <button
                      onClick={() => setActiveView('transactions')}
                      className={`px-4 py-2 rounded-lg font-medium transition-all ${
                        activeView === 'transactions'
                          ? 'bg-blue-600 text-white shadow-lg'
                          : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                      }`}
                    >
                      <svg className="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                      </svg>
                      Transactions
                    </button>
                    <button
                      onClick={() => setActiveView('dashboard')}
                      className={`px-4 py-2 rounded-lg font-medium transition-all ${
                        activeView === 'dashboard'
                          ? 'bg-blue-600 text-white shadow-lg'
                          : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                      }`}
                    >
                      <svg className="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                      </svg>
                      Dashboard
                    </button>
                  </div>
                </div>
              </div>
            </nav>

            {/* Main Content Area */}
            <div className="container mx-auto px-4 py-6">
              {activeView === 'pos' && (
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                  {/* Products Section */}
                  <div className="lg:col-span-2">
                    <div className="bg-white rounded-xl shadow-lg p-6">
                      <div className="flex items-center justify-between mb-4">
                        <h2 className="text-2xl font-bold text-gray-800">Products</h2>
                        <button
                          onClick={() => setShowScanner(true)}
                          className="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center space-x-2"
                        >
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                          </svg>
                          <span>Scan Barcode</span>
                        </button>
                      </div>
                      <ProductGrid />
                    </div>
                  </div>

                  {/* Cart Section */}
                  <div className="lg:col-span-1">
                    <div className="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                      <CartPanel onCheckout={() => setShowCheckout(true)} />
                    </div>
                  </div>
                </div>
              )}

              {activeView === 'transactions' && <TransactionHistory />}
              {activeView === 'dashboard' && <SalesDashboard />}
            </div>

            {/* Modals */}
            {showCheckout && <CheckoutModal onClose={() => setShowCheckout(false)} />}
            {showScanner && <BarcodeScanner onClose={() => setShowScanner(false)} />}
          </div>
        </CartProvider>
      </ProductProvider>
    </TenantProvider>
  );
}
