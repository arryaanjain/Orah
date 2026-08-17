import { useState, useEffect, useCallback } from 'react';
import toast from 'react-hot-toast';
import { orderService, productService, customerService } from '../services';
import type { Order, FinishedProduct, Customer, InventoryStatus, ProductCalculation } from '../types';
import { RefreshCw, CheckCircle2, AlertTriangle, Calculator, Plus, Trash2, Eye, X, PackageCheck } from 'lucide-react';

interface SingleOrderCalculation {
  order_id: number;
  product: string;
  order_qty: number;
  inventory_status: InventoryStatus[];
}

export function OrderBook() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [products, setProducts] = useState<FinishedProduct[]>([]);
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [calculationResult, setCalculationResult] = useState<Record<string, ProductCalculation> | null>(null);
  const [calculationLoading, setCalculationLoading] = useState(false);

  // Single Order Competency Modal state
  const [selectedOrderCalc, setSelectedOrderCalc] = useState<SingleOrderCalculation | null>(null);
  const [orderCalcLoading, setOrderCalcLoading] = useState(false);

  // Form state for new order rows
  const [orderRows, setOrderRows] = useState([
    {
      id: Date.now(),
      product_id: '',
      customer_id: '',
      qty: '',
      order_date: new Date().toISOString().split('T')[0],
    },
  ]);

  const runCalculation = async () => {
    setCalculationLoading(true);
    try {
      const res = await orderService.calculateMaterialRequirement();
      setCalculationResult(res.products || {});
    } catch {
      toast.error('Failed to calculate material requirements');
    } finally {
      setCalculationLoading(false);
    }
  };

  const checkSingleOrderCompetency = async (orderId: number) => {
    setOrderCalcLoading(true);
    try {
      const res = await orderService.calculateOrderMaterial(orderId);
      setSelectedOrderCalc(res);
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'Failed to check order material competency');
    } finally {
      setOrderCalcLoading(false);
    }
  };

  const loadData = useCallback(async () => {
    try {
      setLoading(true);
      const [ordersRes, productsRes, customersRes] = await Promise.all([
        orderService.getAll('pending,confirmed,in_production,ready'),
        productService.getAll(),
        customerService.getAll(),
      ]);

      const fetchedOrders = ordersRes.orders || [];
      setOrders(fetchedOrders);
      setProducts(productsRes.products || []);
      setCustomers(customersRes.customers || []);

      // Auto-trigger product-wide competency calculation by default
      if (fetchedOrders.length > 0) {
        const calcRes = await orderService.calculateMaterialRequirement();
        setCalculationResult(calcRes.products || {});
      } else {
        setCalculationResult({});
      }
    } catch {
      toast.error('Failed to load order data');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadData();
  }, [loadData]);

  const addRow = () => {
    setOrderRows((prev) => [
      ...prev,
      {
        id: Date.now(),
        product_id: '',
        customer_id: '',
        qty: '',
        order_date: new Date().toISOString().split('T')[0],
      },
    ]);
  };

  const removeRow = (id: number) => {
    if (orderRows.length > 1) {
      setOrderRows((prev) => prev.filter((r) => r.id !== id));
    }
  };

  const updateRow = (id: number, field: string, value: string) => {
    setOrderRows((prev) => prev.map((r) => (r.id === id ? { ...r, [field]: value } : r)));
  };

  const handleSubmit = async () => {
    const validRows = orderRows.filter((r) => r.product_id && r.qty && r.order_date);
    if (validRows.length === 0) {
      toast.error('Please fill in at least one order row');
      return;
    }

    setSubmitting(true);
    try {
      const ordersData = validRows.map((r) => ({
        product_id: parseInt(r.product_id),
        customer_id: r.customer_id ? parseInt(r.customer_id) : undefined,
        qty: parseFloat(r.qty),
        order_date: r.order_date,
      }));

      if (ordersData.length === 1) {
        await orderService.create(ordersData[0]);
      } else {
        await orderService.batchCreate(ordersData);
      }

      toast.success('Order(s) created successfully!');
      setOrderRows([
        {
          id: Date.now(),
          product_id: '',
          customer_id: '',
          qty: '',
          order_date: new Date().toISOString().split('T')[0],
        },
      ]);
      loadData();
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'Failed to create order');
    } finally {
      setSubmitting(false);
    }
  };

  const getProductName = (id: number) => products.find((p) => p.id === id)?.product_name || '—';
  const getCustomerName = (c?: Customer) => c?.billing_name || c?.name || '—';

  const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    in_production: 'bg-purple-100 text-purple-800',
    ready: 'bg-green-100 text-green-800',
    delivered: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800',
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
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-xl lg:text-2xl font-bold text-gray-900">Order Book</h1>
          <p className="text-sm text-gray-600 mt-0.5">Manage customer orders and real-time inventory competency (Product-wise & Order-wise)</p>
        </div>
        <button
          onClick={runCalculation}
          disabled={calculationLoading}
          className="flex items-center gap-2 bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
        >
          <RefreshCw className={`h-3.5 w-3.5 ${calculationLoading ? 'animate-spin' : ''}`} />
          Recalculate Product Competency
        </button>
      </div>

      {/* ── Product-Wide Material Competency Calculation ──────────────────── */}
      <div className="bg-gradient-to-r from-purple-50 via-indigo-50 to-blue-50 border-2 border-purple-200 rounded-xl p-4 lg:p-6 shadow-sm">
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4">
          <div className="flex items-center gap-2">
            <Calculator className="h-6 w-6 text-purple-700" />
            <div>
              <h2 className="text-lg font-bold text-gray-900">Overall Inventory Competency (By Product)</h2>
              <p className="text-xs text-gray-600">Total material required vs available raw materials across all pending orders</p>
            </div>
          </div>
          <span className="px-2.5 py-1 bg-purple-600 text-white text-xs font-medium rounded-full">
            Auto-Updated
          </span>
        </div>

        {calculationLoading ? (
          <div className="p-8 text-center">
            <div className="animate-spin h-6 w-6 border-2 border-purple-600 border-t-transparent rounded-full mx-auto mb-2"></div>
            <p className="text-xs text-purple-800">Calculating material competency against BOM recipes...</p>
          </div>
        ) : calculationResult && Object.keys(calculationResult).length > 0 ? (
          <div className="space-y-4">
            {Object.entries(calculationResult).map(([productName, data]) => {
              const allCompetent = data.inventory_status.every((s) => s.difference >= 0);

              return (
                <div key={productName} className="bg-white rounded-lg border border-purple-200 p-4 shadow-sm">
                  <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                    <div>
                      <h3 className="text-base font-bold text-gray-900">
                        Product: <span className="text-purple-700">{productName}</span>
                      </h3>
                      <p className="text-xs text-gray-500">
                        {data.order_count} active order(s) requiring a total of <span className="font-semibold text-gray-800">{data.total_ordered_qty} units</span>
                      </p>
                    </div>
                    <div>
                      {allCompetent ? (
                        <span className="inline-flex items-center gap-1.5 bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full font-semibold">
                          <CheckCircle2 className="h-4 w-4 text-green-600" /> Fully Competent
                        </span>
                      ) : (
                        <span className="inline-flex items-center gap-1.5 bg-red-100 text-red-800 text-xs px-3 py-1 rounded-full font-semibold">
                          <AlertTriangle className="h-4 w-4 text-red-600" /> Stock Shortage
                        </span>
                      )}
                    </div>
                  </div>

                  <div className="overflow-x-auto">
                    <table className="min-w-full table-auto border-collapse text-xs">
                      <thead>
                        <tr className="bg-purple-50 text-purple-900">
                          <th className="px-3 py-2 text-left font-semibold">Required Raw Material</th>
                          <th className="px-3 py-2 text-left font-semibold">Total Required Qty</th>
                          <th className="px-3 py-2 text-left font-semibold">Current Available Stock</th>
                          <th className="px-3 py-2 text-left font-semibold">Surplus / Deficit</th>
                          <th className="px-3 py-2 text-left font-semibold">Status</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-gray-100">
                        {data.inventory_status.map((s: InventoryStatus, i: number) => (
                          <tr key={i} className="hover:bg-gray-50">
                            <td className="px-3 py-2 font-medium text-gray-900">{s.material}</td>
                            <td className="px-3 py-2 font-semibold text-purple-700">
                              {s.requiredQty} {s.unit}
                            </td>
                            <td className="px-3 py-2 text-gray-700">
                              {s.availableQty} {s.unit}
                            </td>
                            <td className="px-3 py-2 font-bold">
                              {s.difference >= 0 ? (
                                <span className="text-green-600">+{s.difference} {s.unit}</span>
                              ) : (
                                <span className="text-red-600">{s.difference} {s.unit}</span>
                              )}
                            </td>
                            <td className="px-3 py-2">
                              {s.difference >= 0 ? (
                                <span className="inline-flex items-center text-green-700 font-semibold gap-1">
                                  <CheckCircle2 className="h-3.5 w-3.5" /> Ready
                                </span>
                              ) : (
                                <span className="inline-flex items-center text-red-700 font-semibold gap-1">
                                  <AlertTriangle className="h-3.5 w-3.5" /> Need {Math.abs(s.difference)} {s.unit}
                                </span>
                              )}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <div className="bg-white rounded-lg p-6 text-center text-gray-500 text-sm border border-purple-100">
            No active pending orders requiring raw material calculation at this time.
          </div>
        )}
      </div>

      {/* ── New Order Entry ─────────────────────────────────────── */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:p-6">
        <h2 className="text-base lg:text-lg font-semibold text-gray-800 mb-4">New Order Entry</h2>

        {/* Mobile cards */}
        <div className="block sm:hidden space-y-4">
          {orderRows.map((row, idx) => (
            <div key={row.id} className="border border-gray-200 rounded-lg p-4 bg-gray-50">
              <div className="flex justify-between items-start mb-3">
                <span className="text-sm font-medium text-gray-700">Order #{idx + 1}</span>
                {orderRows.length > 1 && (
                  <button onClick={() => removeRow(row.id)} className="text-red-600 hover:text-red-800 text-sm">
                    Remove
                  </button>
                )}
              </div>
              <div className="space-y-3">
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">Order Date</label>
                  <input
                    type="date"
                    value={row.order_date}
                    onChange={(e) => updateRow(row.id, 'order_date', e.target.value)}
                    className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 text-sm"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">Finished Product</label>
                  <select
                    value={row.product_id}
                    onChange={(e) => updateRow(row.id, 'product_id', e.target.value)}
                    className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 text-sm"
                  >
                    <option value="">Select Product</option>
                    {products.filter((p) => p.is_active).map((p) => (
                      <option key={p.id} value={p.id}>
                        {p.product_name}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                  <input
                    type="number"
                    min="1"
                    value={row.qty}
                    onChange={(e) => updateRow(row.id, 'qty', e.target.value)}
                    placeholder="Quantity"
                    className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 text-sm"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">Customer</label>
                  <select
                    value={row.customer_id}
                    onChange={(e) => updateRow(row.id, 'customer_id', e.target.value)}
                    className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 text-sm"
                  >
                    <option value="">Select Customer (optional)</option>
                    {customers.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.billing_name || c.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Desktop table */}
        <div className="hidden sm:block overflow-x-auto">
          <table className="min-w-full table-auto border-collapse border border-gray-300">
            <thead>
              <tr className="bg-gray-50">
                <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Order Date</th>
                <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Finished Product</th>
                <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Quantity</th>
                <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Customer</th>
                <th className="border border-gray-300 px-3 py-2 text-left text-xs lg:text-sm font-medium text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody>
              {orderRows.map((row) => (
                <tr key={row.id}>
                  <td className="border border-gray-300 px-3 py-2">
                    <input
                      type="date"
                      value={row.order_date}
                      onChange={(e) => updateRow(row.id, 'order_date', e.target.value)}
                      className="w-full p-1.5 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 text-sm"
                    />
                  </td>
                  <td className="border border-gray-300 px-3 py-2">
                    <select
                      value={row.product_id}
                      onChange={(e) => updateRow(row.id, 'product_id', e.target.value)}
                      className="w-full p-1.5 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                      <option value="">Select Product</option>
                      {products.filter((p) => p.is_active).map((p) => (
                        <option key={p.id} value={p.id}>
                          {p.product_name}
                        </option>
                      ))}
                    </select>
                  </td>
                  <td className="border border-gray-300 px-3 py-2">
                    <input
                      type="number"
                      min="1"
                      value={row.qty}
                      onChange={(e) => updateRow(row.id, 'qty', e.target.value)}
                      placeholder="Qty"
                      className="w-full p-1.5 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 text-sm font-semibold"
                    />
                  </td>
                  <td className="border border-gray-300 px-3 py-2">
                    <select
                      value={row.customer_id}
                      onChange={(e) => updateRow(row.id, 'customer_id', e.target.value)}
                      className="w-full p-1.5 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                      <option value="">Select Customer</option>
                      {customers.map((c) => (
                        <option key={c.id} value={c.id}>
                          {c.billing_name || c.name}
                        </option>
                      ))}
                    </select>
                  </td>
                  <td className="border border-gray-300 px-3 py-2">
                    {orderRows.length > 1 && (
                      <button
                        onClick={() => removeRow(row.id)}
                        className="bg-red-600 text-white px-2.5 py-1 rounded hover:bg-red-700 transition-colors text-xs"
                      >
                        <Trash2 className="h-3.5 w-3.5 inline" /> Delete
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="mt-4 flex flex-col sm:flex-row gap-3">
          <button
            onClick={handleSubmit}
            disabled={submitting}
            className="w-full sm:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm disabled:opacity-50 font-medium"
          >
            {submitting ? 'Submitting...' : 'Submit Orders'}
          </button>
          <button
            onClick={addRow}
            className="w-full sm:w-auto bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm flex items-center justify-center gap-1"
          >
            <Plus className="h-4 w-4" /> Add Row
          </button>
        </div>
      </div>

      {/* ── Active Order Tickets (With Order-Wise Competency Check) ─────────────── */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:p-6">
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
          <div>
            <h2 className="text-base lg:text-lg font-semibold text-gray-800">Active Order Tickets</h2>
            <p className="text-xs text-gray-500">Check raw material inventory competency on an individual order-by-order basis</p>
          </div>
          <span className="text-xs text-gray-500">{orders.length} active ticket(s)</span>
        </div>

        {orders.length === 0 ? (
          <p className="text-gray-500 text-sm py-4 text-center">No active pending orders.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full table-auto border-collapse border border-gray-300 text-sm">
              <thead>
                <tr className="bg-gray-50">
                  <th className="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Order ID</th>
                  <th className="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Date</th>
                  <th className="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Product</th>
                  <th className="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Qty</th>
                  <th className="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Customer</th>
                  <th className="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Status</th>
                  <th className="border border-gray-300 px-3 py-2 text-center font-semibold text-gray-700">Order-Wise Competency</th>
                </tr>
              </thead>
              <tbody>
                {orders.map((order) => (
                  <tr key={order.id} className="hover:bg-gray-50">
                    <td className="border border-gray-300 px-3 py-2 font-mono text-xs text-gray-600">#{order.id}</td>
                    <td className="border border-gray-300 px-3 py-2 text-xs">{order.order_date?.split('T')[0]}</td>
                    <td className="border border-gray-300 px-3 py-2 font-semibold text-gray-900">
                      {order.product?.product_name || getProductName(order.product_id)}
                    </td>
                    <td className="border border-gray-300 px-3 py-2 font-bold text-purple-700">{order.qty}</td>
                    <td className="border border-gray-300 px-3 py-2 text-xs">{getCustomerName(order.customer)}</td>
                    <td className="border border-gray-300 px-3 py-2">
                      <span className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${statusColors[order.status] || 'bg-gray-100 text-gray-800'}`}>
                        {order.status.replace('_', ' ')}
                      </span>
                    </td>
                    <td className="border border-gray-300 px-3 py-2 text-center">
                      <button
                        onClick={() => checkSingleOrderCompetency(order.id)}
                        className="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 px-3 py-1 rounded-md text-xs font-semibold transition-colors"
                      >
                        <Eye className="h-3.5 w-3.5" /> Check Order Competency
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* ── Order-Wise Competency Modal ─────────────────────────────── */}
      {(selectedOrderCalc || orderCalcLoading) && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl shadow-xl max-w-xl w-full p-6">
            <div className="flex justify-between items-center border-b pb-3 mb-4">
              <div className="flex items-center gap-2">
                <PackageCheck className="h-6 w-6 text-indigo-600" />
                <div>
                  <h3 className="text-lg font-bold text-gray-900">Order #{selectedOrderCalc?.order_id} Competency Breakdown</h3>
                  <p className="text-xs text-gray-500">Checking inventory availability for this specific order ticket</p>
                </div>
              </div>
              <button onClick={() => setSelectedOrderCalc(null)} className="text-gray-400 hover:text-gray-600">
                <X className="h-6 w-6" />
              </button>
            </div>

            {orderCalcLoading ? (
              <div className="py-8 text-center">
                <div className="animate-spin h-8 w-8 border-2 border-indigo-600 border-t-transparent rounded-full mx-auto mb-2"></div>
                <p className="text-sm text-gray-600">Calculating raw material requirements for Order #{selectedOrderCalc?.order_id}...</p>
              </div>
            ) : selectedOrderCalc ? (
              <div className="space-y-4">
                <div className="bg-indigo-50 p-3 rounded-lg flex justify-between items-center text-sm">
                  <div>
                    <span className="text-xs text-gray-500 block">Product</span>
                    <span className="font-bold text-gray-900">{selectedOrderCalc.product}</span>
                  </div>
                  <div>
                    <span className="text-xs text-gray-500 block">Order Quantity</span>
                    <span className="font-bold text-indigo-700">{selectedOrderCalc.order_qty} units</span>
                  </div>
                </div>

                <div className="overflow-x-auto">
                  <table className="min-w-full table-auto border-collapse border border-gray-200 text-xs">
                    <thead>
                      <tr className="bg-gray-100 text-gray-700">
                        <th className="border border-gray-200 px-3 py-2 text-left font-semibold">Raw Material</th>
                        <th className="border border-gray-200 px-3 py-2 text-left font-semibold">Order Requires</th>
                        <th className="border border-gray-200 px-3 py-2 text-left font-semibold">Available Stock</th>
                        <th className="border border-gray-200 px-3 py-2 text-left font-semibold">Surplus / Deficit</th>
                        <th className="border border-gray-200 px-3 py-2 text-left font-semibold">Status</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                      {selectedOrderCalc.inventory_status.map((item, idx) => (
                        <tr key={idx}>
                          <td className="border border-gray-200 px-3 py-2 font-medium">{item.material}</td>
                          <td className="border border-gray-200 px-3 py-2 font-semibold text-purple-700">
                            {item.requiredQty} {item.unit}
                          </td>
                          <td className="border border-gray-200 px-3 py-2 text-gray-700">
                            {item.availableQty} {item.unit}
                          </td>
                          <td className="border border-gray-200 px-3 py-2 font-bold">
                            {item.difference >= 0 ? (
                              <span className="text-green-600">+{item.difference} {item.unit}</span>
                            ) : (
                              <span className="text-red-600">{item.difference} {item.unit}</span>
                            )}
                          </td>
                          <td className="border border-gray-200 px-3 py-2">
                            {item.difference >= 0 ? (
                              <span className="text-green-700 font-bold inline-flex items-center gap-1">
                                <CheckCircle2 className="h-3.5 w-3.5" /> Sufficient
                              </span>
                            ) : (
                              <span className="text-red-700 font-bold inline-flex items-center gap-1">
                                <AlertTriangle className="h-3.5 w-3.5" /> Short {Math.abs(item.difference)}
                              </span>
                            )}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                <div className="flex justify-end pt-2">
                  <button
                    onClick={() => setSelectedOrderCalc(null)}
                    className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-xs font-semibold"
                  >
                    Close Analysis
                  </button>
                </div>
              </div>
            ) : null}
          </div>
        </div>
      )}
    </div>
  );
}
