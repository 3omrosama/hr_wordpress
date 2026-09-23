import React, { useState } from 'react';
import { Employee, Department, Position, AuditLog } from '../types';
import {
  Users,
  UserCheck,
  UserX,
  Building2,
  Plus,
  Search,
  ExternalLink,
  Eye,
  Edit2,
  Shield,
  FileText,
  Clock,
  CheckCircle2,
  Calendar,
  AlertCircle,
  X,
  User,
  Key,
  Copy,
  Check,
  EyeOff,
} from 'lucide-react';

interface AdminViewProps {
  employees: Employee[];
  departments: Department[];
  positions: Position[];
  auditLogs: AuditLog[];
  onAddEmployee: (empData: Partial<Employee>, accountAction: string) => void;
  onToggleStatus: (id: number) => void;
  onSelectEmployeeForPortal: (emp: Employee) => void;
  isArabic: boolean;
}

export const AdminView: React.FC<AdminViewProps> = ({
  employees,
  departments,
  positions,
  auditLogs,
  onAddEmployee,
  onToggleStatus,
  onSelectEmployeeForPortal,
  isArabic,
}) => {
  const [activeTab, setActiveTab] = useState<'dashboard' | 'employees' | 'audit' | 'settings' | 'blueprint'>('dashboard');
  const [settingsSection, setSettingsSection] = useState<'general' | 'employees' | 'attendance' | 'leave' | 'payroll' | 'notifications' | 'localization' | 'security'>('general');
  const [generalSettings, setGeneralSettings] = useState({
    company_name: 'NDS HR Demo Corp',
    company_email: 'hr@example.com',
    company_phone: '+20 100 000 0000',
    company_address: 'Cairo, Egypt',
  });
  const [localizationSettings, setLocalizationSettings] = useState({
    default_language: 'en',
  });
  const [settingsSavedNotice, setSettingsSavedNotice] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [deptFilter, setDeptFilter] = useState<number>(0);
  const [statusFilter, setStatusFilter] = useState<string>('');
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [viewingEmployee, setViewingEmployee] = useState<Employee | null>(null);

  // New Employee Form State
  const nextIdNumber = employees.length + 1;
  const generatedId = `NDS-${String(nextIdNumber).padStart(5, '0')}`;

  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    national_id: '',
    date_of_birth: '1992-05-15',
    gender: 'male' as const,
    hire_date: new Date().toISOString().split('T')[0],
    department_id: 2,
    position_id: 3,
    basic_salary: 12000,
    emergency_contact_name: '',
    emergency_contact_phone: '',
    emergency_contact_relationship: '',
    address: '',
    account_action: 'create',
    account_username: '',
    password_mode: 'generate' as 'generate' | 'manual',
    password: '',
    confirm_password: '',
    show_password: true,
    require_password_change: true,
  });

  // Staged / Created Credentials Notification State
  const [sessionCredentials, setSessionCredentials] = useState<{
    employeeId: string;
    username: string;
    temporaryPassword: string;
    requirePasswordChange: boolean;
  } | null>(null);

  const [hasCopiedCredentials, setHasCopiedCredentials] = useState(false);

  // Helper: Secure password generation matching WordPress standard
  const generatePasswordString = () => {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*()-_=+';
    let result = '';
    for (let i = 0; i < 14; i++) {
      result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
  };

  const activeCount = employees.filter((e) => e.employment_status === 'active').length;
  const inactiveCount = employees.filter((e) => e.employment_status !== 'active').length;

  const filteredEmployees = employees.filter((emp) => {
    const matchesSearch =
      searchQuery === '' ||
      emp.full_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      emp.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
      emp.employee_id.toLowerCase().includes(searchQuery.toLowerCase()) ||
      emp.phone.includes(searchQuery);

    const matchesDept = deptFilter === 0 || emp.department_id === deptFilter;
    const matchesStatus = statusFilter === '' || emp.employment_status === statusFilter;

    return matchesSearch && matchesDept && matchesStatus;
  });

  const handleSubmitNewEmployee = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.first_name || !formData.last_name || !formData.email) return;

    const deptObj = departments.find((d) => d.id === Number(formData.department_id));
    const posObj = positions.find((p) => p.id === Number(formData.position_id));

    const newEmp: Partial<Employee> = {
      employee_id: generatedId,
      first_name: formData.first_name,
      last_name: formData.last_name,
      full_name: `${formData.first_name} ${formData.last_name}`,
      email: formData.email,
      phone: formData.phone || '+966 50 000 0000',
      mobile: formData.phone || '+966 50 000 0000',
      national_id: formData.national_id || '1029384756',
      date_of_birth: formData.date_of_birth,
      gender: formData.gender,
      hire_date: formData.hire_date,
      department_id: Number(formData.department_id),
      department_name: deptObj ? deptObj.name : 'General',
      position_id: Number(formData.position_id),
      position_title: posObj ? posObj.title : 'Staff',
      employment_status: 'active',
      basic_salary: Number(formData.basic_salary),
      emergency_contact_name: formData.emergency_contact_name || 'Family Contact',
      emergency_contact_phone: formData.emergency_contact_phone || '+966 55 000 0000',
      emergency_contact_relationship: formData.emergency_contact_relationship || 'Relative',
      address: formData.address || 'Riyadh, Saudi Arabia',
      user_id: formData.account_action !== 'none' ? employees.length + 10 : null,
    };

    // Handle generated / entered password
    let finalPassword = formData.password;
    if (formData.account_action === 'create' && (!finalPassword || formData.password_mode === 'generate')) {
      finalPassword = finalPassword || generatePasswordString();
    }

    const username = formData.account_username || formData.email.split('@')[0].toLowerCase().replace(/[^a-z0-9._-]/g, '');

    if (formData.account_action === 'create') {
      setSessionCredentials({
        employeeId: generatedId,
        username,
        temporaryPassword: finalPassword,
        requirePasswordChange: formData.require_password_change,
      });
      setHasCopiedCredentials(false);
    } else {
      setSessionCredentials(null);
    }

    onAddEmployee(newEmp, formData.account_action);
    setIsAddModalOpen(false);
    // Reset form
    setFormData({
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      national_id: '',
      date_of_birth: '1992-05-15',
      gender: 'male',
      hire_date: new Date().toISOString().split('T')[0],
      department_id: 2,
      position_id: 3,
      basic_salary: 12000,
      emergency_contact_name: '',
      emergency_contact_phone: '',
      emergency_contact_relationship: '',
      address: '',
      account_action: 'create',
      account_username: '',
      password_mode: 'generate',
      password: '',
      confirm_password: '',
      show_password: true,
      require_password_change: true,
    });
  };

  return (
    <div className="space-y-6" dir={isArabic ? 'rtl' : 'ltr'}>
      {/* WordPress Admin Navigation & Header */}
      <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <div className="w-11 h-11 rounded-lg bg-teal-600 text-white font-bold text-lg flex items-center justify-center shadow-sm">
            NDS
          </div>
          <div>
            <h1 className="text-xl font-bold text-slate-900 leading-tight">
              {isArabic ? 'نظام إدارة الموارد البشرية NDS HR' : 'NDS HR — WordPress Admin Suite'}
            </h1>
            <p className="text-xs text-slate-500 mt-0.5">
              {isArabic
                ? 'المرحلة 1: التأسيس وإدارة الموظفين والربط مع ووردبريس'
                : 'Phase 1: Custom DB Tables, RBAC, WP User Sync, & Employee Directory'}
            </p>
          </div>
        </div>

        {/* Action Buttons & Tabs */}
        <div className="flex items-center flex-wrap gap-2">
          <div className="bg-slate-100 p-1 rounded-lg flex gap-1 border border-slate-200">
            <button
              onClick={() => setActiveTab('dashboard')}
              className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                activeTab === 'dashboard' ? 'bg-white text-teal-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              {isArabic ? 'لوحة التحكم' : 'Dashboard'}
            </button>
            <button
              onClick={() => setActiveTab('employees')}
              className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                activeTab === 'employees' ? 'bg-white text-teal-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              {isArabic ? 'دليل الموظفين' : 'Employees'}
            </button>
            <button
              onClick={() => setActiveTab('audit')}
              className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                activeTab === 'audit' ? 'bg-white text-teal-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              {isArabic ? 'سجل التدقيق' : 'Audit Logs'}
            </button>
            <button
              onClick={() => setActiveTab('settings')}
              className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                activeTab === 'settings' ? 'bg-white text-teal-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              {isArabic ? 'الإعدادات' : 'Settings'}
            </button>
            <button
              onClick={() => setActiveTab('blueprint')}
              className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                activeTab === 'blueprint' ? 'bg-white text-teal-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              {isArabic ? 'خارطة المراحل' : 'Roadmap'}
            </button>
          </div>

          <button
            onClick={() => setIsAddModalOpen(true)}
            className="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors"
          >
            <Plus className="w-3.5 h-3.5" />
            <span>{isArabic ? 'إضافة موظف جديد' : 'Add Employee'}</span>
          </button>
        </div>
      </div>

      {/* SUCCESS: EMPLOYEE CREATED WITH TEMPORARY CREDENTIALS BANNER */}
      {sessionCredentials && (
        <div className="bg-teal-50 border-2 border-teal-600 rounded-xl p-5 shadow-sm space-y-4">
          <div className="flex items-start justify-between gap-4">
            <div className="space-y-1">
              <div className="flex items-center gap-2">
                <CheckCircle2 className="w-5 h-5 text-teal-600" />
                <h3 className="text-base font-bold text-teal-950">
                  {isArabic ? 'تم إنشاء الموظف بنجاح' : 'Employee Created Successfully'}
                </h3>
              </div>
              <p className="text-xs text-slate-600">
                {isArabic
                  ? 'تم تهيئة بيانات الحساب. تظهر بيانات الاعتماد أدناه لهذه الجلسة فقط ولا تُخزَّن كلمة المرور في أي جدول مخصص.'
                  : 'Corporate login account initialized. Below are the credentials for this creation session only; the temporary password is not stored in any HR database table.'}
              </p>
            </div>
            <button
              onClick={() => setSessionCredentials(null)}
              className="text-slate-400 hover:text-slate-600 p-1"
              title="Dismiss"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <div className="bg-white border border-teal-200 rounded-lg p-3 sm:p-4 font-mono text-xs text-slate-800 space-y-1.5 max-w-md">
            <div className="flex items-center justify-between">
              <span className="text-slate-500 font-sans">{isArabic ? 'رمز الموظف:' : 'Employee ID:'}</span>
              <span className="font-bold text-slate-900">{sessionCredentials.employeeId}</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-slate-500 font-sans">{isArabic ? 'اسم المستخدم:' : 'Username:'}</span>
              <span className="font-bold text-slate-900">{sessionCredentials.username}</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-slate-500 font-sans">{isArabic ? 'كلمة المرور المؤقتة:' : 'Temporary Password:'}</span>
              <span className="font-bold text-teal-700 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                {sessionCredentials.temporaryPassword}
              </span>
            </div>
            {sessionCredentials.requirePasswordChange && (
              <div className="pt-1.5 border-t border-slate-100 flex items-center gap-1.5 text-amber-700 font-sans text-[11px]">
                <Key className="w-3.5 h-3.5" />
                <span>{isArabic ? 'يتطلب تغيير كلمة المرور عند أول تسجيل دخول' : 'Requires password change on first login at /login/'}</span>
              </div>
            )}
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={() => {
                const text = `Employee ID: ${sessionCredentials.employeeId}\nUsername: ${sessionCredentials.username}\nTemporary Password: ${sessionCredentials.temporaryPassword}\nLogin URL: ${window.location.origin}/login/`;
                navigator.clipboard.writeText(text);
                setHasCopiedCredentials(true);
                setTimeout(() => setHasCopiedCredentials(false), 3000);
              }}
              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors"
            >
              {hasCopiedCredentials ? (
                <>
                  <Check className="w-3.5 h-3.5" />
                  <span>{isArabic ? 'تم النسخ للحافظة!' : 'Credentials Copied!'}</span>
                </>
              ) : (
                <>
                  <Copy className="w-3.5 h-3.5" />
                  <span>{isArabic ? 'نسخ بيانات الاعتماد' : 'Copy Credentials'}</span>
                </>
              )}
            </button>
          </div>
        </div>
      )}

      {/* TAB 1: DASHBOARD */}
      {activeTab === 'dashboard' && (
        <div className="space-y-6">
          {/* 4 KPI Metrics */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                <Users className="w-6 h-6" />
              </div>
              <div>
                <span className="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                  {isArabic ? 'إجمالي القوى العاملة' : 'Total Workforce'}
                </span>
                <div className="text-2xl font-bold text-slate-900 mt-0.5">{employees.length}</div>
              </div>
            </div>

            <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <UserCheck className="w-6 h-6" />
              </div>
              <div>
                <span className="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                  {isArabic ? 'الموظفون النشطون' : 'Active Employees'}
                </span>
                <div className="text-2xl font-bold text-slate-900 mt-0.5">{activeCount}</div>
              </div>
            </div>

            <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <UserX className="w-6 h-6" />
              </div>
              <div>
                <span className="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                  {isArabic ? 'غير نشط / في إجازة' : 'Inactive / Leave'}
                </span>
                <div className="text-2xl font-bold text-slate-900 mt-0.5">{inactiveCount}</div>
              </div>
            </div>

            <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                <Building2 className="w-6 h-6" />
              </div>
              <div>
                <span className="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                  {isArabic ? 'الأقسام المعتمدة' : 'Departments'}
                </span>
                <div className="text-2xl font-bold text-slate-900 mt-0.5">{departments.length}</div>
              </div>
            </div>
          </div>

          {/* Main 2-Column Section */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Recent Employees Table (2 cols) */}
            <div className="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
              <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                  <h2 className="text-base font-bold text-slate-900">{isArabic ? 'أحدث الموظفين' : 'Recent Employees'}</h2>
                  <p className="text-xs text-slate-500">
                    {isArabic ? 'المرحلة 1: الهيكل وقاعدة البيانات مفعّلة' : 'Registered in custom database table'}
                  </p>
                </div>
                <button
                  onClick={() => setActiveTab('employees')}
                  className="text-xs font-semibold text-teal-600 hover:text-teal-700"
                >
                  {isArabic ? 'عرض الكل' : 'View Directory'} &rarr;
                </button>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead className="bg-slate-50 border-b border-slate-100 text-slate-500 font-semibold uppercase">
                    <tr>
                      <th className="px-5 py-3">{isArabic ? 'الموظف' : 'Employee'}</th>
                      <th className="px-4 py-3">{isArabic ? 'الرقم الوظيفي' : 'Employee ID'}</th>
                      <th className="px-4 py-3">{isArabic ? 'القسم والمسمى' : 'Department'}</th>
                      <th className="px-4 py-3">{isArabic ? 'الحالة' : 'Status'}</th>
                      <th className="px-4 py-3 text-right">{isArabic ? 'الإجراءات' : 'Actions'}</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100 text-slate-700">
                    {employees.slice(0, 4).map((emp) => (
                      <tr key={emp.id} className="hover:bg-slate-50/50">
                        <td className="px-5 py-3">
                          <div className="flex items-center gap-3">
                            <div className="w-8 h-8 rounded-full bg-teal-100 text-teal-700 font-bold text-xs flex items-center justify-center shrink-0">
                              {emp.first_name[0]}
                              {emp.last_name[0]}
                            </div>
                            <div>
                              <div className="font-semibold text-slate-900">{emp.full_name}</div>
                              <div className="text-[11px] text-slate-400">{emp.email}</div>
                            </div>
                          </div>
                        </td>
                        <td className="px-4 py-3">
                          <span className="font-mono bg-slate-100 px-2 py-0.5 rounded text-[11px] font-medium border border-slate-200">
                            {emp.employee_id}
                          </span>
                        </td>
                        <td className="px-4 py-3">
                          <div className="font-medium text-slate-800">{emp.department_name}</div>
                          <div className="text-[11px] text-slate-400">{emp.position_title}</div>
                        </td>
                        <td className="px-4 py-3">
                          <span
                            className={`inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold ${
                              emp.employment_status === 'active'
                                ? 'bg-emerald-100 text-emerald-800'
                                : 'bg-amber-100 text-amber-800'
                            }`}
                          >
                            {emp.employment_status.toUpperCase()}
                          </span>
                        </td>
                        <td className="px-4 py-3 text-right">
                          <div className="flex items-center justify-end gap-1.5">
                            <button
                              onClick={() => setViewingEmployee(emp)}
                              className="p-1 rounded hover:bg-slate-100 text-slate-600"
                              title="View Details"
                            >
                              <Eye className="w-3.5 h-3.5" />
                            </button>
                            <button
                              onClick={() => onSelectEmployeeForPortal(emp)}
                              className="p-1 rounded hover:bg-teal-50 text-teal-600"
                              title="Preview Employee Portal as this user"
                            >
                              <ExternalLink className="w-3.5 h-3.5" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

            {/* Audit Log Activity Feed (1 col) */}
            <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4">
              <div className="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 className="text-base font-bold text-slate-900 flex items-center gap-2">
                  <Shield className="w-4 h-4 text-teal-600" />
                  <span>{isArabic ? 'سجل التدقيق الأخير' : 'Recent HR Audit Logs'}</span>
                </h2>
                <button
                  onClick={() => setActiveTab('audit')}
                  className="text-xs font-semibold text-teal-600 hover:text-teal-700"
                >
                  {isArabic ? 'السجل الكامل' : 'All'} &rarr;
                </button>
              </div>

              <div className="space-y-3">
                {auditLogs.slice(0, 4).map((log) => (
                  <div key={log.id} className="flex items-start gap-3 text-xs pb-2 border-b border-slate-50 last:border-0">
                    <div className="w-2 h-2 rounded-full bg-teal-500 mt-1.5 shrink-0" />
                    <div className="flex-1">
                      <div className="font-semibold text-slate-800">
                        {log.action.replace(/_/g, ' ').toUpperCase()}
                      </div>
                      <div className="text-slate-500 text-[11px] mt-0.5">
                        {log.actor_name} &bull; {log.entity_type} #{log.entity_id}
                      </div>
                      <div className="text-[10px] text-slate-400 font-mono mt-0.5">{log.created_at}</div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* TAB 2: EMPLOYEE DIRECTORY */}
      {activeTab === 'employees' && (
        <div className="space-y-4">
          {/* Filters Bar */}
          <div className="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
            <div className="relative w-full md:w-72">
              <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder={isArabic ? 'البحث بالاسم أو البريد أو الرقم...' : 'Search by name, email, ID...'}
                className="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-teal-500"
              />
            </div>

            <div className="flex items-center gap-2 w-full md:w-auto">
              <select
                value={deptFilter}
                onChange={(e) => setDeptFilter(Number(e.target.value))}
                className="text-xs bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-slate-700"
              >
                <option value={0}>{isArabic ? 'كافة الأقسام' : 'All Departments'}</option>
                {departments.map((d) => (
                  <option key={d.id} value={d.id}>
                    {d.name}
                  </option>
                ))}
              </select>

              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="text-xs bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-slate-700"
              >
                <option value="">{isArabic ? 'كافة الحالات' : 'All Statuses'}</option>
                <option value="active">{isArabic ? 'نشط (Active)' : 'Active'}</option>
                <option value="inactive">{isArabic ? 'غير نشط (Inactive)' : 'Inactive'}</option>
              </select>
            </div>
          </div>

          {/* Directory Table */}
          <div className="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div className="px-5 py-3 bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500">
              {isArabic ? `عرض ${filteredEmployees.length} موظف` : `Showing ${filteredEmployees.length} employee(s)`}
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-semibold uppercase text-[11px]">
                  <tr>
                    <th className="px-5 py-3">{isArabic ? 'الموظف' : 'Employee'}</th>
                    <th className="px-4 py-3">{isArabic ? 'الرقم الوظيفي' : 'Employee ID'}</th>
                    <th className="px-4 py-3">{isArabic ? 'القسم والمسمى' : 'Department & Title'}</th>
                    <th className="px-4 py-3">{isArabic ? 'الاتصال' : 'Contact'}</th>
                    <th className="px-4 py-3">{isArabic ? 'تاريخ التعيين' : 'Hire Date'}</th>
                    <th className="px-4 py-3">{isArabic ? 'الحالة' : 'Status'}</th>
                    <th className="px-4 py-3 text-right">{isArabic ? 'الإجراءات' : 'Actions'}</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-slate-700">
                  {filteredEmployees.map((emp) => (
                    <tr key={emp.id} className="hover:bg-slate-50/70 transition-colors">
                      <td className="px-5 py-3.5">
                        <div className="flex items-center gap-3">
                          <div className="w-9 h-9 rounded-full bg-teal-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                            {emp.first_name[0]}
                            {emp.last_name[0]}
                          </div>
                          <div>
                            <div className="font-semibold text-slate-900">{emp.full_name}</div>
                            <div className="text-[11px] text-slate-400">{emp.email}</div>
                            {emp.user_id && (
                              <span className="inline-flex items-center gap-1 text-[10px] text-blue-700 bg-blue-50 px-1.5 py-0.2 rounded mt-0.5">
                                WP User #{emp.user_id}
                              </span>
                            )}
                          </div>
                        </div>
                      </td>
                      <td className="px-4 py-3.5">
                        <span className="font-mono bg-slate-100 px-2 py-0.5 rounded text-[11px] font-semibold text-slate-800 border border-slate-200">
                          {emp.employee_id}
                        </span>
                      </td>
                      <td className="px-4 py-3.5">
                        <div className="font-medium text-slate-800">{emp.department_name}</div>
                        <div className="text-[11px] text-slate-500">{emp.position_title}</div>
                      </td>
                      <td className="px-4 py-3.5">
                        <div className="text-slate-800">{emp.phone}</div>
                        <div className="text-[11px] text-slate-400">ID: {emp.national_id}</div>
                      </td>
                      <td className="px-4 py-3.5 text-slate-600">{emp.hire_date}</td>
                      <td className="px-4 py-3.5">
                        <button
                          onClick={() => onToggleStatus(emp.id)}
                          className={`inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold transition-colors cursor-pointer ${
                            emp.employment_status === 'active'
                              ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                              : 'bg-amber-100 text-amber-800 hover:bg-amber-200'
                          }`}
                          title="Click to toggle status"
                        >
                          {emp.employment_status.toUpperCase()}
                        </button>
                      </td>
                      <td className="px-4 py-3.5 text-right">
                        <div className="flex items-center justify-end gap-1.5">
                          <button
                            onClick={() => setViewingEmployee(emp)}
                            className="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700"
                            title="View Full Profile"
                          >
                            <Eye className="w-3.5 h-3.5" />
                          </button>
                          <button
                            onClick={() => onSelectEmployeeForPortal(emp)}
                            className="p-1.5 rounded-lg border border-slate-200 hover:bg-teal-50 text-teal-700"
                            title="Open Employee Portal"
                          >
                            <ExternalLink className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* TAB 3: AUDIT LOGS */}
      {activeTab === 'audit' && (
        <div className="bg-white border border-slate-200 rounded-xl shadow-sm p-5 space-y-4">
          <div className="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
              <h2 className="text-base font-bold text-slate-900">{isArabic ? 'سجل التدقيق والتغييرات' : 'HR Audit & Compliance Log'}</h2>
              <p className="text-xs text-slate-500">
                {isArabic ? 'سجل ثابت وغير قابل للتعديل لجميع العمليات الحساسة' : 'Immutable audit trail of workforce lifecycle operations'}
              </p>
            </div>
            <span className="text-xs font-mono bg-slate-100 px-2.5 py-1 rounded text-slate-600">
              Table: wp_nds_hr_audit_logs
            </span>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-semibold">
                <tr>
                  <th className="px-4 py-2.5">ID</th>
                  <th className="px-4 py-2.5">{isArabic ? 'التاريخ والوقت' : 'Timestamp'}</th>
                  <th className="px-4 py-2.5">{isArabic ? 'العملية' : 'Action'}</th>
                  <th className="px-4 py-2.5">{isArabic ? 'الكيان' : 'Entity'}</th>
                  <th className="px-4 py-2.5">{isArabic ? 'المستخدم المنفذ' : 'Actor'}</th>
                  <th className="px-4 py-2.5">{isArabic ? 'عنوان IP' : 'IP Address'}</th>
                  <th className="px-4 py-2.5">{isArabic ? 'البيانات' : 'Details / Payload'}</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {auditLogs.map((log) => (
                  <tr key={log.id} className="hover:bg-slate-50/60">
                    <td className="px-4 py-3 font-mono text-slate-400">#{log.id}</td>
                    <td className="px-4 py-3 text-slate-600 font-mono text-[11px]">{log.created_at}</td>
                    <td className="px-4 py-3">
                      <span className="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                        {log.action}
                      </span>
                    </td>
                    <td className="px-4 py-3">
                      <span className="font-semibold text-slate-900">{log.entity_type}</span> #{log.entity_id}
                    </td>
                    <td className="px-4 py-3">{log.actor_name}</td>
                    <td className="px-4 py-3 font-mono text-[11px] text-slate-500">{log.ip_address}</td>
                    <td className="px-4 py-3">
                      <pre className="text-[10px] font-mono bg-slate-100 p-1.5 rounded max-w-xs overflow-x-auto text-slate-800">
                        {JSON.stringify(log.new_values || {}, null, 1)}
                      </pre>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* TAB 4: ARCHITECTURAL BLUEPRINT */}
      {activeTab === 'blueprint' && (
        <div className="bg-white border border-slate-200 rounded-xl shadow-sm p-6 space-y-6">
          <div className="border-b border-slate-100 pb-4">
            <h2 className="text-lg font-bold text-slate-900">
              {isArabic ? 'خارطة الطريق المعمارية للنظام' : 'NDS HR Multi-Phase Architectural Blueprint'}
            </h2>
            <p className="text-xs text-slate-500 mt-1">
              {isArabic
                ? 'تم تصميم قاعدة البيانات وهيكل الأدوار البرمجية لتستوعب جميع المراحل القادمة بسلاسة تامة دون الحاجة لإعادة كتابة الأكواد.'
                : 'Schema and role structure pre-architected for zero-rewrite upgrades across Phase 2 through Phase 6.'}
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="border-2 border-emerald-500 bg-emerald-50/30 rounded-xl p-4 flex flex-col justify-between">
              <div>
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-bold tracking-wider uppercase text-emerald-700">Phase 1</span>
                  <span className="bg-emerald-100 text-emerald-800 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                    Completed
                  </span>
                </div>
                <h3 className="text-sm font-bold text-slate-900 mt-2">Foundation & Employee Mgmt</h3>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Dedicated custom DB tables, RBAC, WP user sync, Employee ID generator, and self-service portal.
                </p>
              </div>
              <div className="mt-4 pt-3 border-t border-emerald-200 text-[11px] text-emerald-800 font-semibold">
                &bull; wp_nds_hr_employees<br />
                &bull; wp_nds_hr_departments<br />
                &bull; wp_nds_hr_positions<br />
                &bull; wp_nds_hr_audit_logs
              </div>
            </div>

            <div className="border border-slate-200 bg-slate-50 rounded-xl p-4 flex flex-col justify-between">
              <div>
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-bold tracking-wider uppercase text-slate-500">Phase 2</span>
                  <span className="bg-slate-200 text-slate-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                    Scheduled
                  </span>
                </div>
                <h3 className="text-sm font-bold text-slate-900 mt-2">Attendance & Time Tracking</h3>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Daily check-in / check-out, shifts, work hours, overtime calculation, and geolocation logs.
                </p>
              </div>
              <div className="mt-4 pt-3 border-t border-slate-200 text-[11px] text-slate-500 font-mono">
                &bull; wp_nds_hr_attendance<br />
                &bull; wp_nds_hr_shifts
              </div>
            </div>

            <div className="border border-slate-200 bg-slate-50 rounded-xl p-4 flex flex-col justify-between">
              <div>
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-bold tracking-wider uppercase text-slate-500">Phase 3</span>
                  <span className="bg-slate-200 text-slate-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                    Scheduled
                  </span>
                </div>
                <h3 className="text-sm font-bold text-slate-900 mt-2">Leave Management</h3>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Annual, sick, and maternity leaves, yearly balances, and multi-tier approval workflow.
                </p>
              </div>
              <div className="mt-4 pt-3 border-t border-slate-200 text-[11px] text-slate-500 font-mono">
                &bull; wp_nds_hr_leaves<br />
                &bull; wp_nds_hr_leave_types<br />
                &bull; wp_nds_hr_leave_balances
              </div>
            </div>

            <div className="border border-slate-200 bg-slate-50 rounded-xl p-4 flex flex-col justify-between">
              <div>
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-bold tracking-wider uppercase text-slate-500">Phase 4 & 5</span>
                  <span className="bg-slate-200 text-slate-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                    Scheduled
                  </span>
                </div>
                <h3 className="text-sm font-bold text-slate-900 mt-2">Payroll & Payslips</h3>
                <p className="text-xs text-slate-600 mt-1 leading-relaxed">
                  Salary structures, allowances, deductions, payroll cycles, and printable payslips.
                </p>
              </div>
              <div className="mt-4 pt-3 border-t border-slate-200 text-[11px] text-slate-500 font-mono">
                &bull; wp_nds_hr_payroll<br />
                &bull; wp_nds_hr_salary_structures<br />
                &bull; wp_nds_hr_payslips
              </div>
            </div>
          </div>
        </div>
      )}

      {/* TAB 4: SETTINGS (Phase 1) */}
      {activeTab === 'settings' && (
        <div className="space-y-6">
          {/* Top Notice */}
          {settingsSavedNotice && (
            <div className="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-xs">
              <div className="flex items-center gap-2 text-xs font-semibold">
                <Check className="w-4 h-4 text-teal-600" />
                <span>{settingsSavedNotice}</span>
              </div>
              <button
                onClick={() => setSettingsSavedNotice(null)}
                className="text-teal-600 hover:text-teal-800 text-sm font-bold"
              >
                &times;
              </button>
            </div>
          )}

          <div className="grid grid-cols-1 md:grid-cols-4 gap-6 items-start">
            {/* Left Nav */}
            <div className="bg-white border border-slate-200 rounded-xl p-3 shadow-xs space-y-1">
              <div className="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                {isArabic ? 'وحدات الإعدادات' : 'Configuration Modules'}
              </div>
              {[
                { id: 'general', label: isArabic ? 'الإعدادات العامة' : 'General', desc: 'Company profile & contact' },
                { id: 'employees', label: isArabic ? 'الموظفون' : 'Employees', desc: 'Employee policies' },
                { id: 'attendance', label: isArabic ? 'الحضور والانصراف' : 'Attendance', desc: 'Shifts & timings' },
                { id: 'leave', label: isArabic ? 'الإجازات' : 'Leave', desc: 'Leave types & balances' },
                { id: 'payroll', label: isArabic ? 'الرواتب' : 'Payroll', desc: 'Salary structures' },
                { id: 'notifications', label: isArabic ? 'الإشعارات' : 'Notifications', desc: 'Alerts & emails' },
                { id: 'localization', label: isArabic ? 'اللغة والتعريب' : 'Localization', desc: 'Language & direction' },
                { id: 'security', label: isArabic ? 'الأمان' : 'Security', desc: 'Access controls' },
              ].map((m) => (
                <button
                  key={m.id}
                  onClick={() => setSettingsSection(m.id as any)}
                  className={`w-full text-left px-3 py-2.5 rounded-lg text-xs font-medium transition-colors flex flex-col ${
                    settingsSection === m.id
                      ? 'bg-teal-50 text-teal-700 font-semibold border border-teal-100'
                      : 'text-slate-600 hover:bg-slate-50'
                  }`}
                >
                  <span className="text-slate-900 font-semibold">{m.label}</span>
                  <span className="text-[10px] text-slate-400 font-normal">{m.desc}</span>
                </button>
              ))}
            </div>

            {/* Right Pane */}
            <div className="md:col-span-3 bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
              {settingsSection === 'general' && (
                <div className="space-y-6">
                  <div>
                    <h3 className="text-base font-bold text-slate-900">
                      {isArabic ? 'الإعدادات العامة' : 'General Settings'}
                    </h3>
                    <p className="text-xs text-slate-500 mt-0.5">
                      {isArabic ? 'إدارة تفاصيل المنشأة وبيانات التواصل الرسمية.' : 'Manage your organization profile and official contact details.'}
                    </p>
                  </div>

                  <form
                    onSubmit={(e) => {
                      e.preventDefault();
                      setSettingsSavedNotice(isArabic ? 'تم حفظ الإعدادات العامة بنجاح.' : 'General settings saved successfully.');
                    }}
                    className="space-y-4 max-w-xl text-xs"
                  >
                    <div>
                      <label className="block text-slate-700 font-semibold mb-1">
                        {isArabic ? 'اسم الشركة / المنشأة' : 'Company Name'}
                      </label>
                      <input
                        type="text"
                        value={generalSettings.company_name}
                        onChange={(e) => setGeneralSettings({ ...generalSettings, company_name: e.target.value })}
                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                      />
                    </div>

                    <div>
                      <label className="block text-slate-700 font-semibold mb-1">
                        {isArabic ? 'البريد الإلكتروني الرسمي' : 'Company Email'}
                      </label>
                      <input
                        type="email"
                        value={generalSettings.company_email}
                        onChange={(e) => setGeneralSettings({ ...generalSettings, company_email: e.target.value })}
                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                      />
                    </div>

                    <div>
                      <label className="block text-slate-700 font-semibold mb-1">
                        {isArabic ? 'رقم الهاتف الرسمي' : 'Company Phone'}
                      </label>
                      <input
                        type="text"
                        value={generalSettings.company_phone}
                        onChange={(e) => setGeneralSettings({ ...generalSettings, company_phone: e.target.value })}
                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                      />
                    </div>

                    <div>
                      <label className="block text-slate-700 font-semibold mb-1">
                        {isArabic ? 'العنوان' : 'Company Address'}
                      </label>
                      <textarea
                        rows={3}
                        value={generalSettings.company_address}
                        onChange={(e) => setGeneralSettings({ ...generalSettings, company_address: e.target.value })}
                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                      />
                    </div>

                    <div className="pt-2 flex justify-end">
                      <button
                        type="submit"
                        className="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg shadow-xs transition-colors"
                      >
                        {isArabic ? 'حفظ التغييرات' : 'Save Changes'}
                      </button>
                    </div>
                  </form>
                </div>
              )}

              {settingsSection === 'localization' && (
                <div className="space-y-6">
                  <div>
                    <h3 className="text-base font-bold text-slate-900">
                      {isArabic ? 'إعدادات اللغة والتعريب' : 'Localization Settings'}
                    </h3>
                    <p className="text-xs text-slate-500 mt-0.5">
                      {isArabic ? 'تحديد اللغة الافتراضية واتجاه النصوص للنظام.' : 'Configure default application language and regional presentation preferences.'}
                    </p>
                  </div>

                  <form
                    onSubmit={(e) => {
                      e.preventDefault();
                      setSettingsSavedNotice(isArabic ? 'تم حفظ إعدادات اللغة بنجاح.' : 'Localization settings saved successfully.');
                    }}
                    className="space-y-4 max-w-xl text-xs"
                  >
                    <div>
                      <label className="block text-slate-700 font-semibold mb-1">
                        {isArabic ? 'اللغة الافتراضية' : 'Default Language'}
                      </label>
                      <select
                        value={localizationSettings.default_language}
                        onChange={(e) => setLocalizationSettings({ default_language: e.target.value })}
                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white"
                      >
                        <option value="en">English (LTR)</option>
                        <option value="ar">العربية — Arabic (RTL)</option>
                      </select>
                      <p className="text-[11px] text-slate-500 mt-1">
                        {isArabic ? 'تحدد هذه القيمة اللغة الافتراضية للمستخدمين الجلسات الجديدة.' : 'This setting represents the default language for new user sessions.'}
                      </p>
                    </div>

                    <div className="p-4 bg-slate-50 border border-slate-200 rounded-lg space-y-1">
                      <div className="font-semibold text-slate-800">
                        {isArabic ? 'اتجاه النص (Direction)' : 'Text Direction'}
                      </div>
                      <p className="text-slate-500 text-[11px]">
                        {isArabic ? 'يتم ضبط الاتجاه تلقائيًا بناءً على اللغة المختارة (LTR للإنجليزية و RTL للعربية).' : 'Direction is automatic based on the selected language (LTR for English, RTL for Arabic).'}
                      </p>
                    </div>

                    <div className="pt-2 flex justify-end">
                      <button
                        type="submit"
                        className="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg shadow-xs transition-colors"
                      >
                        {isArabic ? 'حفظ التغييرات' : 'Save Changes'}
                      </button>
                    </div>
                  </form>
                </div>
              )}

              {settingsSection === 'security' && (
                <div className="text-center py-12 space-y-3">
                  <div className="w-12 h-12 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center mx-auto">
                    <Shield className="w-6 h-6" />
                  </div>
                  <h4 className="text-base font-bold text-slate-800">
                    {isArabic ? 'إعدادات الأمان' : 'Security Settings'}
                  </h4>
                  <p className="text-xs text-slate-500 max-w-sm mx-auto">
                    {isArabic
                      ? 'ستتوفر إعدادات الأمان وسياسات الجلسات في إصدار قادم.'
                      : 'Security settings will be available in a future release.'}
                  </p>
                </div>
              )}

              {['employees', 'attendance', 'leave', 'payroll', 'notifications'].includes(settingsSection) && (
                <div className="text-center py-12 space-y-3">
                  <div className="w-12 h-12 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center mx-auto">
                    <Clock className="w-6 h-6" />
                  </div>
                  <h4 className="text-base font-bold text-slate-800 capitalize">
                    {settingsSection} {isArabic ? 'الإعدادات' : 'Settings'}
                  </h4>
                  <span className="inline-block bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                    {isArabic ? 'لم تُهيأ بعد' : 'Not Configured Yet'}
                  </span>
                  <p className="text-xs text-slate-500 max-w-sm mx-auto">
                    {isArabic
                      ? 'سيتم تمكين خيارات التهيئة وسياسات العمل لهذه الوحدة في إصدار قادم.'
                      : `Configuration parameters and business policies for the ${settingsSection} module will be available in an upcoming release.`}
                  </p>
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {/* MODAL: ADD EMPLOYEE */}
      {isAddModalOpen && (
        <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto">
          <div className="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center">
                  <Plus className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-slate-900">
                    {isArabic ? 'إضافة موظف جديد' : 'Add New Employee'}
                  </h3>
                  <span className="text-xs font-mono text-teal-700 bg-teal-50 px-2 py-0.5 rounded">
                    {generatedId}
                  </span>
                </div>
              </div>
              <button
                onClick={() => setIsAddModalOpen(false)}
                className="text-slate-400 hover:text-slate-600 p-1"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSubmitNewEmployee} className="p-6 space-y-5 text-xs">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-slate-700 font-semibold mb-1">
                    {isArabic ? 'الاسم الأول *' : 'First Name *'}
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.first_name}
                    onChange={(e) => setFormData({ ...formData, first_name: e.target.value })}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                    placeholder="e.g. Abdullah"
                  />
                </div>
                <div>
                  <label className="block text-slate-700 font-semibold mb-1">
                    {isArabic ? 'اسم العائلة *' : 'Last Name *'}
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.last_name}
                    onChange={(e) => setFormData({ ...formData, last_name: e.target.value })}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                    placeholder="e.g. Al-Otaibi"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-slate-700 font-semibold mb-1">
                    {isArabic ? 'البريد الإلكتروني للعمل *' : 'Work Email *'}
                  </label>
                  <input
                    type="email"
                    required
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                    placeholder="abdullah.otaibi@nds-hr.corp"
                  />
                </div>
                <div>
                  <label className="block text-slate-700 font-semibold mb-1">
                    {isArabic ? 'رقم الهاتف' : 'Phone / Mobile'}
                  </label>
                  <input
                    type="text"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                    placeholder="+966 50 123 4567"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-slate-700 font-semibold mb-1">
                    {isArabic ? 'القسم' : 'Department'}
                  </label>
                  <select
                    value={formData.department_id}
                    onChange={(e) => setFormData({ ...formData, department_id: Number(e.target.value) })}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                  >
                    {departments.map((d) => (
                      <option key={d.id} value={d.id}>
                        {d.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-slate-700 font-semibold mb-1">
                    {isArabic ? 'المسمى الوظيفي' : 'Position / Title'}
                  </label>
                  <select
                    value={formData.position_id}
                    onChange={(e) => setFormData({ ...formData, position_id: Number(e.target.value) })}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                  >
                    {positions.map((p) => (
                      <option key={p.id} value={p.id}>
                        {p.title}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-slate-700 font-semibold mb-1">
                    {isArabic ? 'تاريخ التعيين' : 'Hire Date'}
                  </label>
                  <input
                    type="date"
                    value={formData.hire_date}
                    onChange={(e) => setFormData({ ...formData, hire_date: e.target.value })}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block text-slate-700 font-semibold mb-1">
                    {isArabic ? 'الهوية الوطنية / الإقامة' : 'National ID / Iqama'}
                  </label>
                  <input
                    type="text"
                    value={formData.national_id}
                    onChange={(e) => setFormData({ ...formData, national_id: e.target.value })}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                    placeholder="1092837465"
                  />
                </div>
              </div>

              {/* WordPress Account Integration */}
              <div className="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-3">
                <div className="font-semibold text-slate-900 flex items-center justify-between">
                  <div className="flex items-center gap-1.5">
                    <User className="w-4 h-4 text-teal-600" />
                    <span>{isArabic ? 'حساب الدخول لووردبريس وبوابة الموظف' : 'Employee Account Onboarding'}</span>
                  </div>
                  <span className="text-[10px] text-teal-800 bg-teal-100 font-semibold px-2 py-0.5 rounded">
                    {isArabic ? 'تهيئة الحساب' : 'Account Setup'}
                  </span>
                </div>

                <div className="space-y-2">
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      name="account_action"
                      value="create"
                      checked={formData.account_action === 'create'}
                      onChange={() => setFormData({ ...formData, account_action: 'create' })}
                      className="text-teal-600"
                    />
                    <span className="font-medium text-slate-900">
                      {isArabic ? 'إنشاء حساب مستخدم جديد وتعيين دور hr_employee' : 'Create new WordPress login account'}
                    </span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      name="account_action"
                      value="none"
                      checked={formData.account_action === 'none'}
                      onChange={() => setFormData({ ...formData, account_action: 'none' })}
                      className="text-teal-600"
                    />
                    <span>{isArabic ? 'عدم إنشاء حساب مستخدم حالياً' : 'Do not create account now'}</span>
                  </label>
                </div>

                {formData.account_action === 'create' && (
                  <div className="pt-3 border-t border-slate-200 space-y-3">
                    {/* Username */}
                    <div>
                      <label className="block text-slate-700 font-semibold mb-1">
                        {isArabic ? 'اسم المستخدم للدخول *' : 'Username *'}
                      </label>
                      <input
                        type="text"
                        value={formData.account_username}
                        onChange={(e) => setFormData({ ...formData, account_username: e.target.value })}
                        className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none font-mono text-xs"
                        placeholder={formData.email ? formData.email.split('@')[0].toLowerCase() : 'e.g. ahmed.mansoor'}
                      />
                      <span className="text-[11px] text-slate-500 mt-0.5 block">
                        {isArabic ? 'يُستخدم للدخول إلى /login/' : 'Used by employee to authenticate at /login/'}
                      </span>
                    </div>

                    {/* Password Method Selector */}
                    <div>
                      <label className="block text-slate-700 font-semibold mb-1.5">
                        {isArabic ? 'طريقة تعيين كلمة المرور' : 'Password Option'}
                      </label>
                      <div className="grid grid-cols-2 gap-2">
                        <button
                          type="button"
                          onClick={() => {
                            const gen = generatePasswordString();
                            setFormData({
                              ...formData,
                              password_mode: 'generate',
                              password: gen,
                              confirm_password: gen,
                              show_password: true,
                            });
                          }}
                          className={`py-1.5 px-3 rounded-lg text-xs font-semibold border flex items-center justify-center gap-1.5 transition-colors ${
                            formData.password_mode === 'generate'
                              ? 'bg-teal-600 text-white border-teal-600'
                              : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'
                          }`}
                        >
                          <Key className="w-3.5 h-3.5" />
                          <span>{isArabic ? 'توليد كلمة مرور آمنة' : 'Generate Secure'}</span>
                        </button>

                        <button
                          type="button"
                          onClick={() => {
                            setFormData({
                              ...formData,
                              password_mode: 'manual',
                              password: '',
                              confirm_password: '',
                            });
                          }}
                          className={`py-1.5 px-3 rounded-lg text-xs font-semibold border flex items-center justify-center gap-1.5 transition-colors ${
                            formData.password_mode === 'manual'
                              ? 'bg-teal-600 text-white border-teal-600'
                              : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'
                          }`}
                        >
                          <Edit2 className="w-3.5 h-3.5" />
                          <span>{isArabic ? 'إدخال كلمة مرور يدوياً' : 'Enter Manually'}</span>
                        </button>
                      </div>
                    </div>

                    {/* Password Inputs */}
                    <div className="space-y-2">
                      <div>
                        <div className="flex items-center justify-between mb-1">
                          <label className="text-slate-700 font-semibold">
                            {isArabic ? 'كلمة المرور *' : 'Password *'}
                          </label>
                          <button
                            type="button"
                            onClick={() => {
                              const gen = generatePasswordString();
                              setFormData({
                                ...formData,
                                password: gen,
                                confirm_password: gen,
                                show_password: true,
                              });
                            }}
                            className="text-teal-700 hover:text-teal-800 text-[11px] underline font-medium"
                          >
                            {isArabic ? 'إعادة التوليد' : 'Generate Password'}
                          </button>
                        </div>
                        <div className="relative">
                          <input
                            type={formData.show_password ? 'text' : 'password'}
                            value={formData.password}
                            onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                            className="w-full px-3 py-2 pr-16 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none font-mono text-xs"
                            placeholder={isArabic ? 'اتركه فارغاً للتوليد التلقائي' : 'Leave empty to auto-generate'}
                          />
                          <button
                            type="button"
                            onClick={() => setFormData({ ...formData, show_password: !formData.show_password })}
                            className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700 text-[11px] px-1.5 py-0.5 rounded flex items-center gap-1"
                          >
                            {formData.show_password ? (
                              <>
                                <EyeOff className="w-3 h-3" />
                                <span>Hide</span>
                              </>
                            ) : (
                              <>
                                <Eye className="w-3 h-3" />
                                <span>Show</span>
                              </>
                            )}
                          </button>
                        </div>
                      </div>

                      {formData.password_mode === 'manual' && (
                        <div>
                          <label className="block text-slate-700 font-semibold mb-1">
                            {isArabic ? 'تأكيد كلمة المرور *' : 'Confirm Password *'}
                          </label>
                          <input
                            type={formData.show_password ? 'text' : 'password'}
                            value={formData.confirm_password}
                            onChange={(e) => setFormData({ ...formData, confirm_password: e.target.value })}
                            className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none font-mono text-xs"
                            placeholder={isArabic ? 'أعد إدخال كلمة المرور' : 'Repeat password'}
                          />
                          {formData.password && formData.confirm_password && (
                            <span
                              className={`text-[11px] font-medium mt-1 block ${
                                formData.password === formData.confirm_password ? 'text-emerald-700' : 'text-red-600'
                              }`}
                            >
                              {formData.password === formData.confirm_password
                                ? '✓ Passwords match'
                                : '✗ Passwords do not match'}
                            </span>
                          )}
                        </div>
                      )}
                    </div>

                    {/* Copy Generated Credentials button if password exists */}
                    {formData.password && (
                      <div className="bg-emerald-50 border border-emerald-200 rounded-lg p-2.5 flex items-center justify-between gap-2">
                        <div className="text-[11px] text-emerald-900 font-mono truncate">
                          PWD: <span className="font-bold">{formData.password}</span>
                        </div>
                        <button
                          type="button"
                          onClick={() => {
                            const uname = formData.account_username || formData.email.split('@')[0].toLowerCase();
                            const txt = `Username: ${uname}\nPassword: ${formData.password}`;
                            navigator.clipboard.writeText(txt);
                          }}
                          className="px-2.5 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 text-emerald-800 rounded text-[11px] font-semibold flex items-center gap-1 shrink-0"
                        >
                          <Copy className="w-3 h-3" />
                          <span>{isArabic ? 'نسخ الحساب' : 'Copy Credentials'}</span>
                        </button>
                      </div>
                    )}

                    {/* Require password change on first login */}
                    <div className="pt-2">
                      <label className="flex items-start gap-2 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={formData.require_password_change}
                          onChange={(e) => setFormData({ ...formData, require_password_change: e.target.checked })}
                          className="mt-0.5 text-teal-600 rounded"
                        />
                        <span className="text-xs text-slate-800 leading-tight">
                          <strong>{isArabic ? 'إلزام تغيير كلمة المرور عند أول تسجيل دخول' : 'Require password change on first login'}</strong>
                          <span className="block text-[11px] text-slate-500 font-normal mt-0.5">
                            {isArabic
                              ? 'سيُطلب من الموظف تعيين كلمة مرور شخصية خاصة فور تسجيل الدخول في /login/.'
                              : 'Employee will be prompted to set a private password immediately after logging in at /login/.'}
                          </span>
                        </span>
                      </label>
                    </div>
                  </div>
                )}
              </div>

              <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setIsAddModalOpen(false)}
                  className="px-4 py-2 border border-slate-300 rounded-lg font-semibold text-slate-700 hover:bg-slate-50"
                >
                  {isArabic ? 'إلغاء' : 'Cancel'}
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg shadow-xs"
                >
                  {isArabic ? 'حفظ وتثبيت الموظف' : 'Save Employee'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* DRAWER: VIEW EMPLOYEE PROFILE */}
      {viewingEmployee && (
        <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-end p-4">
          <div className="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full h-[95vh] overflow-y-auto p-6 space-y-6">
            <div className="flex items-center justify-between pb-4 border-b border-slate-100">
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 rounded-full bg-teal-600 text-white font-bold text-base flex items-center justify-center">
                  {viewingEmployee.first_name[0]}
                  {viewingEmployee.last_name[0]}
                </div>
                <div>
                  <h3 className="text-lg font-bold text-slate-900">{viewingEmployee.full_name}</h3>
                  <div className="flex items-center gap-2 mt-0.5">
                    <span className="font-mono text-xs text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                      {viewingEmployee.employee_id}
                    </span>
                    <span
                      className={`text-[10px] font-semibold px-2 py-0.5 rounded-full ${
                        viewingEmployee.employment_status === 'active'
                          ? 'bg-emerald-100 text-emerald-800'
                          : 'bg-amber-100 text-amber-800'
                      }`}
                    >
                      {viewingEmployee.employment_status.toUpperCase()}
                    </span>
                  </div>
                </div>
              </div>
              <button
                onClick={() => setViewingEmployee(null)}
                className="text-slate-400 hover:text-slate-600 p-1"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Profile Fields */}
            <div className="space-y-4 text-xs">
              <div>
                <h4 className="font-bold text-slate-800 uppercase tracking-wide text-[11px] mb-2 text-teal-700">
                  {isArabic ? 'البيانات المهنية والتنظيمية' : 'Employment & Job Details'}
                </h4>
                <div className="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-lg border border-slate-100">
                  <div>
                    <span className="text-slate-400 block">{isArabic ? 'القسم' : 'Department'}</span>
                    <strong className="text-slate-900">{viewingEmployee.department_name}</strong>
                  </div>
                  <div>
                    <span className="text-slate-400 block">{isArabic ? 'المسمى الوظيفي' : 'Position'}</span>
                    <strong className="text-slate-900">{viewingEmployee.position_title}</strong>
                  </div>
                  <div>
                    <span className="text-slate-400 block">{isArabic ? 'تاريخ التعيين' : 'Hire Date'}</span>
                    <strong className="text-slate-900">{viewingEmployee.hire_date}</strong>
                  </div>
                  <div>
                    <span className="text-slate-400 block">{isArabic ? 'حساب ووردبريس' : 'WordPress User'}</span>
                    <strong className="text-slate-900">
                      {viewingEmployee.user_id ? `User #${viewingEmployee.user_id}` : 'None'}
                    </strong>
                  </div>
                </div>
              </div>

              <div>
                <h4 className="font-bold text-slate-800 uppercase tracking-wide text-[11px] mb-2 text-teal-700">
                  {isArabic ? 'البيانات الشخصية وبيانات الاتصال' : 'Personal & Contact Details'}
                </h4>
                <div className="space-y-2 bg-slate-50 p-3 rounded-lg border border-slate-100">
                  <div>
                    <span className="text-slate-400 block">{isArabic ? 'البريد الإلكتروني' : 'Email'}</span>
                    <span className="text-slate-900 font-medium">{viewingEmployee.email}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block">{isArabic ? 'الهاتف' : 'Phone / Mobile'}</span>
                    <span className="text-slate-900 font-medium">{viewingEmployee.phone}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block">{isArabic ? 'الهوية الوطنية' : 'National ID'}</span>
                    <span className="text-slate-900 font-medium">{viewingEmployee.national_id}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block">{isArabic ? 'العنوان' : 'Address'}</span>
                    <span className="text-slate-900 font-medium">{viewingEmployee.address}</span>
                  </div>
                </div>
              </div>

              <div>
                <h4 className="font-bold text-slate-800 uppercase tracking-wide text-[11px] mb-2 text-teal-700">
                  {isArabic ? 'بيانات الاتصال في حالات الطوارئ' : 'Emergency Contact'}
                </h4>
                <div className="bg-slate-50 p-3 rounded-lg border border-slate-100 space-y-1">
                  <div>
                    <strong>{viewingEmployee.emergency_contact_name}</strong> ({viewingEmployee.emergency_contact_relationship})
                  </div>
                  <div className="text-slate-600">{viewingEmployee.emergency_contact_phone}</div>
                </div>
              </div>
            </div>

            <div className="pt-4 border-t border-slate-100 flex gap-2">
              <button
                onClick={() => {
                  onSelectEmployeeForPortal(viewingEmployee);
                  setViewingEmployee(null);
                }}
                className="flex-1 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-lg flex items-center justify-center gap-1.5"
              >
                <ExternalLink className="w-3.5 h-3.5" />
                <span>{isArabic ? 'عرض بوابة الموظف كـ هذا المستخدم' : 'Open Portal As Employee'}</span>
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
