import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { useNavigate } from 'react-router-dom';
import { useState } from 'react';
import { Toaster } from 'react-hot-toast';
import { Layout } from './components/layout';
import { Dashboard } from './pages/Dashboard';

// Simple placeholder components - we'll replace these with proper pages
function RawMaterialMaster() {
  const navigate = useNavigate();
  const [materials, setMaterials] = useState([{ id: 1, name: '' }]);
  const [units, setUnits] = useState([{ id: 1, name: '' }]);
  const [customers, setCustomers] = useState([{ id: 1, name: '' }]);

  const addMaterial = () => {
    setMaterials(prev => [...prev, { id: prev.length + 1, name: '' }]);
  };

  const addUnit = () => {
    setUnits(prev => [...prev, { id: prev.length + 1, name: '' }]);
  };

  const addCustomer = () => {
    setCustomers(prev => [...prev, { id: prev.length + 1, name: '' }]);
  };

  return (
    <div className="p-4 lg:p-6">
      <h1 className="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Raw Material Master</h1>
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
        
        {/* Raw Materials Section */}
        <h3 className="text-base lg:text-lg font-semibold text-gray-800 mb-4">Insert the raw materials</h3>
        <div className="space-y-3 mb-6">
          {materials.map((material, index) => (
            <div key={material.id} className="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
              <input
                type="text"
                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base"
                placeholder={`Raw Material ${index + 1}`}
                value={material.name}
                onChange={(e) => {
                  const newMaterials = [...materials];
                  newMaterials[index].name = e.target.value;
                  setMaterials(newMaterials);
                }}
              />
              {materials.length > 1 && (
                <button 
                  onClick={() => setMaterials(prev => prev.filter(m => m.id !== material.id))}
                  className="w-full sm:w-auto bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm lg:text-base"
                >
                  Remove
                </button>
              )}
            </div>
          ))}
        </div>
        <button 
          onClick={addMaterial}
          className="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors mb-6 text-sm lg:text-base"
        >
          Add Material
        </button>

        {/* Units Section */}
        <h3 className="text-base lg:text-lg font-semibold text-gray-800 mb-4">Insert the units</h3>
        <div className="space-y-3 mb-6">
          {units.map((unit, index) => (
            <div key={unit.id} className="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
              <input
                type="text"
                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base"
                placeholder={`Unit ${index + 1} (e.g., kg, pcs, liters)`}
                value={unit.name}
                onChange={(e) => {
                  const newUnits = [...units];
                  newUnits[index].name = e.target.value;
                  setUnits(newUnits);
                }}
              />
              {units.length > 1 && (
                <button 
                  onClick={() => setUnits(prev => prev.filter(u => u.id !== unit.id))}
                  className="w-full sm:w-auto bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm lg:text-base"
                >
                  Remove
                </button>
              )}
            </div>
          ))}
        </div>
        <button 
          onClick={addUnit}
          className="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors mb-6 text-sm lg:text-base"
        >
          Add Unit
        </button>

        {/* Customers Section */}
        <h3 className="text-base lg:text-lg font-semibold text-gray-800 mb-4">Insert customer details</h3>
        <div className="space-y-3 mb-6">
          {customers.map((customer, index) => (
            <div key={customer.id} className="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
              <input
                type="text"
                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base"
                placeholder={`Customer ${index + 1} name`}
                value={customer.name}
                onChange={(e) => {
                  const newCustomers = [...customers];
                  newCustomers[index].name = e.target.value;
                  setCustomers(newCustomers);
                }}
              />
              {customers.length > 1 && (
                <button 
                  onClick={() => setCustomers(prev => prev.filter(c => c.id !== customer.id))}
                  className="w-full sm:w-auto bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm lg:text-base"
                >
                  Remove
                </button>
              )}
            </div>
          ))}
        </div>
        <button 
          onClick={addCustomer}
          className="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors mb-6 text-sm lg:text-base"
        >
          Add Customer
        </button>

        {/* Action Buttons */}
        <div className="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
          <button className="w-full sm:w-auto bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm lg:text-base">
            Submit
          </button>
          <button 
            onClick={() => navigate('/')}
            className="w-full sm:w-auto bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm lg:text-base"
          >
            Back
          </button>
        </div>
      </div>
    </div>
  );
}

