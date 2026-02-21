import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';
import toast from 'react-hot-toast';
import { 
  Plus, 
  Search, 
  Wrench, 
  Clock, 
  CheckCircle,
  AlertCircle,
  Edit
} from 'lucide-react';

const Maintenance = () => {
  const { user, hasRole } = useAuth();
  const queryClient = useQueryClient();
  const [statusFilter, setStatusFilter] = useState('');
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [selectedWorkOrder, setSelectedWorkOrder] = useState(null);

  const canCreateWorkOrders = hasRole(['Administrator', 'Fleet Manager']);
  const isTechnician = hasRole(['Technician']);
  const isDriver = hasRole(['Driver']);

  // Fetch work orders
  const { data: workOrdersData, isLoading } = useQuery({
    queryKey: ['work-orders', statusFilter],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (statusFilter) params.append('status', statusFilter);
      const response = await api.get(`/work-orders?${params}`);
      return response.data;
    },
  });

  const workOrders = workOrdersData?.work_orders?.data || [];

  // Fetch vehicles for dropdown
  const { data: vehiclesData } = useQuery({
    queryKey: ['vehicles-list'],
    queryFn: async () => {
      const response = await api.get('/vehicles');
      return response.data;
    },
  });

  const vehicles = vehiclesData?.vehicles?.data || [];

  // Create work order mutation
  const createWorkOrderMutation = useMutation({
    mutationFn: async (data) => {
      const response = await api.post('/work-orders', data);
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries(['work-orders']);
      toast.success('Work order created successfully');
      setShowCreateModal(false);
    },
    onError: (error) => {
      toast.error(error.response?.data?.message || 'Failed to create work order');
    },
  });

  // Update work order status mutation
  const updateStatusMutation = useMutation({
    mutationFn: async ({ id, status }) => {
      const response = await api.patch(`/work-orders/${id}/status`, { status });
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries(['work-orders']);
      toast.success('Status updated successfully');
    },
    onError: (error) => {
      toast.error(error.response?.data?.message || 'Failed to update status');
    },
  });

  const handleCreateWorkOrder = (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    createWorkOrderMutation.mutate(data);
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'Completed': return <CheckCircle className="w-5 h-5 text-green-600" />;
      case 'In Progress': return <Clock className="w-5 h-5 text-blue-600 animate-pulse" />;
      case 'Pending': return <AlertCircle className="w-5 h-5 text-yellow-600" />;
      default: return <Wrench className="w-5 h-5 text-gray-600" />;
    }
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'Completed': return 'bg-green-100 text-green-800';
      case 'In Progress': return 'bg-blue-100 text-blue-800';
      case 'Pending': return 'bg-yellow-100 text-yellow-800';
      case 'On Hold': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Maintenance & Repairs</h1>
          <p className="text-gray-600">Manage work orders and service history</p>
        </div>
        {(canCreateWorkOrders || isDriver) && (
          <button
            onClick={() => setShowCreateModal(true)}
            className="btn btn-primary flex items-center gap-2"
          >
            <Plus className="w-4 h-4" />
            {isDriver ? 'Report Issue' : 'Create Work Order'}
          </button>
        )}
      </div>

      {/* Filters */}
      <div className="card">
        <div className="flex gap-4">
          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="input md:w-48"
          >
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="On Hold">On Hold</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
      </div>

      {/* Work Orders List */}
      <div className="grid grid-cols-1 gap-4">
        {isLoading ? (
          <div className="card text-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p className="mt-4 text-gray-600">Loading work orders...</p>
          </div>
        ) : workOrders.length === 0 ? (
          <div className="card text-center py-12">
            <Wrench className="w-16 h-16 text-gray-400 mx-auto mb-4" />
            <p className="text-gray-600">No work orders found</p>
          </div>
        ) : (
          workOrders.map((workOrder) => (
            <div key={workOrder.id} className="card hover:shadow-lg transition-shadow">
              <div className="flex items-start justify-between">
                <div className="flex items-start gap-4 flex-1">
                  {/* Status Icon */}
                  <div className="mt-1">
                    {getStatusIcon(workOrder.status)}
                  </div>

                  {/* Work Order Details */}
                  <div className="flex-1">
                    <div className="flex items-center gap-3 mb-2">
                      <h3 className="text-lg font-semibold text-gray-900">
                        #{workOrder.work_order_number}
                      </h3>
                      <span className={`px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(workOrder.status)}`}>
                        {workOrder.status}
                      </span>
                      <span className="px-2 py-1 text-xs bg-purple-100 text-purple-800 font-semibold rounded-full">
                        {workOrder.type}
                      </span>
                    </div>

                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                      <div>
                        <span className="text-gray-500">Vehicle:</span>
                        <p className="font-medium text-gray-900">
                          {workOrder.vehicle?.make} {workOrder.vehicle?.model}
                        </p>
                        <p className="text-xs text-gray-500">{workOrder.vehicle?.plate_number}</p>
                      </div>
                      <div>
                        <span className="text-gray-500">Description:</span>
                        <p className="font-medium text-gray-900">{workOrder.description}</p>
                      </div>
                      {workOrder.technician && (
                        <div>
                          <span className="text-gray-500">Technician:</span>
                          <p className="font-medium text-gray-900">{workOrder.technician.name}</p>
                        </div>
                      )}
                      <div>
                        <span className="text-gray-500">Cost:</span>
                        <p className="font-medium text-gray-900">
                          ${Number(workOrder.total_cost || 0).toFixed(2)}
                        </p>
                      </div>
                    </div>

                    <div className="mt-3 text-xs text-gray-500">
                      Created: {new Date(workOrder.created_at).toLocaleDateString()}
                      {workOrder.completed_at && (
                        <> • Completed: {new Date(workOrder.completed_at).toLocaleDateString()}</>
                      )}
                    </div>
                  </div>
                </div>

                {/* Actions */}
                {isTechnician && workOrder.status !== 'Completed' && (
                  <div className="flex gap-2">
                    {workOrder.status === 'Pending' && (
                      <button
                        onClick={() => updateStatusMutation.mutate({ id: workOrder.id, status: 'In Progress' })}
                        className="btn btn-primary text-sm"
                      >
                        Start Work
                      </button>
                    )}
                    {workOrder.status === 'In Progress' && (
                      <button
                        onClick={() => updateStatusMutation.mutate({ id: workOrder.id, status: 'Completed' })}
                        className="btn bg-green-600 hover:bg-green-700 text-white text-sm"
                      >
                        Complete
                      </button>
                    )}
                  </div>
                )}
              </div>
            </div>
          ))
        )}
      </div>

      {/* Create Work Order Modal */}
      {showCreateModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <h2 className="text-xl font-bold mb-4">
              {isDriver ? 'Report Vehicle Issue' : 'Create Work Order'}
            </h2>
            <form onSubmit={handleCreateWorkOrder} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Vehicle <span className="text-red-500">*</span>
                </label>
                <select name="vehicle_id" required className="input">
                  <option value="">Select vehicle...</option>
                  {vehicles.map((vehicle) => (
                    <option key={vehicle.id} value={vehicle.id}>
                      {vehicle.year} {vehicle.make} {vehicle.model} - {vehicle.plate_number}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Type <span className="text-red-500">*</span>
                </label>
                <select name="type" required className="input">
                  <option value="">Select type...</option>
                  <option value="Preventative">Preventative Maintenance</option>
                  <option value="Repair">Repair</option>
                  <option value="Inspection">Inspection</option>
                  <option value="Other">Other</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Description <span className="text-red-500">*</span>
                </label>
                <textarea
                  name="description"
                  required
                  rows="3"
                  className="input"
                  placeholder="Describe the issue or maintenance needed..."
                ></textarea>
              </div>

              {!isDriver && (
                <>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Status
                    </label>
                    <select name="status" defaultValue="Pending" className="input">
                      <option value="Pending">Pending</option>
                      <option value="In Progress">In Progress</option>
                      <option value="On Hold">On Hold</option>
                    </select>
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Labor Cost
                      </label>
                      <input name="labor_cost" type="number" min="0" step="0.01" className="input" placeholder="0.00" />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Parts Cost
                      </label>
                      <input name="parts_cost" type="number" min="0" step="0.01" className="input" placeholder="0.00" />
                    </div>
                  </div>
                </>
              )}

              <div className="flex justify-end gap-2 pt-4">
                <button
                  type="button"
                  onClick={() => setShowCreateModal(false)}
                  className="btn btn-secondary"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                  disabled={createWorkOrderMutation.isPending}
                >
                  {createWorkOrderMutation.isPending ? 'Creating...' : isDriver ? 'Submit Report' : 'Create Work Order'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default Maintenance;
