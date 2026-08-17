import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Trash2, ShoppingCart, Package } from 'lucide-react';
import { rawMaterialService, unitService, purchaseService } from '../services';
import toast from 'react-hot-toast';
import type { RawMaterial, Unit, RawMaterialPurchase as PurchaseType } from '../types';

interface PurchaseRow {
  id: string;
  material_id: string;
  unit_id: string;
  quantity: string;
  rate: string;
  supplier_name: string;
  batch_number: string;
  expiry_date: string;
}

export function RawMaterialPurchase() {
  const navigate = useNavigate();
  const [materials, setMaterials] = useState<RawMaterial[]>([]);
  const [materialUnitsMap, setMaterialUnitsMap] = useState<Record<number, Unit[]>>({});
  const [purchases, setPurchases] = useState<PurchaseType[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [purchaseDate, setPurchaseDate] = useState(new Date().toISOString().split('T')[0]);

  const [rows, setRows] = useState<PurchaseRow[]>([
    {
      id: crypto.randomUUID(),
      material_id: '',
      unit_id: '',
      quantity: '',
      rate: '',
      supplier_name: '',
      batch_number: '',
      expiry_date: '',
    },
  ]);

  const loadData = useCallback(async () => {
    try {
      setLoading(true);
      const [materialsRes, purchasesRes] = await Promise.all([
        rawMaterialService.getAll(),
        purchaseService.getAll(),
      ]);

      const rawMats = materialsRes.materials || [];
      setMaterials(rawMats);
      setPurchases(purchasesRes.purchases || []);

      // Pre-fetch units for all materials
      const unitsMap: Record<number, Unit[]> = {};
      await Promise.all(
        rawMats.map(async (m) => {
          try {
            const uRes = await unitService.getByMaterial(m.id);
            unitsMap[m.id] = uRes.units || [];
          } catch {
            unitsMap[m.id] = [];
          }
        })
      );
      setMaterialUnitsMap(unitsMap);
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to load purchase data');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadData();
  }, [loadData]);

  const addRow = () => {
    setRows((prev) => [
      ...prev,
      {
        id: crypto.randomUUID(),
        material_id: '',
        unit_id: '',
        quantity: '',
        rate: '',
        supplier_name: '',
        batch_number: '',
        expiry_date: '',
      },
    ]);
  };

  const deleteRow = (id: string) => {
    if (rows.length > 1) {
      setRows((prev) => prev.filter((row) => row.id !== id));
    }
  };

  const updateRow = (id: string, field: keyof PurchaseRow, value: string) => {
    setRows((prev) =>
      prev.map((row) => {
        if (row.id === id) {
          const updatedRow = { ...row, [field]: value };
          // Auto select first available unit when material changes
          if (field === 'material_id') {
            const matId = parseInt(value);
            const availUnits = materialUnitsMap[matId] || [];
            updatedRow.unit_id = availUnits.length > 0 ? String(availUnits[0].id) : '';
          }
          return updatedRow;
        }
        return row;
      })
    );
  };

  const calculateTotal = (quantity: string, rate: string): number => {
    const qty = parseFloat(quantity) || 0;
    const rt = parseFloat(rate) || 0;
    return qty * rt;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    const validRows = rows.filter(
      (row) => row.material_id && row.unit_id && row.quantity && row.quantity !== '0'
    );

    if (validRows.length === 0) {
      toast.error('Please fill in at least one valid raw material purchase entry');
      return;
    }

    setSubmitting(true);
    try {
      for (const row of validRows) {
        await purchaseService.create({
          material_id: parseInt(row.material_id),
          unit_id: parseInt(row.unit_id),
          qty: parseFloat(row.quantity),
          rate: parseFloat(row.rate) || 0,
          supplier_name: row.supplier_name || 'General Supplier',
          batch_number: row.batch_number || undefined,
          expiry_date: row.expiry_date || undefined,
          purchase_date: purchaseDate,
        });
      }

      toast.success(`${validRows.length} raw material purchase(s) recorded successfully!`);

      // Reset form
      setRows([
        {
          id: crypto.randomUUID(),
          material_id: '',
          unit_id: '',
          quantity: '',
          rate: '',
          supplier_name: '',
          batch_number: '',
          expiry_date: '',
        },
      ]);
      setPurchaseDate(new Date().toISOString().split('T')[0]);

      loadData();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to record purchase');
    } finally {
      setSubmitting(false);
    }
  };

  const deletePurchase = async (id: number) => {
    if (!confirm('Are you sure you want to delete this purchase record?')) return;

    try {
      await purchaseService.delete(id);
      toast.success('Purchase record deleted successfully');
      loadData();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to delete purchase record');
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin h-12 w-12 border-4 border-blue-500 border-t-transparent rounded-full"></div>
      </div>
    );
  }

  return (
    <div className="p-4 lg:p-6 space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-xl lg:text-2xl font-bold text-gray-900">Raw Material Purchase</h1>
          <p className="text-sm text-gray-600 mt-1">Record purchases to replenish inventory stock</p>
        </div>
        <button
          onClick={() => navigate('/')}
          className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm"
        >
          Back to Dashboard
        </button>
      </div>

      {/* New Purchase Entry Card */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:p-6">
        <div className="flex items-center gap-2 mb-4">
          <ShoppingCart className="h-5 w-5 text-blue-600" />
          <h2 className="text-lg font-semibold text-gray-900">New Purchase Entry</h2>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="max-w-xs">
            <label className="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Purchase Date *</label>
            <input
              type="date"
              required
              value={purchaseDate}
              onChange={(e) => setPurchaseDate(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          {/* Desktop Rows Table */}
          <div className="hidden lg:block overflow-x-auto">
            <table className="w-full border-collapse border border-gray-200">
              <thead>
                <tr className="bg-gray-50 border-b border-gray-200">
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Material *</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Unit *</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Quantity *</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Rate (₹)</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Total (₹)</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Supplier Name</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Batch #</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Expiry Date</th>
                  <th className="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody>
                {rows.map((row) => {
                  const matId = parseInt(row.material_id);
                  const units = materialUnitsMap[matId] || [];
                  const total = calculateTotal(row.quantity, row.rate);

                  return (
                    <tr key={row.id} className="border-b border-gray-100 hover:bg-gray-50">
                      <td className="px-3 py-2">
                        <select
                          value={row.material_id}
                          onChange={(e) => updateRow(row.id, 'material_id', e.target.value)}
                          className="w-full p-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          required
                        >
                          <option value="">Select Material</option>
                          {materials.map((material) => (
                            <option key={material.id} value={material.id}>
                              {material.material}
                            </option>
                          ))}
                        </select>
                      </td>
                      <td className="px-3 py-2">
                        <select
                          value={row.unit_id}
                          onChange={(e) => updateRow(row.id, 'unit_id', e.target.value)}
                          className="w-full p-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          required
                          disabled={!row.material_id}
                        >
                          <option value="">Select Unit</option>
                          {units.map((unit) => (
                            <option key={unit.id} value={unit.id}>
                              {unit.unit_name}
                            </option>
                          ))}
                        </select>
                      </td>
                      <td className="px-3 py-2">
                        <input
                          type="number"
                          step="0.0001"
                          min="0.0001"
                          value={row.quantity}
                          onChange={(e) => updateRow(row.id, 'quantity', e.target.value)}
                          className="w-28 p-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 font-medium"
                          placeholder="e.g. 10.5"
                          required
                        />
                      </td>
                      <td className="px-3 py-2">
                        <input
                          type="number"
                          step="0.01"
                          min="0"
                          value={row.rate}
                          onChange={(e) => updateRow(row.id, 'rate', e.target.value)}
                          className="w-24 p-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          placeholder="Rate ₹"
                        />
                      </td>
                      <td className="px-3 py-2">
                        <span className="text-xs font-semibold text-gray-900">₹{total.toFixed(2)}</span>
                      </td>
                      <td className="px-3 py-2">
                        <input
                          type="text"
                          value={row.supplier_name}
                          onChange={(e) => updateRow(row.id, 'supplier_name', e.target.value)}
                          className="w-full p-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          placeholder="Supplier"
                        />
                      </td>
                      <td className="px-3 py-2">
                        <input
                          type="text"
                          value={row.batch_number}
                          onChange={(e) => updateRow(row.id, 'batch_number', e.target.value)}
                          className="w-24 p-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          placeholder="Batch"
                        />
                      </td>
                      <td className="px-3 py-2">
                        <input
                          type="date"
                          value={row.expiry_date}
                          onChange={(e) => updateRow(row.id, 'expiry_date', e.target.value)}
                          className="w-32 p-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                        />
                      </td>
                      <td className="px-3 py-2">
                        {rows.length > 1 && (
                          <button
                            type="button"
                            onClick={() => deleteRow(row.id)}
                            className="p-1 text-red-600 hover:bg-red-50 rounded transition-colors"
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        )}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>

          {/* Mobile Entry Cards */}
          <div className="lg:hidden space-y-4">
            {rows.map((row, index) => {
              const matId = parseInt(row.material_id);
              const units = materialUnitsMap[matId] || [];
              const total = calculateTotal(row.quantity, row.rate);

              return (
                <div key={row.id} className="border border-gray-200 rounded-lg p-4 space-y-3 bg-gray-50">
                  <div className="flex justify-between items-center">
                    <span className="text-xs font-semibold text-gray-700">Item Entry #{index + 1}</span>
                    {rows.length > 1 && (
                      <button type="button" onClick={() => deleteRow(row.id)} className="text-red-600 hover:text-red-800 text-xs">
                        Remove
                      </button>
                    )}
                  </div>

                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Raw Material *</label>
                    <select
                      value={row.material_id}
                      onChange={(e) => updateRow(row.id, 'material_id', e.target.value)}
                      className="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                      required
                    >
                      <option value="">Select Material</option>
                      {materials.map((m) => (
                        <option key={m.id} value={m.id}>
                          {m.material}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="grid grid-cols-2 gap-3">
                    <div>
                      <label className="block text-xs font-medium text-gray-700 mb-1">Unit *</label>
                      <select
                        value={row.unit_id}
                        onChange={(e) => updateRow(row.id, 'unit_id', e.target.value)}
                        className="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        required
                        disabled={!row.material_id}
                      >
                        <option value="">Select Unit</option>
                        {units.map((u) => (
                          <option key={u.id} value={u.id}>
                            {u.unit_name}
                          </option>
                        ))}
                      </select>
                    </div>

                    <div>
                      <label className="block text-xs font-medium text-gray-700 mb-1">Quantity *</label>
                      <input
                        type="number"
                        step="0.0001"
                        min="0.0001"
                        value={row.quantity}
                        onChange={(e) => updateRow(row.id, 'quantity', e.target.value)}
                        className="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-semibold"
                        placeholder="Quantity"
                        required
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-3">
                    <div>
                      <label className="block text-xs font-medium text-gray-700 mb-1">Rate (₹)</label>
                      <input
                        type="number"
                        step="0.01"
                        value={row.rate}
                        onChange={(e) => updateRow(row.id, 'rate', e.target.value)}
                        className="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                      />
                    </div>
                    <div className="flex flex-col justify-end">
                      <div className="bg-blue-100 p-2 rounded text-xs font-bold text-blue-900">Total: ₹{total.toFixed(2)}</div>
                    </div>
                  </div>

                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Supplier Name</label>
                    <input
                      type="text"
                      value={row.supplier_name}
                      onChange={(e) => updateRow(row.id, 'supplier_name', e.target.value)}
                      className="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                      placeholder="Supplier"
                    />
                  </div>
                </div>
              );
            })}
          </div>

          {/* Actions */}
          <div className="flex flex-col sm:flex-row gap-3">
            <button
              type="button"
              onClick={addRow}
              className="flex items-center justify-center gap-2 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm"
            >
              <Plus className="h-4 w-4" /> Add Row
            </button>
            <button
              type="submit"
              disabled={submitting}
              className="flex items-center justify-center gap-2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm disabled:opacity-50"
            >
              <ShoppingCart className="h-4 w-4" /> {submitting ? 'Recording...' : 'Record Purchases'}
            </button>
          </div>
        </form>
      </div>

      {/* Purchase History */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:p-6">
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-2">
            <Package className="h-5 w-5 text-gray-700" />
            <h2 className="text-lg font-semibold text-gray-900">Purchase History Logs</h2>
          </div>
          <span className="text-xs text-gray-500">{purchases.length} record(s)</span>
        </div>

        {purchases.length === 0 ? (
          <div className="text-center py-8">
            <ShoppingCart className="h-12 w-12 mx-auto text-gray-300 mb-2" />
            <p className="text-gray-500 text-sm">No raw material purchases recorded yet.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full border-collapse border border-gray-200 text-sm">
              <thead className="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Date</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Material</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Qty Purchased</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Rate</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Total Amount</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Supplier</th>
                  <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700">Batch #</th>
                  <th className="px-3 py-2">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {purchases.map((purchase) => (
                  <tr key={purchase.id} className="hover:bg-gray-50">
                    <td className="px-3 py-2 text-gray-900">{purchase.purchase_date?.split('T')[0]}</td>
                    <td className="px-3 py-2 font-medium text-gray-900">{purchase.material?.material || '—'}</td>
                    <td className="px-3 py-2 text-blue-700 font-semibold">
                      {purchase.qty} {purchase.unit?.unit_name || ''}
                    </td>
                    <td className="px-3 py-2 text-gray-700">₹{Number(purchase.rate).toFixed(2)}</td>
                    <td className="px-3 py-2 font-semibold text-gray-900">₹{Number(purchase.total_amount).toFixed(2)}</td>
                    <td className="px-3 py-2 text-gray-600">{purchase.supplier_name || '—'}</td>
                    <td className="px-3 py-2 text-gray-500">{purchase.batch_number || '—'}</td>
                    <td className="px-3 py-2">
                      <button
                        onClick={() => deletePurchase(purchase.id)}
                        className="p-1 text-red-600 hover:bg-red-50 rounded transition-colors"
                        title="Delete Record"
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
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
