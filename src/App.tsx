import React, { useState } from 'react';
import {
  initialEmployees,
  initialDepartments,
  initialPositions,
  initialAuditLogs,
  initialRoles,
  initialCapabilityGroups,
  initialRoleMatrix,
} from './mockData';
import { Employee, AuditLog, RoleMeta } from './types';
import { AdminView } from './components/AdminView';
import { PortalView } from './components/PortalView';
import { PluginExplorer } from './components/PluginExplorer';
import { RolesPermissionsView } from './components/RolesPermissionsView';
import { UnifiedLoginView } from './components/UnifiedLoginView';
import {
  Download,
  Globe,
  LayoutDashboard,
  User,
  FileCode,
  Shield,
  LogIn,
} from 'lucide-react';

export default function App() {
  const [viewMode, setViewMode] = useState<'admin' | 'roles' | 'portal' | 'login' | 'explorer'>('admin');
  const [employees, setEmployees] = useState<Employee[]>(initialEmployees);
  const [auditLogs, setAuditLogs] = useState<AuditLog[]>(initialAuditLogs);
  const [roles, setRoles] = useState<RoleMeta[]>(initialRoles);
  const [roleMatrix, setRoleMatrix] = useState<Record<string, Record<string, boolean>>>(initialRoleMatrix);
  const [currentPortalEmployee, setCurrentPortalEmployee] = useState<Employee>(initialEmployees[0]);
  const [isArabic, setIsArabic] = useState<boolean>(false);
  const [activeSessionNotice, setActiveSessionNotice] = useState<string | null>(null);
  const [isNavigating, setIsNavigating] = useState<boolean>(false);
  const [navProgress, setNavProgress] = useState<number>(0);

  // Centralized Navigation Handler with Immediate Loading Feedback
  const handleNavigateView = (targetMode: 'admin' | 'roles' | 'portal' | 'login' | 'explorer') => {
    if (targetMode === viewMode) return;
    setIsNavigating(true);
    setNavProgress(35);
    
    // Smooth transition
    requestAnimationFrame(() => {
      setNavProgress(75);
      setViewMode(targetMode);
      setTimeout(() => {
        setNavProgress(100);
        setTimeout(() => {
          setIsNavigating(false);
          setNavProgress(0);
        }, 200);
      }, 80);
    });
  };

  // Add Employee Handler
  const handleAddEmployee = (empData: Partial<Employee>, accountAction: string) => {
    const newId = employees.length + 1;
    const newEmployee: Employee = {
      id: newId,
      employee_id: empData.employee_id || `NDS-${String(newId).padStart(5, '0')}`,
      user_id: empData.user_id || (accountAction === 'create' ? newId + 10 : null),
      first_name: empData.first_name || '',
      last_name: empData.last_name || '',
      full_name: `${empData.first_name} ${empData.last_name}`,
      email: empData.email || '',
      phone: empData.phone || '',
      mobile: empData.mobile || empData.phone || '',
      national_id: empData.national_id || '',
      date_of_birth: empData.date_of_birth || '1995-01-01',
      gender: empData.gender || 'male',
      hire_date: empData.hire_date || new Date().toISOString().split('T')[0],
      department_id: empData.department_id || 2,
      department_name: empData.department_name || 'Human Resources',
      position_id: empData.position_id || 3,
      position_title: empData.position_title || 'Staff',
      employment_status: 'active',
      basic_salary: empData.basic_salary || 10000,
      emergency_contact_name: empData.emergency_contact_name || '',
      emergency_contact_phone: empData.emergency_contact_phone || '',
      emergency_contact_relationship: empData.emergency_contact_relationship || '',
      address: empData.address || '',
      created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
    };

    setEmployees([newEmployee, ...employees]);

    // Record Audit Log
    const newAuditLog: AuditLog = {
      id: auditLogs.length + 1,
      user_id: 1,
      actor_name: 'Admin User',
      action: 'employee_created',
      entity_type: 'employee',
      entity_id: newId,
      old_values: null,
      new_values: {
        employee_id: newEmployee.employee_id,
        name: newEmployee.full_name,
        email: newEmployee.email,
        department: newEmployee.department_name,
        wp_user_created: accountAction === 'create',
      },
      ip_address: '127.0.0.1',
      created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
    };

    setAuditLogs([newAuditLog, ...auditLogs]);
  };

  // Toggle Status Handler
  const handleToggleStatus = (id: number) => {
    const updated = employees.map((emp) => {
      if (emp.id === id) {
        const nextStatus: 'active' | 'inactive' = emp.employment_status === 'active' ? 'inactive' : 'active';
        return { ...emp, employment_status: nextStatus };
      }
      return emp;
    });

    const targetEmp = employees.find((e) => e.id === id);
    if (targetEmp) {
      const nextStatus = targetEmp.employment_status === 'active' ? 'inactive' : 'active';
      const newAuditLog: AuditLog = {
        id: auditLogs.length + 1,
        user_id: 1,
        actor_name: 'Admin User',
        action: 'employee_status_updated',
        entity_type: 'employee',
        entity_id: id,
        old_values: { employment_status: targetEmp.employment_status },
        new_values: { employment_status: nextStatus },
        ip_address: '127.0.0.1',
        created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
      };
      setAuditLogs([newAuditLog, ...auditLogs]);
    }

    setEmployees(updated);
  };

  // When clicking to preview portal as a specific employee
  const handleSelectEmployeeForPortal = (emp: Employee) => {
    setCurrentPortalEmployee(emp);
    setViewMode('portal');
  };

  // Save role matrix
  const handleSaveMatrix = (newMatrix: Record<string, Record<string, boolean>>) => {
    setRoleMatrix(newMatrix);
    const newAudit: AuditLog = {
      id: auditLogs.length + 1,
      user_id: 1,
      actor_name: 'Administrator',
      action: 'role_permissions_updated',
      entity_type: 'role_matrix',
      entity_id: 0,
      old_values: null,
      new_values: { updated_roles: Object.keys(newMatrix) },
      ip_address: '127.0.0.1',
      created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
    };
    setAuditLogs([newAudit, ...auditLogs]);
  };

  // Reset role matrix
  const handleResetMatrix = () => {
    setRoleMatrix(initialRoleMatrix);
    const newAudit: AuditLog = {
      id: auditLogs.length + 1,
      user_id: 1,
      actor_name: 'Administrator',
      action: 'role_permissions_reset_defaults',
      entity_type: 'role_matrix',
      entity_id: 0,
      old_values: null,
      new_values: { status: 'factory_defaults_restored' },
      ip_address: '127.0.0.1',
      created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
    };
    setAuditLogs([newAudit, ...auditLogs]);
  };

  // Unified login simulation handler
  const handleSimulateLogin = (username: string, simulatedRole: string, destination: string) => {
    setActiveSessionNotice(
      isArabic
        ? `تم تسجيل الدخول بنجاح كـ (${username} - دور: ${simulatedRole}). تم التوجيه التلقائي إلى ${
            destination === 'admin' ? '/wp-admin/' : '/employee/'
          } استناداً إلى صلاحيات الدور.`
        : `Authenticated as ${username} (${simulatedRole}). Capability-based engine routed you to ${
            destination === 'admin' ? '/wp-admin/ (NDS HR Suite)' : '/employee/ (Self-Service Portal)'
          }.`
    );

    if (destination === 'admin') {
      setViewMode('admin');
    } else {
      // Find matching employee or fallback
      const found = employees.find((e) => e.email.includes(username.split('.')[0])) || employees[1];
      setCurrentPortalEmployee(found);
      setViewMode('portal');
    }

    setTimeout(() => {
      setActiveSessionNotice(null);
    }, 6000);
  };

  return (
    <div className="min-h-screen bg-[#F1F5F9] text-slate-800 flex flex-col selection:bg-teal-500 selection:text-white relative">
      {/* Centralized Slim Top Loading Progress Bar */}
      {isNavigating && (
        <div
          role="progressbar"
          aria-label={isArabic ? 'جاري تحميل الصفحة...' : 'Loading page...'}
          aria-valuemin={0}
          aria-valuemax={100}
          aria-valuenow={navProgress}
          className="fixed top-0 left-0 right-0 h-[3px] z-50 bg-teal-500/30 overflow-hidden shadow-xs"
        >
          <div
            className="h-full bg-teal-500 nds-progress-bar shadow-[0_0_8px_rgba(13,148,136,0.6)]"
            style={{ width: `${navProgress}%` }}
          />
        </div>
      )}

      {/* Top Application Header / Switcher Bar */}
      <header className="bg-slate-900 text-white border-b border-slate-800 sticky top-0 z-40 shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
          {/* Logo & Plugin Name */}
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-teal-500 text-slate-950 font-black text-sm flex items-center justify-center shadow-xs">
              NDS
            </div>
            <div>
              <div className="flex items-center gap-2">
                <span className="font-bold text-sm sm:text-base text-white">NDS HR</span>
                <span className="hidden sm:inline-block text-[10px] uppercase font-bold tracking-wider bg-teal-900 text-teal-300 px-2 py-0.5 rounded border border-teal-700/50">
                  WordPress Plugin
                </span>
              </div>
              <p className="text-[11px] text-slate-400 hidden md:block">
                Unified Authentication &bull; Roles & Capability Engine &bull; Employee Portal
              </p>
            </div>
          </div>

          {/* Navigation View Modes */}
          <div className="flex items-center gap-1.5 bg-slate-800 p-1 rounded-xl border border-slate-700/80">
            <button
              onClick={() => handleNavigateView('admin')}
              className={`flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all ${
                viewMode === 'admin'
                  ? 'bg-teal-600 text-white shadow-xs'
                  : 'text-slate-300 hover:text-white hover:bg-slate-700/50'
              }`}
            >
              <LayoutDashboard className="w-3.5 h-3.5" />
              <span className="hidden sm:inline">{isArabic ? 'إدارة الموارد (/hr/)' : 'HR Portal (/hr/)'}</span>
            </button>

            <button
              onClick={() => handleNavigateView('roles')}
              className={`flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all ${
                viewMode === 'roles'
                  ? 'bg-teal-600 text-white shadow-xs'
                  : 'text-slate-300 hover:text-white hover:bg-slate-700/50'
              }`}
            >
              <Shield className="w-3.5 h-3.5" />
              <span className="hidden sm:inline">{isArabic ? 'الأدوار والصلاحيات' : 'Roles & Matrix'}</span>
            </button>

            <button
              onClick={() => handleNavigateView('portal')}
              className={`flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all ${
                viewMode === 'portal'
                  ? 'bg-teal-600 text-white shadow-xs'
                  : 'text-slate-300 hover:text-white hover:bg-slate-700/50'
              }`}
            >
              <User className="w-3.5 h-3.5" />
              <span className="hidden sm:inline">{isArabic ? 'بوابة الموظف' : 'Portal (/employee/)'}</span>
            </button>

            <button
              onClick={() => handleNavigateView('login')}
              className={`flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all ${
                viewMode === 'login'
                  ? 'bg-teal-600 text-white shadow-xs'
                  : 'text-slate-300 hover:text-white hover:bg-slate-700/50'
              }`}
            >
              <LogIn className="w-3.5 h-3.5" />
              <span className="hidden sm:inline">{isArabic ? 'تسجيل الدخول' : 'Login (/login/)'}</span>
            </button>

            <button
              onClick={() => handleNavigateView('explorer')}
              className={`flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all ${
                viewMode === 'explorer'
                  ? 'bg-teal-600 text-white shadow-xs'
                  : 'text-slate-300 hover:text-white hover:bg-slate-700/50'
              }`}
            >
              <FileCode className="w-3.5 h-3.5" />
              <span className="hidden sm:inline">PHP Code</span>
            </button>
          </div>

          {/* Download Plugin ZIP & Language Toggle */}
          <div className="flex items-center gap-2">
            <button
              onClick={() => setIsArabic(!isArabic)}
              className="p-2 text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg border border-slate-700 transition-colors flex items-center gap-1 text-xs"
              title="Toggle Language & RTL"
            >
              <Globe className="w-3.5 h-3.5" />
              <span className="font-semibold">{isArabic ? 'EN' : 'عربي'}</span>
            </button>

            <a
              href="/nds-hr.zip"
              download="nds-hr.zip"
              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs rounded-lg shadow-xs transition-all"
              title="Download installable WordPress plugin ZIP file"
            >
              <Download className="w-3.5 h-3.5" />
              <span className="hidden md:inline">Download ZIP</span>
            </a>
          </div>
        </div>
      </header>

      {/* Post-Login Routing Feedback Banner */}
      {activeSessionNotice && (
        <div className="bg-teal-700 text-white py-2 px-4 text-xs shadow-xs flex items-center justify-center gap-2">
          <Shield className="w-4 h-4 text-teal-300" />
          <span>{activeSessionNotice}</span>
        </div>
      )}

      {/* Main Content Area */}
      <main className="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
        {viewMode === 'admin' && (
          <AdminView
            employees={employees}
            departments={initialDepartments}
            positions={initialPositions}
            auditLogs={auditLogs}
            onAddEmployee={handleAddEmployee}
            onToggleStatus={handleToggleStatus}
            onSelectEmployeeForPortal={handleSelectEmployeeForPortal}
            isArabic={isArabic}
          />
        )}

        {viewMode === 'roles' && (
          <RolesPermissionsView
            roles={roles}
            capabilityGroups={initialCapabilityGroups}
            matrix={roleMatrix}
            onSaveMatrix={handleSaveMatrix}
            onResetMatrix={handleResetMatrix}
            isArabic={isArabic}
          />
        )}

        {viewMode === 'portal' && (
          <PortalView
            currentEmployee={currentPortalEmployee}
            allEmployees={employees}
            onSwitchEmployee={setCurrentPortalEmployee}
            isArabic={isArabic}
            onToggleLanguage={() => setIsArabic(!isArabic)}
          />
        )}

        {viewMode === 'login' && (
          <UnifiedLoginView
            onSimulateLogin={handleSimulateLogin}
            isArabic={isArabic}
          />
        )}

        {viewMode === 'explorer' && <PluginExplorer />}
      </main>

      {/* Subtle Footer */}
      <footer className="bg-white border-t border-slate-200 py-4 px-6 text-center text-xs text-slate-500">
        <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
          <div className="flex items-center gap-2">
            <span className="font-semibold text-slate-700">NDS HR</span>
            <span>&bull; Production WordPress HR Management System</span>
          </div>
          <div className="flex items-center gap-4 text-[11px] text-slate-400">
            <span>Unified Auth (/login/)</span>
            <span>Capability-Based Routing</span>
            <span>Custom Roles & Permissions Matrix</span>
            <span>Single Plugin Architecture</span>
          </div>
        </div>
      </footer>
    </div>
  );
}
