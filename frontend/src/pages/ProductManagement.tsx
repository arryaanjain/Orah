import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Edit2, Trash2, Save, X, Package, ToggleLeft, ToggleRight, DollarSign, TrendingDown, Layers } from 'lucide-react';
import { rawMaterialService, unitService, productService } from '../services';
import toast from 'react-hot-toast';
import type { FinishedProduct, RawMaterial, Unit, ProductBOM } from '../types';

interface ProductFormData {
  product_name: string;
  description: string;
  base_unit: string;
  selling_price: string;
  minimum_stock: string;
}

interface BomRow {
  material_id: string;
  qty_required: string;
  unit_id: string;
}

export function ProductManagement() {
  const navigate = useNavigate();
  const [products, setProducts] = useState<FinishedProduct[]>([]);
  const [materials, setMaterials] = useState<RawMaterial[]>([]);
  const [materialUnitsMap, setMaterialUnitsMap] = useState<Record<number, Unit[]>>({});
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingProduct, setEditingProduct] = useState<FinishedProduct | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterActive, setFilterActive] = useState<'all' | 'active' | 'inactive'>('all');
  const [viewingBomProduct, setViewingBomProduct] = useState<FinishedProduct | null>(null);
  const [currentBom, setCurrentBom] = useState<ProductBOM[]>([]);

  const [formData, setFormData] = useState<ProductFormData>({
    product_name: '',
    description: '',
    base_unit: '',
    selling_price: '',
    minimum_stock: '',
  });

  const [bomRows, setBomRows] = useState<BomRow[]>([
    { material_id: '', qty_required: '', unit_id: '' }
  ]);

  useEffect(() => {
    loadProducts();
    loadMaterials();
  }, []);

  const loadProducts = async () => {
    try {
      setLoading(true);
      const response = await productService.getAll();
      setProducts(response.products || []);
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to load products');
    } finally {
      setLoading(false);
    }
  };

  const loadMaterials = async () => {
    try {
      const res = await rawMaterialService.getAll();
      const rawMats = res.materials || [];
      setMaterials(rawMats);

      // Load units for each material
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
    } catch {
      toast.error('Failed to load raw materials');
    }
  };

  const addBomRow = () => {
    setBomRows((prev) => [...prev, { material_id: '', qty_required: '', unit_id: '' }]);
  };

  const removeBomRow = (index: number) => {
    setBomRows((prev) => prev.filter((_, i) => i !== index));
  };

  const updateBomRow = (index: number, field: keyof BomRow, value: string) => {
    setBomRows((prev) => {
      const next = [...prev];
      next[index] = { ...next[index], [field]: value };
      // Auto reset unit_id when material changes
      if (field === 'material_id') {
        const matId = parseInt(value);
        const availUnits = materialUnitsMap[matId] || [];
        next[index].unit_id = availUnits.length > 0 ? String(availUnits[0].id) : '';
      }
      return next;
    });
  };

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const validBom = bomRows
        .filter((r) => r.material_id && r.qty_required && r.unit_id)
        .map((r) => ({
          material_id: parseInt(r.material_id),
          qty_required: parseFloat(r.qty_required),
          unit_id: parseInt(r.unit_id),
        }));

      await productService.create({
        product_name: formData.product_name,
        description: formData.description || undefined,
        base_unit: formData.base_unit,
        selling_price: parseFloat(formData.selling_price) || 0,
        minimum_stock: parseFloat(formData.minimum_stock) || 0,
        bom: validBom.length > 0 ? validBom : undefined,
      });

      toast.success('Product created successfully with BOM');
      setShowForm(false);
      resetForm();
      loadProducts();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to create product');
    }
  };

  const handleUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingProduct) return;

    try {
      await productService.update(editingProduct.id, {
        product_name: formData.product_name,
        description: formData.description || undefined,
        base_unit: formData.base_unit,
        selling_price: parseFloat(formData.selling_price) || 0,
        minimum_stock: parseFloat(formData.minimum_stock) || 0,
      });

      // Update BOM if rows provided
      const validBom = bomRows
        .filter((r) => r.material_id && r.qty_required && r.unit_id)
        .map((r) => ({
          material_id: parseInt(r.material_id),
          qty_required: parseFloat(r.qty_required),
          unit_id: parseInt(r.unit_id),
        }));

      await productService.updateBom(editingProduct.id, validBom);

      toast.success('Product and BOM updated successfully');
      setEditingProduct(null);
      resetForm();
      loadProducts();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to update product');
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Are you sure you want to delete this product?')) return;

    try {
      await productService.delete(id);
      toast.success('Product deleted successfully');
      loadProducts();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to delete product');
    }
  };

  const handleToggleActive = async (id: number, currentStatus: boolean) => {
    try {
      await productService.update(id, { is_active: !currentStatus });
      toast.success(`Product ${currentStatus ? 'deactivated' : 'activated'} successfully`);
      loadProducts();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to update product status');
    }
  };

  const handleEdit = async (product: FinishedProduct) => {
    setEditingProduct(product);
    setFormData({
      product_name: product.product_name,
      description: product.description || '',
      base_unit: product.base_unit,
      selling_price: product.selling_price.toString(),
      minimum_stock: product.minimum_stock.toString(),
    });

    // Load BOM for edit
    try {
      const bomRes = await productService.getBom(product.id);
      const existingBom = bomRes.bom || [];
      if (existingBom.length > 0) {
        setBomRows(
          existingBom.map((b) => ({
            material_id: String(b.material_id),
            qty_required: String(b.qty_required),
            unit_id: String(b.unit_id),
          }))
        );
      } else {
        setBomRows([{ material_id: '', qty_required: '', unit_id: '' }]);
      }
    } catch {
      setBomRows([{ material_id: '', qty_required: '', unit_id: '' }]);
    }
  };

  const handleViewBom = async (product: FinishedProduct) => {
    setViewingBomProduct(product);
    try {
      const res = await productService.getBom(product.id);
      setCurrentBom(res.bom || []);
    } catch {
      toast.error('Failed to load Bill of Materials');
    }
  };

  const resetForm = () => {
    setFormData({
      product_name: '',
      description: '',
      base_unit: '',
      selling_price: '',
      minimum_stock: '',
    });
    setBomRows([{ material_id: '', qty_required: '', unit_id: '' }]);
  };

  const filteredProducts = products.filter((product) => {
    if (filterActive === 'active' && !product.is_active) return false;
    if (filterActive === 'inactive' && product.is_active) return false;

    if (searchTerm) {
      const search = searchTerm.toLowerCase();
      return (
        product.product_name.toLowerCase().includes(search) ||
        product.description?.toLowerCase().includes(search) ||
        product.base_unit.toLowerCase().includes(search)
      );
    }

    return true;
  });

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
          <h1 className="text-2xl font-bold text-gray-900">Product Management</h1>
          <p className="text-sm text-gray-600 mt-1">Manage finished products and Bill of Materials (BOM)</p>
        </div>
        <div className="flex gap-3">
          <button
            onClick={() => {
              resetForm();
              setShowForm(true);
            }}
            className="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
          >
            <Plus className="h-5 w-5" />
            Add Product
          </button>
          <button
            onClick={() => navigate('/')}
            className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
          >
            Back to Dashboard
          </button>
        </div>
      </div>

      {/* Search & Filter Bar */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div className="flex flex-col sm:flex-row gap-4">
          <input
            type="text"
            placeholder="Search products..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <div className="flex gap-2">
            <button
              onClick={() => setFilterActive('all')}
              className={`px-4 py-2 rounded-lg transition-colors ${
                filterActive === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              }`}
            >
              All
            </button>
            <button
              onClick={() => setFilterActive('active')}
              className={`px-4 py-2 rounded-lg transition-colors ${
                filterActive === 'active' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              }`}
            >
              Active
            </button>
            <button
              onClick={() => setFilterActive('inactive')}
              className={`px-4 py-2 rounded-lg transition-colors ${
                filterActive === 'inactive' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              }`}
            >
              Inactive
            </button>
          </div>
        </div>
      </div>

      {/* Products Grid */}
      {filteredProducts.length === 0 ? (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <Package className="h-16 w-16 mx-auto text-gray-400 mb-4" />
          <h3 className="text-lg font-semibold text-gray-900 mb-2">
            {searchTerm || filterActive !== 'all' ? 'No products found' : 'No Products Yet'}
          </h3>
          <p className="text-gray-600 mb-4">
            {searchTerm || filterActive !== 'all' ? 'Try adjusting your filters' : 'Start building your product catalog'}
          </p>
          {!searchTerm && filterActive === 'all' && (
            <button
              onClick={() => {
                resetForm();
                setShowForm(true);
              }}
              className="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Plus className="h-5 w-5" />
              Add First Product
            </button>
          )}
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {filteredProducts.map((product) => (
            <div
              key={product.id}
              className={`bg-white rounded-lg shadow-sm border-2 p-6 hover:shadow-md transition-all flex flex-col justify-between ${
                product.is_active ? 'border-green-200' : 'border-gray-200 opacity-75'
              }`}
            >
              <div>
                <div className="flex justify-between items-start mb-4">
                  <div className="flex-1">
                    <div className="flex items-center gap-2 mb-2">
                      <h3 className="text-lg font-semibold text-gray-900">{product.product_name}</h3>
                      {product.is_active ? (
                        <span className="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded">
                          Active
                        </span>
                      ) : (
                        <span className="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs font-medium rounded">
                          Inactive
                        </span>
                      )}
                    </div>
                    {product.description && <p className="text-sm text-gray-600 mb-3 line-clamp-2">{product.description}</p>}
                  </div>
                </div>

                <div className="space-y-2 mb-4">
                  <div className="flex items-center gap-2 text-sm">
                    <Package className="h-4 w-4 text-gray-500" />
                    <span className="text-gray-700">
                      Unit: <span className="font-medium">{product.base_unit}</span>
                    </span>
                  </div>
                  <div className="flex items-center gap-2 text-sm">
                    <DollarSign className="h-4 w-4 text-green-600" />
                    <span className="text-gray-700">
                      Price: <span className="font-bold text-green-600">₹{Number(product.selling_price).toFixed(2)}</span>
                    </span>
                  </div>
                  <div className="flex items-center gap-2 text-sm">
                    <TrendingDown className="h-4 w-4 text-orange-500" />
                    <span className="text-gray-700">
                      Min Stock: <span className="font-medium">{product.minimum_stock}</span>
                    </span>
                  </div>
                  <div className="flex items-center gap-2 text-sm">
                    <Layers className="h-4 w-4 text-purple-600" />
                    <span className="text-gray-700">
                      BOM Items: <span className="font-semibold text-purple-700">{product.bom_items?.length || 0} material(s)</span>
                    </span>
                  </div>
                </div>
              </div>

              <div className="space-y-2 pt-3 border-t border-gray-100">
                <button
                  onClick={() => handleViewBom(product)}
                  className="w-full flex items-center justify-center gap-1.5 bg-purple-50 text-purple-700 py-1.5 rounded-lg hover:bg-purple-100 transition-colors text-xs font-semibold"
                >
                  <Layers className="h-3.5 w-3.5" /> View BOM Formula
                </button>
                <div className="flex gap-2">
                  <button
                    onClick={() => handleToggleActive(product.id, product.is_active)}
                    className={`flex-1 flex items-center justify-center gap-1 px-3 py-2 rounded-lg transition-colors ${
                      product.is_active ? 'bg-orange-50 text-orange-600 hover:bg-orange-100' : 'bg-green-50 text-green-600 hover:bg-green-100'
                    }`}
                    title={product.is_active ? 'Deactivate' : 'Activate'}
                  >
                    {product.is_active ? <ToggleRight className="h-4 w-4" /> : <ToggleLeft className="h-4 w-4" />}
                  </button>
                  <button
                    onClick={() => handleEdit(product)}
                    className="flex-1 flex items-center justify-center gap-1 bg-blue-50 text-blue-600 px-3 py-2 rounded-lg hover:bg-blue-100 transition-colors"
                  >
                    <Edit2 className="h-4 w-4" />
                  </button>
                  <button
                    onClick={() => handleDelete(product.id)}
                    className="flex-1 flex items-center justify-center gap-1 bg-red-50 text-red-600 px-3 py-2 rounded-lg hover:bg-red-100 transition-colors"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Product Form Modal (Add / Edit + BOM Editor) */}
      {(showForm || editingProduct) && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-bold text-gray-900">{editingProduct ? 'Edit Product & BOM' : 'Add New Finished Product'}</h2>
              <button
                onClick={() => {
                  setShowForm(false);
                  setEditingProduct(null);
                  resetForm();
                }}
                className="text-gray-400 hover:text-gray-600"
              >
                <X className="h-6 w-6" />
              </button>
            </div>

            <form onSubmit={editingProduct ? handleUpdate : handleCreate} className="space-y-6">
              {/* Product Basic Details */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="sm:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                  <input
                    type="text"
                    required
                    value={formData.product_name}
                    onChange={(e) => setFormData({ ...formData, product_name: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    placeholder="e.g., Gulab Jamun 1kg Box"
                  />
                </div>

                <div className="sm:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <textarea
                    rows={2}
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    placeholder="Product details..."
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Base Unit *</label>
                  <input
                    type="text"
                    required
                    value={formData.base_unit}
                    onChange={(e) => setFormData({ ...formData, base_unit: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    placeholder="e.g. Box, Piece, Kg"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Selling Price (₹)</label>
                  <input
                    type="number"
                    step="0.01"
                    value={formData.selling_price}
                    onChange={(e) => setFormData({ ...formData, selling_price: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    placeholder="0.00"
                  />
                </div>
              </div>

              {/* Bill of Materials (BOM) Section */}
              <div className="border-t border-gray-200 pt-4">
                <div className="flex justify-between items-center mb-3">
                  <div>
                    <h3 className="text-base font-semibold text-gray-800">Bill of Materials (Raw Material Recipe)</h3>
                    <p className="text-xs text-gray-500">Specify raw materials required to produce 1 unit of this product</p>
                  </div>
                  <button
                    type="button"
                    onClick={addBomRow}
                    className="text-xs bg-purple-600 text-white px-3 py-1.5 rounded hover:bg-purple-700 transition-colors flex items-center gap-1"
                  >
                    <Plus className="h-3.5 w-3.5" /> Add Material
                  </button>
                </div>

                <div className="space-y-3">
                  {bomRows.map((row, idx) => {
                    const selectedMatId = parseInt(row.material_id);
                    const availUnits = materialUnitsMap[selectedMatId] || [];

                    return (
                      <div key={idx} className="flex flex-col sm:flex-row gap-2 items-center bg-gray-50 p-2.5 rounded-lg border border-gray-200">
                        <div className="flex-1 w-full">
                          <select
                            value={row.material_id}
                            onChange={(e) => updateBomRow(idx, 'material_id', e.target.value)}
                            className="w-full p-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-blue-500"
                          >
                            <option value="">Select Raw Material</option>
                            {materials.map((m) => (
                              <option key={m.id} value={m.id}>
                                {m.material}
                              </option>
                            ))}
                          </select>
                        </div>
                        <div className="w-full sm:w-28">
                          <input
                            type="number"
                            step="0.0001"
                            placeholder="Qty needed"
                            value={row.qty_required}
                            onChange={(e) => updateBomRow(idx, 'qty_required', e.target.value)}
                            className="w-full p-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-blue-500"
                          />
                        </div>
                        <div className="w-full sm:w-36">
                          <select
                            value={row.unit_id}
                            onChange={(e) => updateBomRow(idx, 'unit_id', e.target.value)}
                            className="w-full p-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-blue-500"
                          >
                            <option value="">Select Unit</option>
                            {availUnits.map((u) => (
                              <option key={u.id} value={u.id}>
                                {u.unit_name}
                              </option>
                            ))}
                          </select>
                        </div>
                        {bomRows.length > 1 && (
                          <button
                            type="button"
                            onClick={() => removeBomRow(idx)}
                            className="text-red-600 hover:text-red-800 p-1 text-xs"
                          >
                            <X className="h-4 w-4" />
                          </button>
                        )}
                      </div>
                    );
                  })}
                </div>
              </div>

              {/* Form Action Buttons */}
              <div className="flex gap-3 pt-4 border-t border-gray-200">
                <button
                  type="submit"
                  className="flex-1 flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm"
                >
                  <Save className="h-4 w-4" />
                  {editingProduct ? 'Update Product & BOM' : 'Create Product'}
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setShowForm(false);
                    setEditingProduct(null);
                    resetForm();
                  }}
                  className="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors text-sm"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* View BOM Modal */}
      {viewingBomProduct && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
            <div className="flex justify-between items-center mb-4 border-b pb-3">
              <div>
                <h3 className="text-lg font-bold text-gray-900">{viewingBomProduct.product_name}</h3>
                <p className="text-xs text-gray-500">Bill of Materials (BOM Formula)</p>
              </div>
              <button onClick={() => setViewingBomProduct(null)} className="text-gray-400 hover:text-gray-600">
                <X className="h-6 w-6" />
              </button>
            </div>

            {currentBom.length === 0 ? (
              <p className="text-sm text-gray-500 py-4 text-center">No materials configured for this product yet.</p>
            ) : (
              <div className="overflow-x-auto">
                <table className="min-w-full table-auto border-collapse border border-gray-300 text-sm">
                  <thead>
                    <tr className="bg-gray-50">
                      <th className="border border-gray-300 px-3 py-2 text-left text-xs font-semibold text-gray-700">Material</th>
                      <th className="border border-gray-300 px-3 py-2 text-left text-xs font-semibold text-gray-700">Qty / Unit Product</th>
                      <th className="border border-gray-300 px-3 py-2 text-left text-xs font-semibold text-gray-700">Unit</th>
                    </tr>
                  </thead>
                  <tbody>
                    {currentBom.map((item) => (
                      <tr key={item.id}>
                        <td className="border border-gray-300 px-3 py-2 font-medium">{item.material?.material || '—'}</td>
                        <td className="border border-gray-300 px-3 py-2 text-purple-700 font-semibold">{item.qty_required}</td>
                        <td className="border border-gray-300 px-3 py-2 text-gray-600">{item.unit?.unit_name || '—'}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}

            <div className="mt-6 flex justify-end">
              <button
                onClick={() => setViewingBomProduct(null)}
                className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
