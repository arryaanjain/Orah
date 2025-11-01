import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Trash2, ShoppingCart, Package } from 'lucide-react';
import { api } from '../services/api';
import toast from 'react-hot-toast';

interface RawMaterial {
  id: number;
  material_name: string;
  description: string | null;
  base_unit: string;
  minimum_stock: number;
  units: Unit[];
}

interface Unit {
  id: number;
  raw_material_id: number;
  unit_name: string;
  conversion_factor: number;
}

interface Purchase {
  id: number;
  raw_material_id: number;
  unit_id: number;
  quantity: number;
  rate: number;
  total: number;
  supplier_name: string;
  batch_number: string | null;
  expiry_date: string | null;
  purchase_date: string;
  raw_material: RawMaterial;
  unit: Unit;
  created_at: string;
}

interface PurchaseRow {
  id: string;
  raw_material_id: string;
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
  const [purchases, setPurchases] = useState<Purchase[]>([]);
  const [loading, setLoading] = useState(true);
  const [purchaseDate, setPurchaseDate] = useState(new Date().toISOString().split('T')[0]);
  
  const [rows, setRows] = useState<PurchaseRow[]>([
    {
      id: crypto.randomUUID(),
      raw_material_id: '',
      unit_id: '',
      quantity: '',
      rate: '',
      supplier_name: '',
      batch_number: '',
      expiry_date: '',
    },
  ]);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const [materialsRes, purchasesRes] = await Promise.all([
        api.get<{ raw_materials: RawMaterial[] }>('/raw-materials'),
        api.get<{ purchases: Purchase[] }>('/rm-purchases'),
      ]);
      setMaterials(materialsRes.data.raw_materials);
      setPurchases(purchasesRes.data.purchases);
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to load data');
    } finally {
      setLoading(false);
    }
  };

  const addRow = () => {
    setRows([
      ...rows,
      {
        id: crypto.randomUUID(),
        raw_material_id: '',
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
      setRows(rows.filter((row) => row.id !== id));
    }
  };

  const updateRow = (id: string, field: keyof PurchaseRow, value: string) => {
    setRows(
      rows.map((row) => {
        if (row.id === id) {
          const updatedRow = { ...row, [field]: value };
          // Reset unit when material changes
          if (field === 'raw_material_id') {
            updatedRow.unit_id = '';
          }
          return updatedRow;
        }
        return row;
      })
    );
  };

  const getUnitsForMaterial = (materialId: string): Unit[] => {
    if (!materialId || !materials || materials.length === 0) return [];
    const material = materials.find((m) => m.id === parseInt(materialId));
    return material?.units || [];
  };

  const calculateTotal = (quantity: string, rate: string): number => {
    const qty = parseFloat(quantity) || 0;
    const rt = parseFloat(rate) || 0;
    return qty * rt;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    // Validate all rows
    const validRows = rows.filter(
      (row) =>
        row.raw_material_id &&
        row.unit_id &&
        row.quantity &&
        row.rate &&
        row.supplier_name
    );

    if (validRows.length === 0) {
      toast.error('Please fill in at least one complete purchase entry');
      return;
    }

    try {
      const purchaseData = validRows.map((row) => ({
        raw_material_id: parseInt(row.raw_material_id),
        unit_id: parseInt(row.unit_id),
        quantity: parseFloat(row.quantity),
        rate: parseFloat(row.rate),
        supplier_name: row.supplier_name,
        batch_number: row.batch_number || null,
        expiry_date: row.expiry_date || null,
        purchase_date: purchaseDate,
      }));

      if (purchaseData.length === 1) {
        await api.post('/rm-purchases', purchaseData[0]);
      } else {
        await api.post('/rm-purchases/batch', { purchases: purchaseData });
      }

      toast.success(`${purchaseData.length} purchase(s) recorded successfully`);
      
      // Reset form
      setRows([
        {
          id: crypto.randomUUID(),
          raw_material_id: '',
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
    }
  };

  const deletePurchase = async (id: number) => {
    if (!confirm('Are you sure you want to delete this purchase record?')) return;

    try {
      await api.delete(`/rm-purchases/${id}`);
      toast.success('Purchase deleted successfully');
      loadData();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to delete purchase');
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
          <h1 className="text-2xl font-bold text-gray-900">Raw Material Purchase</h1>
          <p className="text-sm text-gray-600 mt-1">Record new raw material purchases</p>
        </div>
        <button
          onClick={() => navigate('/')}
          className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
        >
          Back to Dashboard
        </button>
      </div>

      {/* Purchase Form */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
        <div className="flex items-center gap-2 mb-4">
          <ShoppingCart className="h-6 w-6 text-blue-600" />
          <h2 className="text-xl font-semibold text-gray-900">New Purchase Entry</h2>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Purchase Date */}
          <div className="max-w-xs">
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Purchase Date *
            </label>
            <input
              type="date"
              required
              value={purchaseDate}
              onChange={(e) => setPurchaseDate(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          {/* Purchase Rows - Desktop Table */}
          <div className="hidden lg:block overflow-x-auto">
            <table className="w-full border-collapse">
              <thead>
                <tr className="bg-gray-50 border-b border-gray-200">
                  <th className="px-3 py-2 text-left text-xs font-medium text-gray-700">Material *</th>
                  <th className="px-3 py-2 text-left text-xs font-medium text-gray-700">Unit *</th>
                  <th className="px-3 py-2 text-left text-xs font-medium text-gray-700">Quantity *</th>
                  <th className="px-3 py-2 text-left text-xs font-medium text-gray-700">Rate *</th>
                  <th className="px-3 py-2 text-left text-xs font-medium text-gray-700">Total</th>
                  <th className="px-3 py-2 text-left text-xs font-medium text-gray-700">Supplier *</th>
                  <th className="px-3 py-2 text-left text-xs font-medium text-gray-700">Batch #</th>
                  <th className="px-3 py-2 text-left text-xs font-medium text-gray-700">Expiry</th>
                  <th className="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody>
                {rows.map((row) => {
                  const units = getUnitsForMaterial(row.raw_material_id);
                  const total = calculateTotal(row.quantity, row.rate);

                  return (
                    <tr key={row.id} className="border-b border-gray-100">
                      <td className="px-3 py-2">
                        <select
                          value={row.raw_material_id}
                          onChange={(e) => updateRow(row.id, 'raw_material_id', e.target.value)}
                          className="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          required
                        >
                          <option value="">Select Material</option>
                          {materials && materials.map((material) => (
                            <option key={material.id} value={material.id}>
                              {material.material_name}
                            </option>
                          ))}
                        </select>
                      </td>
                      <td className="px-3 py-2">
                        <select
                          value={row.unit_id}
                          onChange={(e) => updateRow(row.id, 'unit_id', e.target.value)}
                          className="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          required
                          disabled={!row.raw_material_id}
                        >
                          <option value="">Select Unit</option>
                          {units && units.map((unit) => (
                            <option key={unit.id} value={unit.id}>
                              {unit.unit_name}
                            </option>
                          ))}
                        </select>
                      </td>
                      <td className="px-3 py-2">
                        <input
                          type="number"
                          step="0.01"
                          value={row.quantity}
                          onChange={(e) => updateRow(row.id, 'quantity', e.target.value)}
                          className="w-24 px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          placeholder="0.00"
                          required
                        />
                      </td>
                      <td className="px-3 py-2">
                        <input
                          type="number"
                          step="0.01"
                          value={row.rate}
                          onChange={(e) => updateRow(row.id, 'rate', e.target.value)}
                          className="w-24 px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          placeholder="0.00"
                          required
                        />
                      </td>
                      <td className="px-3 py-2">
                        <span className="text-sm font-medium text-gray-900">
                          ${total.toFixed(2)}
                        </span>
                      </td>
                      <td className="px-3 py-2">
                        <input
                          type="text"
                          value={row.supplier_name}
                          onChange={(e) => updateRow(row.id, 'supplier_name', e.target.value)}
                          className="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          placeholder="Supplier"
                          required
                        />
                      </td>
                      <td className="px-3 py-2">
                        <input
                          type="text"
                          value={row.batch_number}
                          onChange={(e) => updateRow(row.id, 'batch_number', e.target.value)}
                          className="w-24 px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          placeholder="Batch"
                        />
                      </td>
                      <td className="px-3 py-2">
                        <input
                          type="date"
                          value={row.expiry_date}
                          onChange={(e) => updateRow(row.id, 'expiry_date', e.target.value)}
                          className="w-36 px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                        />
                      </td>
                      <td className="px-3 py-2">
                        {rows.length > 1 && (
                          <button
                            type="button"
                            onClick={() => deleteRow(row.id)}
                            className="p-1.5 text-red-600 hover:bg-red-50 rounded transition-colors"
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

          {/* Purchase Rows - Mobile Cards */}
          <div className="lg:hidden space-y-4">
            {rows.map((row, index) => {
              const units = getUnitsForMaterial(row.raw_material_id);
              const total = calculateTotal(row.quantity, row.rate);

              return (
                <div key={row.id} className="border border-gray-200 rounded-lg p-4 space-y-3">
                  <div className="flex justify-between items-center mb-2">
                    <span className="text-sm font-medium text-gray-700">Entry #{index + 1}</span>
                    {rows.length > 1 && (
                      <button
                        type="button"
                        onClick={() => deleteRow(row.id)}
                        className="text-red-600 hover:text-red-800 text-sm"
                      >
                        Remove
                      </button>
                    )}
                  </div>

                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Material *</label>
                    <select
                      value={row.raw_material_id}
                      onChange={(e) => updateRow(row.id, 'raw_material_id', e.target.value)}
                      className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                      required
                    >
                      <option value="">Select Material</option>
                      {materials && materials.map((material) => (
                        <option key={material.id} value={material.id}>
                          {material.material_name}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Unit *</label>
                    <select
                      value={row.unit_id}
                      onChange={(e) => updateRow(row.id, 'unit_id', e.target.value)}
                      className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                      required
                      disabled={!row.raw_material_id}
                    >
                      <option value="">Select Unit</option>
                      {units && units.map((unit) => (
                        <option key={unit.id} value={unit.id}>
                          {unit.unit_name}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="grid grid-cols-2 gap-3">
                    <div>
                      <label className="block text-xs font-medium text-gray-700 mb-1">Quantity *</label>
                      <input
                        type="number"
                        step="0.01"
                        value={row.quantity}
                        onChange={(e) => updateRow(row.id, 'quantity', e.target.value)}
                        className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-medium text-gray-700 mb-1">Rate *</label>
                      <input
                        type="number"
                        step="0.01"
                        value={row.rate}
                        onChange={(e) => updateRow(row.id, 'rate', e.target.value)}
                        className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                        required
                      />
                    </div>
                  </div>

                  <div className="bg-blue-50 p-2 rounded">
                    <span className="text-xs font-medium text-gray-700">Total: </span>
                    <span className="text-sm font-bold text-blue-600">${total.toFixed(2)}</span>
                  </div>

                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Supplier *</label>
                    <input
                      type="text"
                      value={row.supplier_name}
                      onChange={(e) => updateRow(row.id, 'supplier_name', e.target.value)}
                      className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                      placeholder="Supplier Name"
                      required
                    />
                  </div>

                  <div className="grid grid-cols-2 gap-3">
                    <div>
                      <label className="block text-xs font-medium text-gray-700 mb-1">Batch Number</label>
                      <input
                        type="text"
                        value={row.batch_number}
                        onChange={(e) => updateRow(row.id, 'batch_number', e.target.value)}
                        className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="Optional"
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-medium text-gray-700 mb-1">Expiry Date</label>
                      <input
                        type="date"
                        value={row.expiry_date}
                        onChange={(e) => updateRow(row.id, 'expiry_date', e.target.value)}
                        className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                  </div>
                </div>
              );
            })}
          </div>

          {/* Form Actions */}
          <div className="flex flex-col sm:flex-row gap-3">
            <button
              type="button"
              onClick={addRow}
              className="flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
            >
              <Plus className="h-4 w-4" />
              Add Row
            </button>
            <button
              type="submit"
              className="flex items-center justify-center gap-2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
              <ShoppingCart className="h-4 w-4" />
              Record Purchase
            </button>
          </div>
        </form>
      </div>

      {/* Purchase History */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
        <div className="flex items-center gap-2 mb-4">
          <Package className="h-6 w-6 text-gray-600" />
          <h2 className="text-xl font-semibold text-gray-900">Purchase History</h2>
        </div>

        {purchases.length === 0 ? (
          <div className="text-center py-12">
            <ShoppingCart className="h-16 w-16 mx-auto text-gray-400 mb-4" />
            <h3 className="text-lg font-semibold text-gray-900 mb-2">No Purchases Yet</h3>
            <p className="text-gray-600">Start recording your raw material purchases above</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-700">Date</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-700">Material</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-700">Quantity</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-700">Rate</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-700">Total</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-700">Supplier</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-700">Batch</th>
                  <th className="px-4 py-3"></th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {purchases && purchases.map((purchase) => (
                  <tr key={purchase.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3 text-sm text-gray-900">
                      {new Date(purchase.purchase_date).toLocaleDateString()}
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-900">
                      {purchase.raw_material.material_name}
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-900">
                      {purchase.quantity} {purchase.unit.unit_name}
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-900">
                      ${purchase.rate.toFixed(2)}
                    </td>
                    <td className="px-4 py-3 text-sm font-medium text-gray-900">
                      ${purchase.total.toFixed(2)}
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-900">
                      {purchase.supplier_name}
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-600">
                      {purchase.batch_number || '-'}
                    </td>
                    <td className="px-4 py-3 text-sm">
                      <button
                        onClick={() => deletePurchase(purchase.id)}
                        className="p-1.5 text-red-600 hover:bg-red-50 rounded transition-colors"
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
