import { NavLink } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import {
  LayoutDashboard,
  Car,
  Wrench,
  Package,
  Fuel,
  Users,
  DollarSign,
  FileText,
  Settings,
} from 'lucide-react';

const Sidebar = ({ isOpen }) => {
  const { user, hasRole } = useAuth();

  const navigation = [
    { name: 'Dashboard', to: '/dashboard', icon: LayoutDashboard,allowed: true },
    { name: 'Fleet Management', to: '/fleet', icon: Car, allowed: true },
    { name: 'Maintenance', to: '/maintenance', icon: Wrench, allowed: true },
    { 
      name: 'Inventory', 
      to: '/inventory', 
      icon: Package, 
      allowed: hasRole(['Administrator', 'Fleet Manager', 'Technician']) 
    },
    { name: 'Fuel Management', to: '/fuel', icon: Fuel, allowed: true },
    { 
      name: 'Drivers', 
      to: '/drivers', 
      icon: Users, 
      allowed: hasRole(['Administrator', 'Fleet Manager']) 
    },
    { 
      name: 'Finance', 
      to: '/finance', 
      icon: DollarSign, 
      allowed: hasRole(['Administrator', 'Fleet Manager']) 
    },
    { 
      name: 'Reports', 
      to: '/reports', 
      icon: FileText, 
      allowed: hasRole(['Administrator', 'Fleet Manager', 'Technician']) 
    },
    { 
      name: 'Administration', 
      to: '/admin', 
      icon: Settings, 
      allowed: hasRole(['Administrator']) 
    },
  ];

  return (
    <aside
      className={`fixed left-0 z-20 h-full bg-gray-900 pt-16 transition-transform duration-300 ${
        isOpen ? 'translate-x-0' : '-translate-x-full'
      } w-64`}
    >
      <div className="h-full overflow-y-auto px-3 py-4">
        <ul className="space-y-2">
          {navigation.filter(item => item.allowed).map((item) => (
            <li key={item.name}>
              <NavLink
                to={item.to}
                className={({ isActive }) =>
                  `flex items-center p-2 rounded-lg hover:bg-gray-700 group ${
                    isActive
                      ? 'bg-gray-700 text-white'
                      : 'text-gray-300 hover:text-white'
                  }`
                }
              >
                <item.icon className="w-5 h-5" />
                <span className="ml-3">{item.name}</span>
              </NavLink>
            </li>
          ))}
        </ul>
      </div>
    </aside>
  );
};

export default Sidebar;
