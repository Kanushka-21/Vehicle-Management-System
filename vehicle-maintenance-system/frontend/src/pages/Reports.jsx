import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';
import { FileText, Download, Calendar } from 'lucide-react';

const Reports = () => {
  const { user, hasRole } = useAuth();
  const [selectedReport, setSelectedReport] = useState('maintenance');

  const reportTypes = [
    { id: 'maintenance', name: 'Maintenance Costs', icon: FileText },
    { id: 'fuel', name: 'Fuel Consumption', icon: FileText },
    { id: 'vehicle', name: 'Vehicle Reports', icon: FileText },
    { id: 'driver', name: 'Driver Reports', icon: FileText, adminOnly: true },
  ];

  const visibleReports = reportTypes.filter(report => 
    !report.adminOnly || hasRole(['Administrator', 'Fleet Manager'])
  );

  // Fetch report data
  const { data: reportData, isLoading } = useQuery({
    queryKey: ['reports', selectedReport],
    queryFn: async () => {
      // This would fetch actual report data
      return { data: [] };
    },
  });

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Reports</h1>
          <p className="text-gray-600">Generate and view fleet reports</p>
        </div>
        <button className="btn btn-primary flex items-center gap-2">
          <Download className="w-4 h-4" />
          Export Report
        </button>
      </div>

      {/* Report Type Selection */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {visibleReports.map((report) => (
          <button
            key={report.id}
            onClick={() => setSelectedReport(report.id)}
            className={`card text-left hover:shadow-lg transition-shadow ${
              selectedReport === report.id ? 'ring-2 ring-blue-600' : ''
            }`}
          >
            <report.icon className="w-8 h-8 text-blue-600 mb-2" />
            <h3 className="font-medium text-gray-900">{report.name}</h3>
          </button>
        ))}
      </div>

      {/* Report Content */}
      <div className="card">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-lg font-bold text-gray-900">
            {visibleReports.find(r => r.id === selectedReport)?.name}
          </h2>
          <div className="flex items-center gap-2 text-sm text-gray-600">
            <Calendar className="w-4 h-4" />
            <span>Last 30 days</span>
          </div>
        </div>

        {isLoading ? (
          <div className="text-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p className="mt-4 text-gray-600">Loading report...</p>
          </div>
        ) : (
          <div className="text-center py-12">
            <FileText className="w-16 h-16 text-gray-400 mx-auto mb-4" />
            <p className="text-gray-600">Report data will be displayed here</p>
            <p className="text-sm text-gray-500 mt-2">Select date range and filters to generate report</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default Reports;