function RawMaterialPurchase() {
  const navigate = useNavigate();
  const [rows, setRows] = useState([{ id: 1 }]);

  const addRow = () => {
    setRows(prev => [...prev, { id: prev.length + 1 }]);
  };

  const deleteRow = (id: number) => {
    if (rows.length > 1) {
      setRows(prev => prev.filter(row => row.id !== id));
    }
  };

  return (
    <div className="p-4 lg:p-6">
      <h1 className="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Raw Material Purchase</h1>
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
        <h3 className="text-base lg:text-lg font-semibold text-gray-800 mb-4">Purchase Details</h3>
        
        {/* Mobile: Card view for small screens */}
        <div className="block sm:hidden space-y-4">
          {rows.map((row, index) => (
            <div key={row.id} className="border border-gray-200 rounded-lg p-4">
              <div className="flex justify-between items-start mb-3">
                <span className="text-sm font-medium text-gray-700">Purchase #{index + 1}</span>
                {rows.length > 1 && (
                  <button 
                    onClick={() => deleteRow(row.id)}
                    className="text-red-600 hover:text-red-800 text-sm"
                  >
                    Remove
                  </button>
                )}
              </div>
              <div className="space-y-3">
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">Purchase Date</label>
                  <input 
                    type="date" 
                    className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">Material Name</label>
                  <input 
                    type="text" 
                    className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                    placeholder="Material name" 
                  />
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                    <input 
                      type="number" 
                      className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                      placeholder="Quantity" 
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Unit</label>
                    <input 
                      type="text" 
                      className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                      placeholder="Unit" 
                    />
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Desktop: Table view for larger screens */}
        <div className="hidden sm:block overflow-x-auto">
          <table className="min-w-full table-auto border-collapse border border-gray-300">
            <thead>
              <tr className="bg-gray-50">
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Purchase Date</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Material Name</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">QTY</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Unit</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Actions</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((row) => (
                <tr key={row.id}>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="date" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="text" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                      placeholder="Material name" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="number" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                      placeholder="Quantity" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="text" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                      placeholder="Unit" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    {rows.length > 1 && (
                      <button 
                        onClick={() => deleteRow(row.id)}
                        className="bg-red-600 text-white px-2 lg:px-3 py-1 rounded hover:bg-red-700 transition-colors text-xs lg:text-sm"
                      >
                        Delete
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        <div className="mt-6 flex flex-col sm:flex-row gap-3">
          <button className="w-full sm:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm lg:text-base">
            Submit
          </button>
          <button 
            onClick={addRow}
            className="w-full sm:w-auto bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm lg:text-base"
          >
            Add Row
          </button>
          <button 
            onClick={() => navigate('/')}
            className="w-full sm:w-auto bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm lg:text-base"
          >
            Back
          </button>
        </div>
      </div>
    </div>
  );
}

function CreateProduct() {
  const navigate = useNavigate();
  
  return (
    <div className="p-4 lg:p-6">
      <h1 className="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Add New Product</h1>
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
        <div className="space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
              <input
                type="text"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base"
                placeholder="Enter product name"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Product Code</label>
              <input
                type="text"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base"
                placeholder="Enter product code"
              />
            </div>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              rows={3}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base"
              placeholder="Enter product description"
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Category</label>
              <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base">
                <option value="">Select category</option>
                <option value="electronics">Electronics</option>
                <option value="clothing">Clothing</option>
                <option value="food">Food</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Unit</label>
              <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base">
                <option value="">Select unit</option>
                <option value="pcs">Pieces</option>
                <option value="kg">Kilograms</option>
                <option value="liters">Liters</option>
                <option value="meters">Meters</option>
              </select>
            </div>
          </div>
          
          <div className="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
            <button className="w-full sm:w-auto bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm lg:text-base">
              Create Product
            </button>
            <button 
              onClick={() => navigate('/')}
              className="w-full sm:w-auto bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm lg:text-base"
            >
              Back
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function OrderBook() {
  const navigate = useNavigate();
  const [rows, setRows] = useState([{ id: 1 }]);

  const addRow = () => {
    setRows(prev => [...prev, { id: prev.length + 1 }]);
  };

  const deleteRow = (id: number) => {
    if (rows.length > 1) {
      setRows(prev => prev.filter(row => row.id !== id));
    }
  };

  return (
    <div className="p-4 lg:p-6">
      <h1 className="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Order Book</h1>
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
        <h3 className="text-base lg:text-lg font-semibold text-gray-800 mb-4">Order Book Details</h3>
        
        {/* Mobile: Card view for small screens */}
        <div className="block sm:hidden space-y-4">
          {rows.map((row, index) => (
            <div key={row.id} className="border border-gray-200 rounded-lg p-4">
              <div className="flex justify-between items-start mb-3">
                <span className="text-sm font-medium text-gray-700">Order #{index + 1}</span>
                {rows.length > 1 && (
                  <button 
                    onClick={() => deleteRow(row.id)}
                    className="text-red-600 hover:text-red-800 text-sm"
                  >
                    Remove
                  </button>
                )}
              </div>
              <div className="space-y-3">
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">Order Date</label>
                  <input 
                    type="date" 
                    className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">Product Name</label>
                  <input 
                    type="text" 
                    className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                    placeholder="Product name" 
                  />
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                    <input 
                      type="number" 
                      className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                      placeholder="Quantity" 
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Billing Name</label>
                    <input 
                      type="text" 
                      className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                      placeholder="Billing name" 
                    />
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Desktop: Table view for larger screens */}
        <div className="hidden sm:block overflow-x-auto">
          <table className="min-w-full table-auto border-collapse border border-gray-300">
            <thead>
              <tr className="bg-gray-50">
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Order Date</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Product Name</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Quantity</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Billing Name</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Actions</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((row) => (
                <tr key={row.id}>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="date" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="text" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                      placeholder="Product name" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="number" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                      placeholder="Quantity" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="text" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                      placeholder="Billing name" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    {rows.length > 1 && (
                      <button 
                        onClick={() => deleteRow(row.id)}
                        className="bg-red-600 text-white px-2 lg:px-3 py-1 rounded hover:bg-red-700 transition-colors text-xs lg:text-sm"
                      >
                        Delete
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        <div className="mt-6 flex flex-col sm:flex-row gap-3">
          <button className="w-full sm:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm lg:text-base">
            Submit
          </button>
          <button 
            onClick={addRow}
            className="w-full sm:w-auto bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm lg:text-base"
          >
            Add Row
          </button>
          <button 
            onClick={() => navigate('/')}
            className="w-full sm:w-auto bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm lg:text-base"
          >
            Back
          </button>
        </div>
      </div>
    </div>
  );
}

function SalesBook() {
  const navigate = useNavigate();
  const [salesRows, setSalesRows] = useState([{ id: 1 }]);

  const addSalesRow = () => {
    setSalesRows(prev => [...prev, { id: prev.length + 1 }]);
  };

  const deleteSalesRow = (id: number) => {
    if (salesRows.length > 1) {
      setSalesRows(prev => prev.filter(row => row.id !== id));
    }
  };

  return (
    <div className="p-4 lg:p-6">
      <h1 className="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Sales Book</h1>
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
        
        {/* Customer Information */}
        <div className="mb-6">
          <h3 className="text-base lg:text-lg font-semibold text-gray-800 mb-4">Customer Information</h3>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs lg:text-sm font-medium text-gray-700 mb-2">Customer Name</label>
              <input
                type="text"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base"
                placeholder="Customer Name"
              />
            </div>
            <div>
              <label className="block text-xs lg:text-sm font-medium text-gray-700 mb-2">Sale Date</label>
              <input
                type="date"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base"
              />
            </div>
            <div>
              <label className="block text-xs lg:text-sm font-medium text-gray-700 mb-2">Invoice No.</label>
              <input
                type="text"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base"
                placeholder="Invoice Number"
              />
            </div>
          </div>
        </div>

        {/* Sales Items */}
        <h3 className="text-base lg:text-lg font-semibold text-gray-800 mb-4">Sales Items</h3>
        
        {/* Mobile: Card view for small screens */}
        <div className="block sm:hidden space-y-4">
          {salesRows.map((row, index) => (
            <div key={row.id} className="border border-gray-200 rounded-lg p-4">
              <div className="flex justify-between items-start mb-3">
                <span className="text-sm font-medium text-gray-700">Item #{index + 1}</span>
                {salesRows.length > 1 && (
                  <button 
                    onClick={() => deleteSalesRow(row.id)}
                    className="text-red-600 hover:text-red-800 text-sm"
                  >
                    Remove
                  </button>
                )}
              </div>
              <div className="space-y-3">
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">Product Name</label>
                  <input 
                    type="text" 
                    className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                    placeholder="Product name" 
                  />
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                    <input 
                      type="number" 
                      className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                      placeholder="Qty" 
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Rate</label>
                    <input 
                      type="number" 
                      className="w-full p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                      placeholder="Rate" 
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">Total</label>
                  <div className="px-3 py-2 bg-gray-100 border border-gray-300 rounded text-sm text-gray-700">
                    ₹0.00
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Desktop: Table view for larger screens */}
        <div className="hidden sm:block overflow-x-auto">
          <table className="min-w-full table-auto border-collapse border border-gray-300">
            <thead>
              <tr className="bg-gray-50">
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Product Name</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Quantity</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Rate</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Total</th>
                <th className="border border-gray-300 px-2 lg:px-4 py-2 text-left text-xs lg:text-sm">Actions</th>
              </tr>
            </thead>
            <tbody>
              {salesRows.map((row) => (
                <tr key={row.id}>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="text" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                      placeholder="Product name" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="number" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                      placeholder="Qty" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    <input 
                      type="number" 
                      className="w-full p-1 lg:p-2 border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs lg:text-sm" 
                      placeholder="Rate" 
                    />
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2 text-xs lg:text-sm text-gray-700">
                    ₹0.00
                  </td>
                  <td className="border border-gray-300 px-2 lg:px-4 py-2">
                    {salesRows.length > 1 && (
                      <button 
                        onClick={() => deleteSalesRow(row.id)}
                        className="bg-red-600 text-white px-2 lg:px-3 py-1 rounded hover:bg-red-700 transition-colors text-xs lg:text-sm"
                      >
                        Delete
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        <button 
          onClick={addSalesRow}
          className="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors mt-4 mb-6 text-sm lg:text-base"
        >
          Add Item
        </button>

        {/* Action Buttons */}
        <div className="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
          <button className="w-full sm:w-auto bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm lg:text-base">
            Submit Sale
          </button>
          <button 
            onClick={() => navigate('/')}
            className="w-full sm:w-auto bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm lg:text-base"
          >
            Back
          </button>
        </div>
      </div>
    </div>
  );
}

function Records() {
  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold text-gray-900 mb-4">Records</h1>
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <p className="text-gray-600">Records and reports will be implemented here.</p>
      </div>
    </div>
  );
}

function App() {
  return (
    <Router>
      <div className="h-screen overflow-hidden">
        <Routes>
          <Route path="/" element={<Layout />}>
            <Route index element={<Dashboard />} />
            <Route path="raw-materials" element={<RawMaterialMaster />} />
            <Route path="purchases" element={<RawMaterialPurchase />} />
            <Route path="products" element={<CreateProduct />} />
            <Route path="orders" element={<OrderBook />} />
            <Route path="sales" element={<SalesBook />} />
            <Route path="customers" element={<div className="p-6"><h1 className="text-2xl font-bold">Customers</h1></div>} />
            <Route path="production" element={<div className="p-6"><h1 className="text-2xl font-bold">Production</h1></div>} />
            <Route path="reports" element={<Records />} />
            <Route path="settings" element={<div className="p-6"><h1 className="text-2xl font-bold">Settings</h1></div>} />
          </Route>
        </Routes>
        <Toaster 
          position="top-right"
          toastOptions={{
            duration: 4000,
            style: {
              background: '#363636',
              color: '#fff',
            },
          }}
        />
      </div>
    </Router>
  );
}

export default App;
