import React, { useState, useEffect } from "react";
import { useTenant } from "../store/TenantStore";
import { money } from "../utils";

export default function TransactionHistory() {
  const { tenant } = useTenant();
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [filterPeriod, setFilterPeriod] = useState('today'); // today, week, month, all
  const [selectedTransaction, setSelectedTransaction] = useState(null);

  useEffect(() => {
    fetchTransactions();
  }, [filterPeriod, tenant]);

  const fetchTransactions = async () => {
    setLoading(true);
    setError('');

    try {
      const params = new URLSearchParams();
      if (tenant?.id) params.append('tenant_id', tenant.id);
      if (filterPeriod !== 'all') params.append('period', filterPeriod);

      const response = await fetch(`/wp-json/erp/v1/transactions?${params.toString()}`, {
        credentials: 'include',
      });

      if (!response.ok) throw new Error('Failed to fetch transactions');
      
      const data = await response.json();
      setTransactions(data.transactions || []);
    } catch (err) {
      setError(err.message);
      setTransactions([]);
    } finally {
      setLoading(false);
    }
  };

  const getPaymentMethodIcon = (method) => {
    const icons = {
      cash: '💵',
      card: '💳',
      qris: '📱',
      transfer: '🏦',
      ewallet: '📲',
    };
    return icons[method] || '💵';
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(date);
  };

  const handlePrintReceipt = (transactionId) => {
    window.open(`/wp-json/erp/v1/receipt/${transactionId}`, '_blank');
  };

  const calculateStats = () => {
    const totalRevenue = transactions.reduce((sum, t) => sum + parseFloat(t.total || 0), 0);
    const totalTransactions = transactions.length;
    const avgTransaction = totalTransactions > 0 ? totalRevenue / totalTransactions : 0;

    const paymentBreakdown = transactions.reduce((acc, t) => {
      acc[t.payment_method] = (acc[t.payment_method] || 0) + 1;
      return acc;
    }, {});

    return { totalRevenue, totalTransactions, avgTransaction, paymentBreakdown };
  };

  const stats = calculateStats();

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <span className="ml-3 text-gray-600">Loading transactions...</span>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header & Stats */}
      <div className="bg-white rounded-xl shadow-lg p-6">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h2 className="text-3xl font-bold text-gray-800">Transaction History</h2>
            <p className="text-gray-500 mt-1">View and manage all transactions</p>
          </div>
          <button
            onClick={fetchTransactions}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all flex items-center space-x-2"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Refresh</span>
          </button>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-blue-100 text-sm mb-1">Total Revenue</p>
                <p className="text-3xl font-bold">{money(stats.totalRevenue)}</p>
              </div>
              <div className="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div className="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-green-100 text-sm mb-1">Transactions</p>
                <p className="text-3xl font-bold">{stats.totalTransactions}</p>
              </div>
              <div className="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
            </div>
          </div>

          <div className="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-purple-100 text-sm mb-1">Avg. Transaction</p>
                <p className="text-3xl font-bold">{money(stats.avgTransaction)}</p>
              </div>
              <div className="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="bg-white rounded-xl shadow-lg p-4">
        <div className="flex items-center space-x-3">
          <span className="text-gray-700 font-semibold">Filter:</span>
          {[
            { value: 'today', label: 'Today' },
            { value: 'week', label: 'This Week' },
            { value: 'month', label: 'This Month' },
            { value: 'all', label: 'All Time' },
          ].map((period) => (
            <button
              key={period.value}
              onClick={() => setFilterPeriod(period.value)}
              className={`px-4 py-2 rounded-lg font-medium transition-all ${
                filterPeriod === period.value
                  ? 'bg-blue-600 text-white shadow-md'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              }`}
            >
              {period.label}
            </button>
          ))}
        </div>
      </div>

      {/* Error Message */}
      {error && (
        <div className="bg-red-50 border-2 border-red-200 rounded-xl p-4 flex items-center space-x-3">
          <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p className="text-red-700">{error}</p>
        </div>
      )}

      {/* Transactions List */}
      <div className="bg-white rounded-xl shadow-lg overflow-hidden">
        {transactions.length === 0 ? (
          <div className="text-center py-16">
            <svg className="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p className="text-gray-500 text-lg">No transactions found</p>
            <p className="text-gray-400 text-sm mt-2">Transactions will appear here once sales are made</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                  <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    ID
                  </th>
                  <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Date & Time
                  </th>
                  <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Customer
                  </th>
                  <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Items
                  </th>
                  <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Payment
                  </th>
                  <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Total
                  </th>
                  <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {transactions.map((transaction) => (
                  <tr
                    key={transaction.id}
                    className="hover:bg-gray-50 transition-colors cursor-pointer"
                    onClick={() => setSelectedTransaction(transaction)}
                  >
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="text-sm font-mono font-semibold text-gray-800">
                        #{transaction.id}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="text-sm text-gray-600">{formatDate(transaction.created_at)}</span>
                    </td>
                    <td className="px-6 py-4">
                      <div className="text-sm">
                        <p className="font-medium text-gray-800">
                          {transaction.customer_name || 'Walk-in Customer'}
                        </p>
                        {transaction.customer_phone && (
                          <p className="text-gray-500 text-xs">{transaction.customer_phone}</p>
                        )}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="text-sm text-gray-600">{transaction.item_count || 0} items</span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        <span className="mr-1">{getPaymentMethodIcon(transaction.payment_method)}</span>
                        {transaction.payment_method}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="text-lg font-bold text-blue-600">{money(transaction.total)}</span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          handlePrintReceipt(transaction.id);
                        }}
                        className="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center space-x-1"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Print</span>
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Transaction Detail Modal */}
      {selectedTransaction && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
              <div>
                <h3 className="text-2xl font-bold">Transaction Details</h3>
                <p className="text-sm text-blue-100">#{selectedTransaction.id}</p>
              </div>
              <button
                onClick={() => setSelectedTransaction(null)}
                className="w-8 h-8 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-all flex items-center justify-center"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div className="p-6 space-y-6">
              {/* Transaction Info */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Date & Time</p>
                  <p className="font-semibold text-gray-800">{formatDate(selectedTransaction.created_at)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Payment Method</p>
                  <p className="font-semibold text-gray-800">
                    {getPaymentMethodIcon(selectedTransaction.payment_method)} {selectedTransaction.payment_method}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Customer</p>
                  <p className="font-semibold text-gray-800">{selectedTransaction.customer_name || 'Walk-in'}</p>
                  {selectedTransaction.customer_phone && (
                    <p className="text-sm text-gray-500">{selectedTransaction.customer_phone}</p>
                  )}
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Cashier</p>
                  <p className="font-semibold text-gray-800">{selectedTransaction.cashier_name || 'N/A'}</p>
                </div>
              </div>

              {/* Items */}
              <div>
                <h4 className="font-bold text-gray-800 mb-3">Items</h4>
                <div className="bg-gray-50 rounded-xl p-4 space-y-2">
                  {selectedTransaction.items && selectedTransaction.items.map((item, idx) => (
                    <div key={idx} className="flex justify-between items-center py-2 border-b border-gray-200 last:border-0">
                      <div>
                        <p className="font-medium text-gray-800">{item.name}</p>
                        <p className="text-sm text-gray-500">x{item.quantity}</p>
                      </div>
                      <p className="font-semibold text-gray-800">{money(item.subtotal || item.price * item.quantity)}</p>
                    </div>
                  ))}
                </div>
              </div>

              {/* Totals */}
              <div className="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 space-y-2">
                <div className="flex justify-between text-gray-700">
                  <span>Subtotal</span>
                  <span className="font-semibold">{money(selectedTransaction.subtotal || 0)}</span>
                </div>
                <div className="flex justify-between text-gray-700">
                  <span>Tax</span>
                  <span className="font-semibold">{money(selectedTransaction.tax || 0)}</span>
                </div>
                <div className="flex justify-between text-xl font-bold text-gray-800 pt-2 border-t-2 border-blue-300">
                  <span>Total</span>
                  <span className="text-blue-600">{money(selectedTransaction.total)}</span>
                </div>
              </div>

              {selectedTransaction.notes && (
                <div>
                  <p className="text-sm text-gray-600 mb-1">Notes</p>
                  <p className="text-gray-800 bg-gray-50 rounded-lg p-3">{selectedTransaction.notes}</p>
                </div>
              )}

              <button
                onClick={() => handlePrintReceipt(selectedTransaction.id)}
                className="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-all flex items-center justify-center space-x-2"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Receipt</span>
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
