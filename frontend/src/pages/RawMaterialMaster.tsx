import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Edit2, Trash2, Save, X, Package } from 'lucide-react';
import { api } from '../services/api';
import toast from 'react-hot-toast';

interface RawMaterial {
  id: number;
  material: string;
  description: string | null;
  base_unit: string;
  minimum_stock: number;
  created_at: string;
  updated_at: string;
  units?: Unit[];
}

interface Unit {
  id: number;
  unit_name: string;
  conversion_factor: number;
  material_id: number;
}

interface MaterialFormData {
  material: string;
  description: string;
  base_unit: string;
  minimum_stock: string;
}

interface UnitFormData {
  unit_name: string;
  conversion_factor: string;
}

export function RawMaterialMaster() {
  const navigate = useNavigate();
  const [materials, setMaterials] = useState<RawMaterial[]>([]);
  const [loading, setLoading] = useState(true);
  const [showMaterialForm, setShowMaterialForm] = useState(false);
  const [editingMaterial, setEditingMaterial] = useState<RawMaterial | null>(null);
  const [selectedMaterial, setSelectedMaterial] = useState<RawMaterial | null>(null);
  const [showUnitForm, setShowUnitForm] = useState(false);

  // Form states
  const [materialForm, setMaterialForm] = useState<MaterialFormData>({
    material: '',
    description: '',
    base_unit: '',
    minimum_stock: '0',
  });

  const [unitForm, setUnitForm] = useState<UnitFormData>({
    unit_name: '',
    conversion_factor: '1',
  });

  useEffect(() => {
    loadMaterials();
  }, []);

  const loadMaterials = async () => {
    try {
      setLoading(true);
      const response = await api.get<{ materials: RawMaterial[] }>('/raw-materials');
      setMaterials(response.data.materials);
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to load materials');
    } finally {
      setLoading(false);
    }
  };

  const handleCreateMaterial = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.post('/raw-materials', {
        material: materialForm.material,
        description: materialForm.description || null,
        base_unit: materialForm.base_unit,
        minimum_stock: parseFloat(materialForm.minimum_stock) || 0,
      });
      toast.success('Material created successfully');
      setShowMaterialForm(false);
      resetMaterialForm();
      loadMaterials();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to create material');
    }
  };

  const handleUpdateMaterial = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingMaterial) return;

    try {
      await api.put(`/raw-materials/${editingMaterial.id}`, {
        material: materialForm.material,
        description: materialForm.description || null,
        base_unit: materialForm.base_unit,
        minimum_stock: parseFloat(materialForm.minimum_stock) || 0,
      });
      toast.success('Material updated successfully');
      setEditingMaterial(null);
      resetMaterialForm();
      loadMaterials();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to update material');
    }
  };

  const handleDeleteMaterial = async (id: number) => {
    if (!confirm('Are you sure you want to delete this material?')) return;

    try {
      await api.delete(`/raw-materials/${id}`);
      toast.success('Material deleted successfully');
      loadMaterials();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to delete material');
    }
  };

  const handleEditMaterial = (material: RawMaterial) => {
    setEditingMaterial(material);
    setMaterialForm({
      material: material.material,
      description: material.description || '',
      base_unit: material.base_unit,
      minimum_stock: material.minimum_stock.toString(),
    });
  };

  const resetMaterialForm = () => {
    setMaterialForm({
      material: '',
      description: '',
      base_unit: '',
      minimum_stock: '0',
    });
  };

  const handleAddUnit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedMaterial) return;

    try {
      await api.post(`/raw-materials/${selectedMaterial.id}/units`, {
        unit_name: unitForm.unit_name,
        conversion_factor: parseFloat(unitForm.conversion_factor) || 1,
      });
      toast.success('Unit added successfully');
      setShowUnitForm(false);
      setUnitForm({ unit_name: '', conversion_factor: '1' });
      loadMaterials();
      // Refresh selected material
      const updatedMaterial = materials.find(m => m.id === selectedMaterial.id);
      if (updatedMaterial) {
        setSelectedMaterial(updatedMaterial);
      }
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to add unit');
    }
  };

  const handleDeleteUnit = async (materialId: number, unitId: number) => {
    if (!confirm('Are you sure you want to delete this unit?')) return;

    try {
      await api.delete(`/raw-materials/${materialId}/units/${unitId}`);
      toast.success('Unit deleted successfully');
      loadMaterials();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to delete unit');
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
          <h1 className="text-2xl font-bold text-gray-900">Raw Material Master</h1>
          <p className="text-sm text-gray-600 mt-1">Manage raw materials and their units</p>
        </div>
        <div className="flex gap-3">
          <button
            onClick={() => setShowMaterialForm(true)}
            className="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
          >
            <Plus className="h-5 w-5" />
            Add Material
          </button>
          <button
            onClick={() => navigate('/')}
            className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
          >
            Back to Dashboard
          </button>
        </div>
      </div>

      {/* Materials List */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {materials.length === 0 ? (
          <div className="col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <Package className="h-16 w-16 mx-auto text-gray-400 mb-4" />
            <h3 className="text-lg font-semibold text-gray-900 mb-2">No Materials Yet</h3>
            <p className="text-gray-600 mb-4">Get started by adding your first raw material</p>
            <button
              onClick={() => setShowMaterialForm(true)}
              className="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Plus className="h-5 w-5" />
              Add First Material
            </button>
          </div>
        ) : (
          materials.map((material) => (
            <div key={material.id} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <div className="flex justify-between items-start mb-4">
                <div className="flex-1">
                  <h3 className="text-lg font-semibold text-gray-900">{material.material}</h3>
                  {material.description && (
                    <p className="text-sm text-gray-600 mt-1">{material.description}</p>
                  )}
                </div>
                <div className="flex gap-2">
                  <button
                    onClick={() => handleEditMaterial(material)}
                    className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                  >
                    <Edit2 className="h-4 w-4" />
                  </button>
                  <button
                    onClick={() => handleDeleteMaterial(material.id)}
                    className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <p className="text-xs text-gray-500">Base Unit</p>
                  <p className="font-medium text-gray-900">{material.base_unit}</p>
                </div>
                <div>
                  <p className="text-xs text-gray-500">Minimum Stock</p>
                  <p className="font-medium text-gray-900">{material.minimum_stock}</p>
                </div>
              </div>

              {/* Units Section */}
              <div className="border-t border-gray-200 pt-4">
                <div className="flex justify-between items-center mb-3">
                  <h4 className="text-sm font-semibold text-gray-700">Conversion Units</h4>
                  <button
                    onClick={() => {
                      setSelectedMaterial(material);
                      setShowUnitForm(true);
                    }}
                    className="text-xs text-blue-600 hover:text-blue-700 font-medium"
                  >
                    + Add Unit
                  </button>
                </div>

                {material.units && material.units.length > 0 ? (
                  <div className="space-y-2">
                    {material.units.map((unit) => (
                      <div
                        key={unit.id}
                        className="flex justify-between items-center bg-gray-50 p-2 rounded"
                      >
                        <div>
                          <span className="font-medium text-sm">{unit.unit_name}</span>
                          <span className="text-xs text-gray-500 ml-2">
                            (1 {unit.unit_name} = {unit.conversion_factor} {material.base_unit})
                          </span>
                        </div>
                        <button
                          onClick={() => handleDeleteUnit(material.id, unit.id)}
                          className="p-1 text-red-600 hover:bg-red-50 rounded"
                        >
                          <Trash2 className="h-3 w-3" />
                        </button>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-xs text-gray-500 italic">No conversion units added</p>
                )}
              </div>
            </div>
          ))
        )}
      </div>

      {/* Material Form Modal */}
      {(showMaterialForm || editingMaterial) && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-bold text-gray-900">
                {editingMaterial ? 'Edit Material' : 'Add New Material'}
              </h2>
              <button
                onClick={() => {
                  setShowMaterialForm(false);
                  setEditingMaterial(null);
                  resetMaterialForm();
                }}
                className="text-gray-400 hover:text-gray-600"
              >
                <X className="h-6 w-6" />
              </button>
            </div>

            <form onSubmit={editingMaterial ? handleUpdateMaterial : handleCreateMaterial} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Material Name *
                </label>
                <input
                  type="text"
                  required
                  value={materialForm.material}
                  onChange={(e) => setMaterialForm({ ...materialForm, material: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="e.g., Steel, Aluminum, Plastic"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Description
                </label>
                <textarea
                  rows={3}
                  value={materialForm.description}
                  onChange={(e) => setMaterialForm({ ...materialForm, description: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Optional description"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Base Unit *
                  </label>
                  <input
                    type="text"
                    required
                    value={materialForm.base_unit}
                    onChange={(e) => setMaterialForm({ ...materialForm, base_unit: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="kg, liters, pcs"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Minimum Stock
                  </label>
                  <input
                    type="number"
                    step="0.001"
                    value={materialForm.minimum_stock}
                    onChange={(e) => setMaterialForm({ ...materialForm, minimum_stock: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="0"
                  />
                </div>
              </div>

              <div className="flex gap-3 pt-4">
                <button
                  type="submit"
                  className="flex-1 flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                >
                  <Save className="h-4 w-4" />
                  {editingMaterial ? 'Update' : 'Create'}
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setShowMaterialForm(false);
                    setEditingMaterial(null);
                    resetMaterialForm();
                  }}
                  className="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Unit Form Modal */}
      {showUnitForm && selectedMaterial && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-bold text-gray-900">
                Add Unit for {selectedMaterial.material}
              </h2>
              <button
                onClick={() => {
                  setShowUnitForm(false);
                  setUnitForm({ unit_name: '', conversion_factor: '1' });
                }}
                className="text-gray-400 hover:text-gray-600"
              >
                <X className="h-6 w-6" />
              </button>
            </div>

            <form onSubmit={handleAddUnit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Unit Name *
                </label>
                <input
                  type="text"
                  required
                  value={unitForm.unit_name}
                  onChange={(e) => setUnitForm({ ...unitForm, unit_name: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="e.g., ton, gram, liter"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Conversion Factor *
                </label>
                <input
                  type="number"
                  step="0.0001"
                  required
                  value={unitForm.conversion_factor}
                  onChange={(e) => setUnitForm({ ...unitForm, conversion_factor: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="1"
                />
                <p className="text-xs text-gray-500 mt-1">
                  1 {unitForm.unit_name || 'unit'} = {unitForm.conversion_factor || '1'} {selectedMaterial.base_unit}
                </p>
              </div>

              <div className="flex gap-3 pt-4">
                <button
                  type="submit"
                  className="flex-1 flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                >
                  <Save className="h-4 w-4" />
                  Add Unit
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setShowUnitForm(false);
                    setUnitForm({ unit_name: '', conversion_factor: '1' });
                  }}
                  className="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
