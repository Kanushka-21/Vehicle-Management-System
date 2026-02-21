import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';
import toast from 'react-hot-toast';
import { 
  Plus, 
  Fuel as FuelIcon, 
  TrendingDown,
  TrendingUp,
  DollarSign
} from 'lucide-react';

const Fuel = () => {
  const { user, hasRole } = useAuth();
  const queryClient = useQueryClient();
  const [showLogModal, setShowLogModal] = useState(false);

  const canLogFuel = hasRole(['Administrator', 'Fleet Manager', 'Driver']);

  // Fetch fuel logs
  const { data: fuelLogsData, isLoading } = useQuery({
    queryKey: ['fuel-logs'],
    queryFn: async () => {
      const response = await api.get('/fuel-logs');
      return response.data;
    },
  });

  const fuelLogs = fuelLogsData?.fuel_logs?.data || [];
  const statistics = fuelLogsData?.statistics || {};

  // Fetch vehicles for dropdown
  const { data: vehiclesData } = useQuery({
    queryKey: ['vehicles-list'],
    queryFn: async () => {
      const response = await api.get('/vehicles');
      return response.data;
    },
  });

  const vehicles = vehiclesData?.vehicles?.data || [];

  // Log fuel mutation
  const logFuelMutation = useMutation({
    mutationFn: async (data) => {
      const response = await api.post('/fuel-logs', data);
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries(['fuel-logs']);
      toast.success('Fuel log added successfully');
      setShowLogModal(false);
    },
    onError: (error) => {
      toast.error(error.response?.data?.message || 'Failed to add fuel log');
    },
  });

  const handleLogFuel = (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    logFuelMutation.mutate(data);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Fuel Management</h1>
          <p className="text-gray-600">Track and analyze fuel consumption</p>
        </div>
        {canLogFuel && (
          <button
            onClick={() => setShowLogModal(true)}
            className="btn btn-primary flex items-center gap-2"
          >
            <Plus className="w-4 h-4" />
            Log Fuel
          </button>
        )}
      </div>

      {/* Statistics */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="card">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Total Fuel Cost</p>
              <p className="text-3xl font-bold text-gray-900">
                ${Number(statistics.total_cost || 0).toFixed(0)}
              </p>
            </div>
            <div className="p-3 bg-blue-100 rounded-full">
              <DollarSign className="w-6 h-6 text-blue-600" />
            </div>
          </div>
          <p className="mt-2 text-sm text-gray-600">This month</p>
        </div>

        <div className="card">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Total Liters</p>
              <p className="text-3xl font-bold text-gray-900">
                {Number(statistics.total_liters || 0).toFixed(0)}
              </p>
            </div>
            <div className="p-3 bg-green-100 rounded-full">
              <FuelIcon className="w-6 h-6 text-green-600" />
            </div>
          </div>
          <p className="mt-2 text-sm text-gray-600">This month</p>
        </div>

        <div className="card">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Avg. Fuel Economy</p>
              <p className="text-3xl font-bold text-gray-900">
                {Number(statistics.average_economy || 0).toFixed(1)}
              </p>
            </div>
            <div className="p-3 bg-purple-100 rounded-full">
              <TrendingDown className="w-6 h-6 text-purple-600" />
            </div>
          </div>
          <p className="mt-2 text-sm text-gray-600">km/L</p>
        </div>
      </div>

      {/* Fuel Logs List */}
      <div className="card">
        <h2 className="text-lg font-bold text-gray-900 mb-4">Recent Fuel Logs</h2>
        {isLoading ? (
          <div className="text-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p className="mt-4 text-gray-600">Loading fuel logs...</p>
          </div>
        ) : fuelLogs.length === 0 ? (
          <div className="text-center py-12">
            <FuelIcon className="w-16 h-16 text-gray-400 mx-auto mb-4" />
            <p className="text-gray-600">No fuel logs found</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Liters</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price/L</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Odometer</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Economy</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {fuelLogs.map((log) => (
                  <tr key={log.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {new Date(log.date).toLocaleDateString()}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm font-medium text-gray-900">
                        {log.vehicle?.make} {log.vehicle?.model}
                      </div>
                      <div className="text-sm text-gray-500">{log.vehicle?.plate_number}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {Number(log.liters).toFixed(2)} L
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      ${Number(log.total_cost).toFixed(2)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      ${Number(log.price_per_liter).toFixed(2)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {Number(log.odometer).toLocaleString()} km
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {log.fuel_economy ? `${Number(log.fuel_economy).toFixed(2)} km/L` : 'N/A'}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Log Fuel Modal */}
      {showLogModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 max-w-md w-full">
            <h2 className="text-xl font-bold mb-4">Log Fuel Purchase</h2>
            <form onSubmit={handleLogFuel} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Vehicle <span className="text-red-500">*</span>
                </label>
                <select name="vehicle_id" required className="input">
                  <option value="">Select vehicle...</option>
                  {vehicles.map((vehicle) => (
                    <option key={vehicle.id} value={vehicle.id}>
                      {vehicle.make} {vehicle.model} - {vehicle.plate_number}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Date <span className="text-red-500">*</span>
                </label>
                <input name="date" type="date" required className="input" defaultValue={new Date().toISOString().split('T')[0]} />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Liters <span className="text-red-500">*</span>
                  </label>
                  <input name="liters" type="number" required min="0" step="0.01" className="input" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Total Cost <span className="text-red-500">*</span>
                  </label>
                  <input name="total_cost" type="number" required min="0" step="0.01" className="input" />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Odometer (km) <span className="text-red-500">*</span>
                </label>
                <input name="odometer" type="number" required min="0" className="input" />
              </div>

              <div className="flex justify-end gap-2 pt-4">
                <button
                  type="button"
                  onClick={() => setShowLogModal(false)}
                  className="btn btn-secondary"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                  disabled={logFuelMutation.isPending}
                >
                  {logFuelMutation.isPending ? 'Logging...' : 'Log Fuel'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default Fuel;
