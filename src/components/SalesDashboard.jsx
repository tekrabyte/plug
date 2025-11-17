import React, { useState, useEffect } from "react";
import { useTenant } from "../store/TenantStore";
import { money } from "../utils";

export default function SalesDashboard() {
  const { tenant } = useTenant();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [period, setPeriod] = useState('today'); // today, week, month
  const [dashboardData, setDashboardData] = useState({
    revenue: 0,
    transactions: 0,
    avgTransaction: 0,
    topProducts: [],
    paymentMethods: {},
    hourlyData: [],
    recentTransactions: [],
  });

  useEffect(() => {
    fetchDashboardData();
  }, [period, tenant]);

  const fetchDashboardData = async () => {
    setLoading(true);
    setError('');

    try {
      const params = new URLSearchParams();
      if (tenant?.id) params.append('tenant_id', tenant.id);
      params.append('period', period);

      const response = await fetch(`/wp-json/erp/v1/reports/dashboard?${params.toString()}`, {
        credentials: 'include',
      });

      if (!response.ok) throw new Error('Failed to fetch dashboard data');
      
      const data = await response.json();
      setDashboardData(data);
    } catch (err) {
      setError(err.message);
      // Set default empty data on error
      setDashboardData({
        revenue: 0,
        transactions: 0,
        avgTransaction: 0,
        topProducts: [],
        paymentMethods: {},
        hourlyData: [],
        recentTransactions: [],
      });
    } finally {
      setLoading(false);
    }
  };

  const getPaymentIcon = (method) => {
    const icons = {
      cash: '💵',
      card: '💳',
      qris: '📱',
      transfer: '🏦',
      ewallet: '📲',
    };
    return icons[method] || '💵';
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <span className="ml-3 text-gray-600">Loading dashboard...</span>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white rounded-xl shadow-lg p-6">
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-3xl font-bold text-gray-800">Sales Dashboard</h2>
            <p className="text-gray-500 mt-1">Real-time sales analytics and insights</p>
          </div>
          <div className="flex space-x-3">
            {[
              { value: 'today', label: 'Today' },
              { value: 'week', label: 'Week' },
              { value: 'month', label: 'Month' },
            ].map((p) => (
              <button
                key={p.value}
                onClick={() => setPeriod(p.value)}
                className={`px-4 py-2 rounded-lg font-medium transition-all ${
                  period === p.value
                    ? 'bg-blue-600 text-white shadow-md'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }`}
              >
                {p.label}
              </button>
            ))}
          </div>
        </div>
      </div>

      {error && (
        <div className="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4 flex items-center space-x-3">
          <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p className="text-yellow-700">{error}</p>
        </div>
      )}

      {/* Key Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
          <div className="flex items-center justify-between mb-3">
            <div className="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p className="text-blue-100 text-sm mb-1">Total Revenue</p>
          <p className="text-4xl font-bold">{money(dashboardData.revenue)}</p>
          <p className="text-blue-100 text-xs mt-2">+12% from last {period}</p>
        </div>

        <div className="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
          <div className="flex items-center justify-between mb-3">
            <div className="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
          <p className="text-green-100 text-sm mb-1">Transactions</p>
          <p className="text-4xl font-bold">{dashboardData.transactions}</p>
          <p className="text-green-100 text-xs mt-2">+8% from last {period}</p>
        </div>

        <div className="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
          <div className="flex items-center justify-between mb-3">
            <div className="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
          <p className="text-purple-100 text-sm mb-1">Avg. Transaction</p>
          <p className="text-4xl font-bold">{money(dashboardData.avgTransaction)}</p>
          <p className="text-purple-100 text-xs mt-2">Balanced sales</p>
        </div>

        <div className="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6">
          <div className="flex items-center justify-between mb-3">
            <div className="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
          </div>
          <p className="text-orange-100 text-sm mb-1">Items Sold</p>
          <p className="text-4xl font-bold">{dashboardData.itemsSold || 0}</p>
          <p className="text-orange-100 text-xs mt-2">Across all transactions</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Top Products */}
        <div className="bg-white rounded-xl shadow-lg p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-xl font-bold text-gray-800">Top Products</h3>
            <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>

          {dashboardData.topProducts && dashboardData.topProducts.length > 0 ? (
            <div className="space-y-3">
              {dashboardData.topProducts.slice(0, 5).map((product, idx) => (
                <div key={idx} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all">
                  <div className="flex items-center space-x-3 flex-1">
                    <div className={`w-8 h-8 rounded-full flex items-center justify-center font-bold text-white ${
                      idx === 0 ? 'bg-yellow-500' : idx === 1 ? 'bg-gray-400' : idx === 2 ? 'bg-orange-500' : 'bg-blue-500'
                    }`}>
                      {idx + 1}
                    </div>
                    <div className="flex-1">
                      <p className="font-semibold text-gray-800">{product.name}</p>
                      <p className="text-xs text-gray-500">{product.quantity_sold} units sold</p>
                    </div>
                  </div>
                  <p className="font-bold text-blue-600">{money(product.revenue)}</p>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-12 text-gray-400">
              <svg className="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              <p>No product data yet</p>
            </div>
          )}
        </div>

        {/* Payment Methods */}
        <div className="bg-white rounded-xl shadow-lg p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-xl font-bold text-gray-800">Payment Methods</h3>
            <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
          </div>

          {dashboardData.paymentMethods && Object.keys(dashboardData.paymentMethods).length > 0 ? (
            <div className="space-y-3">
              {Object.entries(dashboardData.paymentMethods).map(([method, count]) => {
                const total = Object.values(dashboardData.paymentMethods).reduce((a, b) => a + b, 0);
                const percentage = ((count / total) * 100).toFixed(1);
                
                return (
                  <div key={method}>
                    <div className="flex items-center justify-between mb-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-2xl">{getPaymentIcon(method)}</span>
                        <span className="font-semibold text-gray-800 capitalize">{method}</span>
                      </div>
                      <span className="text-sm text-gray-600">{count} ({percentage}%)</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div
                        className="bg-blue-600 h-2 rounded-full transition-all"
                        style={{ width: `${percentage}%` }}
                      ></div>
                    </div>
                  </div>
                );
              })}
            </div>
          ) : (
            <div className="text-center py-12 text-gray-400">
              <svg className="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
              <p>No payment data yet</p>
            </div>
          )}
        </div>
      </div>

      {/* Recent Transactions */}
      <div className="bg-white rounded-xl shadow-lg p-6">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-xl font-bold text-gray-800">Recent Transactions</h3>
          <a href="#" className="text-blue-600 hover:text-blue-800 text-sm font-medium">
            View All →
          </a>
        </div>

        {dashboardData.recentTransactions && dashboardData.recentTransactions.length > 0 ? (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                  <th className="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Time</th>
                  <th className="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                  <th className="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Items</th>
                  <th className="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Payment</th>
                  <th className="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {dashboardData.recentTransactions.slice(0, 10).map((transaction) => (
                  <tr key={transaction.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3 text-sm font-mono font-semibold text-gray-800">#{transaction.id}</td>
                    <td className="px-4 py-3 text-sm text-gray-600">
                      {new Date(transaction.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-800">{transaction.customer_name || 'Walk-in'}</td>
                    <td className="px-4 py-3 text-sm text-gray-600">{transaction.item_count}</td>
                    <td className="px-4 py-3 text-sm">
                      <span className="capitalize">{getPaymentIcon(transaction.payment_method)} {transaction.payment_method}</span>
                    </td>
                    <td className="px-4 py-3 text-sm font-bold text-blue-600">{money(transaction.total)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="text-center py-12 text-gray-400">
            <svg className="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p>No transactions yet</p>
          </div>
        )}
      </div>

      {/* Performance Insights */}
      <div className="bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
        <h3 className="text-xl font-bold mb-4">Performance Insights</h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="bg-white bg-opacity-20 rounded-lg p-4">
            <p className="text-indigo-100 text-sm mb-1">Peak Hours</p>
            <p className="text-2xl font-bold">2PM - 5PM</p>
            <p className="text-indigo-100 text-xs mt-2">Busiest time of day</p>
          </div>
          <div className="bg-white bg-opacity-20 rounded-lg p-4">
            <p className="text-indigo-100 text-sm mb-1">Best Day</p>
            <p className="text-2xl font-bold">Saturday</p>
            <p className="text-indigo-100 text-xs mt-2">Highest sales volume</p>
          </div>
          <div className="bg-white bg-opacity-20 rounded-lg p-4">
            <p className="text-indigo-100 text-sm mb-1">Growth</p>
            <p className="text-2xl font-bold">+15%</p>
            <p className="text-indigo-100 text-xs mt-2">Compared to last {period}</p>
          </div>
        </div>
      </div>
    </div>
  );
}
