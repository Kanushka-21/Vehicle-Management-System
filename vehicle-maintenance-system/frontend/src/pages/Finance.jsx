import { useQuery } from '@tanstack/react-query';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';
import { 
  DollarSign, 
  TrendingUp,
  TrendingDown,
  Calendar
} from 'lucide-react';

const Finance = () => {
  const { user, hasRole } = useAuth();

  // Fetch financial data
  const { data: financeData, isLoading } = useQuery({
    queryKey: ['finance-overview'],
    queryFn: async () => {
      const response = await api.get('/dashboard');
      return response.data;
    },
  });

  const metrics = financeData?.data?.metrics || {};

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Finance & Costing</h1>
        <p className="text-gray-600">Track all fleet-related costs</p>
      </div>

      {/* Cost Summary */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="card">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Monthly Fuel Cost</p>
              <p className="text-3xl font-bold text-gray-900">
                ${Number(metrics.monthly_fuel_cost || 0).toFixed(0)}
              </p>
            </div>
            <div className="p-3 bg-blue-100 rounded-full">
              <TrendingUp className="w-6 h-6 text-blue-600" />
            </div>
          </div>
          <p className="mt-2 text-sm text-gray-600">This month</p>
        </div>

        <div className="card">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Monthly Maintenance</p>
              <p className="text-3xl font-bold text-gray-900">
                ${Number(metrics.monthly_maintenance_cost || 0).toFixed(0)}
              </p>
            </div>
            <div className="p-3 bg-green-100 rounded-full">
              <DollarSign className="w-6 h-6 text-green-600" />
            </div>
          </div>
          <p className="mt-2 text-sm text-gray-600">This month</p>
        </div>

        <div className="card">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Total Monthly Cost</p>
              <p className="text-3xl font-bold text-gray-900">
                ${(Number(metrics.monthly_fuel_cost || 0) + Number(metrics.monthly_maintenance_cost || 0)).toFixed(0)}
              </p>
            </div>
            <div className="p-3 bg-purple-100 rounded-full">
              <Calendar className="w-6 h-6 text-purple-600" />
            </div>
          </div>
          <p className="mt-2 text-sm text-gray-600">Fuel + Maintenance</p>
        </div>
      </div>

      {/* Cost Breakdown */}
      <div className="card">
        <h2 className="text-lg font-bold text-gray-900 mb-4">Cost Categories</h2>
        <div className="space-y-4">
          <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div>
              <p className="font-medium text-gray-900">Fuel</p>
              <p className="text-sm text-gray-600">All fuel purchases</p>
            </div>
            <p className="text-lg font-bold text-gray-900">
              ${Number(metrics.monthly_fuel_cost || 0).toFixed(2)}
            </p>
          </div>
          <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div>
              <p className="font-medium text-gray-900">Maintenance & Repairs</p>
              <p className="text-sm text-gray-600">Work orders and parts</p>
            </div>
            <p className="text-lg font-bold text-gray-900">
              ${Number(metrics.monthly_maintenance_cost || 0).toFixed(2)}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Finance;
