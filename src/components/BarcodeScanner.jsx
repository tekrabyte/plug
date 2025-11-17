import React, { useState, useRef, useEffect } from "react";
import { useProducts } from "../store/ProductStore";
import { useCart } from "../store/CartStore";

export default function BarcodeScanner({ onClose }) {
  const { products } = useProducts();
  const { add } = useCart();
  const [barcode, setBarcode] = useState('');
  const [scanMode, setScanMode] = useState('manual'); // manual or camera
  const [lastScanned, setLastScanned] = useState(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const inputRef = useRef(null);

  useEffect(() => {
    if (scanMode === 'manual' && inputRef.current) {
      inputRef.current.focus();
    }
  }, [scanMode]);

  const handleScan = (code) => {
    if (!code || code.trim() === '') {
      setError('Please enter a valid barcode');
      return;
    }

    const product = products.find(p => 
      p.barcode === code || 
      p.variant_sku === code ||
      p.sku === code
    );

    if (product) {
      if (product.stock <= 0) {
        setError(`${product.name} is out of stock`);
        setSuccess('');
        setLastScanned(product);
      } else {
        add(product);
        setSuccess(`Added ${product.name} to cart!`);
        setError('');
        setLastScanned(product);
        
        // Clear success message after 2 seconds
        setTimeout(() => {
          setSuccess('');
        }, 2000);
      }
    } else {
      setError(`Product not found for barcode: ${code}`);
      setSuccess('');
      setLastScanned(null);
    }

    setBarcode('');
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    handleScan(barcode);
  };

  const handleKeyPress = (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      handleScan(barcode);
    }
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full">
        {/* Header */}
        <div className="bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
              </svg>
            </div>
            <div>
              <h2 className="text-2xl font-bold">Barcode Scanner</h2>
              <p className="text-sm text-green-100">Scan or enter product barcode</p>
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

        <div className="p-6">
          {/* Mode Toggle */}
          <div className="flex bg-gray-100 rounded-lg p-1 mb-6">
            <button
              onClick={() => setScanMode('manual')}
              className={`flex-1 px-4 py-3 rounded-md transition-all font-medium ${
                scanMode === 'manual'
                  ? 'bg-white shadow-sm text-gray-800'
                  : 'text-gray-600 hover:text-gray-800'
              }`}
            >
              <svg className="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Manual Entry
            </button>
            <button
              onClick={() => setScanMode('camera')}
              className={`flex-1 px-4 py-3 rounded-md transition-all font-medium ${
                scanMode === 'camera'
                  ? 'bg-white shadow-sm text-gray-800'
                  : 'text-gray-600 hover:text-gray-800'
              }`}
            >
              <svg className="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Camera Scan
            </button>
          </div>

          {/* Manual Entry Mode */}
          {scanMode === 'manual' && (
            <div>
              <form onSubmit={handleSubmit} className="mb-6">
                <label className="block text-sm font-semibold text-gray-700 mb-2">
                  Enter Barcode / SKU
                </label>
                <div className="flex space-x-3">
                  <input
                    ref={inputRef}
                    type="text"
                    value={barcode}
                    onChange={(e) => setBarcode(e.target.value)}
                    onKeyPress={handleKeyPress}
                    placeholder="Scan or type barcode here..."
                    className="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-green-500 focus:outline-none text-lg"
                    autoFocus
                  />
                  <button
                    type="submit"
                    className="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-all flex items-center space-x-2"
                  >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Scan</span>
                  </button>
                </div>
              </form>

              <div className="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                <p className="text-sm text-blue-800">
                  <strong>💡 Tip:</strong> Use a USB barcode scanner for faster scanning. Just scan and press Enter.
                </p>
              </div>
            </div>
          )}

          {/* Camera Mode */}
          {scanMode === 'camera' && (
            <div className="text-center py-12">
              <div className="w-32 h-32 mx-auto mb-6 bg-gray-100 rounded-2xl flex items-center justify-center">
                <svg className="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-800 mb-2">Camera Scanner</h3>
              <p className="text-gray-600 mb-6">Camera scanning feature coming soon!</p>
              <p className="text-sm text-gray-500">For now, please use manual entry mode with a USB barcode scanner for best results.</p>
            </div>
          )}

          {/* Success Message */}
          {success && (
            <div className="bg-green-50 border-2 border-green-300 rounded-xl p-4 mt-4 flex items-center space-x-3 animate-pulse">
              <div className="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <p className="text-green-800 font-semibold">{success}</p>
            </div>
          )}

          {/* Error Message */}
          {error && (
            <div className="bg-red-50 border-2 border-red-300 rounded-xl p-4 mt-4 flex items-center space-x-3">
              <div className="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
              <p className="text-red-800 font-semibold">{error}</p>
            </div>
          )}

          {/* Last Scanned Product */}
          {lastScanned && (
            <div className="mt-6 bg-gray-50 rounded-xl p-4 border-2 border-gray-200">
              <h4 className="text-sm font-semibold text-gray-600 mb-3">Last Scanned</h4>
              <div className="flex items-center justify-between">
                <div>
                  <p className="font-bold text-gray-800">{lastScanned.name}</p>
                  {lastScanned.variant_name && (
                    <p className="text-sm text-gray-500">{lastScanned.variant_name}</p>
                  )}
                  <p className="text-xs text-gray-400 mt-1">
                    SKU: {lastScanned.variant_sku || lastScanned.sku}
                  </p>
                </div>
                <div className="text-right">
                  <p className="text-2xl font-bold text-blue-600">
                    ${parseFloat(lastScanned.price).toFixed(2)}
                  </p>
                  <p className={`text-xs font-semibold mt-1 ${
                    lastScanned.stock > 0 ? 'text-green-600' : 'text-red-600'
                  }`}>
                    Stock: {lastScanned.stock}
                  </p>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end">
          <button
            onClick={onClose}
            className="px-6 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition-all"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  );
}
