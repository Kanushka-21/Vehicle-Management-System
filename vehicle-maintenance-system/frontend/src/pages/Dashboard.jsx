import { useQuery } from '@tanstack/react-query';
import api from '../services/api';
import { Car, Wrench, AlertTriangle, DollarSign } from 'lucide-react';

const Dashboard = () => {
  const { data, isLoading } = useQuery({
    queryKey: ['dashboard'],
    queryFn: async () => {
      const response = await api.get('/dashboard');
      return response.data.data;
    },
  });

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    );
  }

  const metrics = data?.metrics || {};
  const alerts = data?.alerts || [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-600">Overview of your fleet operations</p>
      </div>

      {/* Metrics Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="card">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Total Vehicles</p>
              <p className="text-3xl font-bold text-gray-900">{metrics.total_vehicles || 0}</p>
            </div>
            <div className="p-3 bg-primary-100 rounded-full">
              <Car className="w-6 h-6 text-primary-600" />
            </div>
          </div>
          <p className="mt-2 text-sm text-green-600">
            {metrics.active_vehicles || 0} active
</p>
        </div>

        <div className="card">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Work Orders</p>
              <p className="text-3xl font-bold text-gray-900">{metrics.pending_work_orders || 0}</p>
            </div>
            <div className="p-3 bg-yellow-100 rounded-full">
              <Wrench className="w-6 h-6 text-yellow-600" />
            </div>
          </div>
          <p className="mt-2 text-sm text-yellow-600">Pending</p>
        </div>

        <div className="card">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Alerts</p>
              <p className="text-3xl font-bold text-gray-900">{alerts.length || 0}</p>
            </div>
            <div className="p-3 bg-red-100 rounded-full">
              <AlertTriangle className="w-6 h-6 text-red-600" />
            </div>
          </div>
          <p className="mt-2 text-sm text-red-600">Require attention</p>
        </div>

        <div className="card">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Monthly Cost</p>
              <p className="text-3xl font-bold text-gray-900">
                ${(Number(metrics.monthly_maintenance_cost || 0) + Number(metrics.monthly_fuel_cost || 0)).toFixed(0)}
              </p>
            </div>
            <div className="p-3 bg-green-100 rounded-full">
              <DollarSign className="w-6 h-6 text-green-600" />
            </div>
          </div>
          <p className="mt-2 text-sm text-gray-600">Maintenance + Fuel</p>
        </div>
      </div>

      {/* Alerts Section */}
      {alerts.length > 0 && (
        <div className="card">
          <h2 className="text-lg font-bold text-gray-900 mb-4">Alerts & Notifications</h2>
          <div className="space-y-3">
            {alerts.slice(0, 5).map((alert, index) => (
              <div
                key={index}
                className={`p-4 rounded-lg border-l-4 ${
                  alert.severity === 'urgent'
                    ? 'bg-red-50 border-red-500'
                    : 'bg-yellow-50 border-yellow-500'
                }`}
              >
                <p className="text-sm font-medium text-gray-900">{alert.message}</p>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Recent Activities */}
      <div className="card">
        <h2 className="text-lg font-bold text-gray-900 mb-4">Recent Activities</h2>
        <div className="space-y-3">
          {data?.recent_activities?.slice(0, 5).map((activity, index) => (
            <div key={index} className="flex items-center justify-between py-3 border-b border-gray-200 last:border-0">
              <div>
                <p className="text-sm font-medium text-gray-900">{activity.message}</p>
                <p className="text-xs text-gray-500">{new Date(activity.timestamp).toLocaleString()}</p>
              </div>
              <span className={`badge ${
                activity.status === 'Completed' ? 'badge-success' :
                activity.status === 'In Progress' ? 'badge-info' :
                'badge-warning'
              }`}>
                {activity.status}
              </span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
