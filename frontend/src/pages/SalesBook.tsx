import { useState, useEffect, useCallback } from 'react';
import toast from 'react-hot-toast';
import { orderService, salesService } from '../services';
import type { Order, Sale, InventoryStatus } from '../types';

export function SalesBook() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [sales, setSales] = useState<Sale[]>([]);
  const [loading, setLoading] = useState(true);
  const [escalating, setEscalating] = useState(false);

  // Escalation form state
  const [selectedOrderId, setSelectedOrderId] = useState<string>('');
  const [salesDate, setSalesDate] = useState(new Date().toISOString().split('T')[0]);
  const [dispatchedQty, setDispatchedQty] = useState<string>('');
  const [materialCheck, setMaterialCheck] = useState<InventoryStatus[] | null>(null);
  const [materialCheckLoading, setMaterialCheckLoading] = useState(false);

  const loadData = useCallback(async () => {
    try {
      setLoading(true);
      const [ordersRes, salesRes] = await Promise.all([
        orderService.getAll('pending,confirmed,in_production,ready'),
        salesService.getAll(),
      ]);
      setOrders(ordersRes.orders || []);
      setSales(salesRes.sales || []);
    } catch {
      toast.error('Failed to load data');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadData();
  }, [loadData]);

  const selectedOrder = orders.find(o => o.id === parseInt(selectedOrderId));

  // When order is selected, auto-fill dispatched qty and check material
  const handleOrderSelect = async (orderId: string) => {
    setSelectedOrderId(orderId);
    setMaterialCheck(null);

    if (orderId) {
      const order = orders.find(o => o.id === parseInt(orderId));
      if (order) {
        setDispatchedQty(String(order.qty));

        // Check material competency
        setMaterialCheckLoading(true);
        try {
          const res = await orderService.calculateOrderMaterial(parseInt(orderId));
          setMaterialCheck(res.inventory_status || []);
        } catch {
          toast.error('Failed to check material availability');
        } finally {
          setMaterialCheckLoading(false);
        }
      }
    } else {
      setDispatchedQty('');
    }
  };

  const handleEscalate = async () => {
    if (!selectedOrderId || !salesDate || !dispatchedQty) {
      toast.error('Please select an order, date, and quantity');
      return;
    }

    setEscalating(true);
    try {
      const res = await salesService.escalateOrder({
        order_id: parseInt(selectedOrderId),
        sales_date: salesDate,
        dispatched_qty: parseFloat(dispatchedQty),
      });

      toast.success(res.message || 'Order escalated to sales!');

      // Reset form
      setSelectedOrderId('');
      setDispatchedQty('');
      setSalesDate(new Date().toISOString().split('T')[0]);
      setMaterialCheck(null);
      loadData();
    } catch (err: any) {
      const errData = err?.response?.data;
      if (errData?.inventory_status) {
        setMaterialCheck(errData.inventory_status);
        toast.error(errData.message || 'Insufficient raw materials');
      } else {
        toast.error(errData?.message || 'Failed to escalate order');
      }
    } finally {
      setEscalating(false);
    }
  };

  const allCompetent = materialCheck
    ? materialCheck.every(s => s.difference >= 0)
    : false;

  const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    in_production: 'bg-purple-100 text-purple-800',
    ready: 'bg-green-100 text-green-800',
    delivered: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800',
  };

  const paymentColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    partial: 'bg-orange-100 text-orange-800',
    paid: 'bg-green-100 text-green-800',
  };

  if (loading) {
    return (
      <div className="p-6 flex items-center justify-center">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="p-4 lg:p-6 space-y-6">
      <h1 className="text-xl lg:text-2xl font-bold text-gray-900">Sales Book</h1>

      {/* ── Escalate Order to Sale ──────────────────────────────── */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:p-6">
        <h3 className="text-base lg:text-lg font-semibold text-gray-800 mb-4">Escalate Order to Sales</h3>

        {orders.length === 0 ? (
          <p className="text-gray-500 text-sm py-4">No pending orders available to escalate.</p>
        ) : (
          <>
            {/* Order Ticket Selector */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
              <div>
                <label className="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Select Order Ticket</label>
                <select value={selectedOrderId} onChange={e => handleOrderSelect(e.target.value)}
                  className="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                  <option value="">Select Order</option>
                  {orders.map(o => (
                    <option key={o.id} value={o.id}>
                      #{o.id} — {o.product?.product_name || 'Product'} ({o.qty} units) — {o.customer?.billing_name || o.customer?.name || 'Walk-in'}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Sales Date</label>
                <input type="date" value={salesDate} onChange={e => setSalesDate(e.target.value)}
                  className="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" />
              </div>
              <div>
                <label className="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Dispatch Qty</label>
                <input type="number" min="1" max={selectedOrder?.qty || undefined} value={dispatchedQty}
                  onChange={e => setDispatchedQty(e.target.value)} placeholder="Qty to dispatch"
                  className="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" />
                {selectedOrder && parseFloat(dispatchedQty) < selectedOrder.qty && parseFloat(dispatchedQty) > 0 && (
                  <p className="text-xs text-amber-600 mt-1">
                    ⚡ Partial dispatch: {selectedOrder.qty - parseFloat(dispatchedQty)} units will remain as a new sub-order
                  </p>
                )}
              </div>
              <div className="flex items-end">
                <button onClick={handleEscalate} disabled={escalating || !selectedOrderId || !dispatchedQty}
                  className="w-full bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm disabled:opacity-50">
                  {escalating ? 'Processing...' : 'Escalate to Sales'}
                </button>
              </div>
            </div>

            {/* Selected order details */}
            {selectedOrder && (
              <div className="bg-gray-50 rounded-lg p-4 mb-4">
                <h4 className="text-sm font-medium text-gray-700 mb-2">Order Details</h4>
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                  <div>
                    <span className="text-gray-500">Product:</span>
                    <span className="ml-1 font-medium">{selectedOrder.product?.product_name}</span>
                  </div>
                  <div>
                    <span className="text-gray-500">Qty:</span>
                    <span className="ml-1 font-medium">{selectedOrder.qty}</span>
                  </div>
                  <div>
                    <span className="text-gray-500">Customer:</span>
                    <span className="ml-1 font-medium">{selectedOrder.customer?.billing_name || selectedOrder.customer?.name || 'Walk-in'}</span>
                  </div>
                  <div>
                    <span className="text-gray-500">Status:</span>
                    <span className={`ml-1 inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${statusColors[selectedOrder.status]}`}>
                      {selectedOrder.status.replace('_', ' ')}
                    </span>
                  </div>
                </div>
              </div>
            )}

            {/* Material Competency Check */}
            {materialCheckLoading && (
              <div className="flex items-center gap-2 text-sm text-gray-500 mb-4">
                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                Checking material availability...
              </div>
            )}

            {materialCheck && materialCheck.length > 0 && (
              <div className={`border rounded-lg p-4 mb-4 ${allCompetent ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50'}`}>
                <h4 className={`text-sm font-semibold mb-3 ${allCompetent ? 'text-green-800' : 'text-red-800'}`}>
                  {allCompetent ? '✅ All materials available — ready to dispatch' : '❌ Insufficient materials'}
                </h4>
                <div className="overflow-x-auto">
                  <table className="min-w-full table-auto border-collapse border border-gray-300 text-sm">
                    <thead>
                      <tr className="bg-white/50">
                        <th className="border border-gray-300 px-3 py-1.5 text-left text-xs font-medium text-gray-700">Material</th>
                        <th className="border border-gray-300 px-3 py-1.5 text-left text-xs font-medium text-gray-700">Required</th>
                        <th className="border border-gray-300 px-3 py-1.5 text-left text-xs font-medium text-gray-700">Available</th>
                        <th className="border border-gray-300 px-3 py-1.5 text-left text-xs font-medium text-gray-700">Difference</th>
                        <th className="border border-gray-300 px-3 py-1.5 text-left text-xs font-medium text-gray-700">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {materialCheck.map((s, i) => (
                        <tr key={i}>
                          <td className="border border-gray-300 px-3 py-1.5">{s.material}</td>
                          <td className="border border-gray-300 px-3 py-1.5">{s.requiredQty} {s.unit}</td>
                          <td className="border border-gray-300 px-3 py-1.5">{s.availableQty} {s.unit}</td>
                          <td className="border border-gray-300 px-3 py-1.5 font-medium">{s.difference}</td>
                          <td className="border border-gray-300 px-3 py-1.5">
                            {s.difference >= 0 ? (
                              <span className="text-green-700 font-medium text-xs">✅ Competent</span>
                            ) : (
                              <span className="text-red-700 font-medium text-xs">❌ Need {Math.abs(s.difference)} more</span>
                            )}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
          </>
        )}
      </div>

      {/* ── Submitted Sales ─────────────────────────────────────── */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:p-6">
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
          <h3 className="text-base lg:text-lg font-semibold text-gray-800">Submitted Sales</h3>
          <span className="text-sm text-gray-500">{sales.length} sale(s)</span>
        </div>

        {sales.length === 0 ? (
          <p className="text-gray-500 text-sm py-4 text-center">No sales recorded yet.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full table-auto border-collapse border border-gray-300">
              <thead>
                <tr className="bg-gray-50">
                  <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Sale Date</th>
                  <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Product</th>
                  <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Qty</th>
                  <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Customer</th>
                  <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Total</th>
                  <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Payment</th>
                  <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Order #</th>
                </tr>
              </thead>
              <tbody>
                {sales.map(sale => (
                  <tr key={sale.id} className="hover:bg-gray-50">
                    <td className="border border-gray-300 px-3 py-2 text-sm">{sale.sale_date?.split('T')[0]}</td>
                    <td className="border border-gray-300 px-3 py-2 text-sm">{sale.product?.product_name || '—'}</td>
                    <td className="border border-gray-300 px-3 py-2 text-sm font-medium">{sale.qty}</td>
                    <td className="border border-gray-300 px-3 py-2 text-sm">{sale.customer?.billing_name || sale.customer?.name || '—'}</td>
                    <td className="border border-gray-300 px-3 py-2 text-sm">₹{parseFloat(String(sale.total_amount)).toFixed(2)}</td>
                    <td className="border border-gray-300 px-3 py-2">
                      <span className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${paymentColors[sale.payment_status] || 'bg-gray-100'}`}>
                        {sale.payment_status}
                      </span>
                    </td>
                    <td className="border border-gray-300 px-3 py-2 text-sm text-gray-500">
                      {sale.order_id ? `#${sale.order_id}` : '—'}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
