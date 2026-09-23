import React, { useState } from 'react';
import { Employee } from '../types';
import {
  LayoutDashboard,
  User,
  Clock,
  Calendar,
  FileText,
  LogOut,
  Smartphone,
  Tablet,
  Monitor,
  CheckCircle2,
  AlertTriangle,
  Building,
  Briefcase,
  CalendarDays,
  ShieldCheck,
  ChevronRight,
  ArrowLeft,
  ChevronLeft,
} from 'lucide-react';

interface PortalViewProps {
  currentEmployee: Employee;
  allEmployees: Employee[];
  onSwitchEmployee: (emp: Employee) => void;
  isArabic: boolean;
  onToggleLanguage: () => void;
}

export const PortalView: React.FC<PortalViewProps> = ({
  currentEmployee,
  allEmployees,
  onSwitchEmployee,
  isArabic,
  onToggleLanguage,
}) => {
  const [portalTab, setPortalTab] = useState<'dashboard' | 'profile' | 'attendance' | 'leaves' | 'payroll'>('dashboard');
  const [deviceFrame, setDeviceFrame] = useState<'desktop' | 'tablet' | 'mobile'>('desktop');

  // Calculate tenure
  const hireDate = new Date(currentEmployee.hire_date);
  const now = new Date();
  const diffDays = Math.floor((now.getTime() - hireDate.getTime()) / (1000 * 3600 * 24));
  const tenureYears = Math.floor(diffDays / 365);
  const tenureMonths = Math.floor((diffDays % 365) / 30);

  const getContainerWidth = () => {
    switch (deviceFrame) {
      case 'mobile':
        return 'max-w-sm';
      case 'tablet':
        return 'max-w-2xl';
      default:
        return 'w-full';
    }
  };

  return (
    <div className="space-y-4">
      {/* Device Toolbar & Employee Selector */}
      <div className="bg-white border border-slate-200 rounded-xl p-3 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
        <div className="flex items-center gap-2">
          <span className="font-semibold text-slate-500">
            {isArabic ? 'معاينة بوابة الموظف بصلاحيات:' : 'Logged In As:'}
          </span>
          <select
            value={currentEmployee.id}
            onChange={(e) => {
              const selected = allEmployees.find((emp) => emp.id === Number(e.target.value));
              if (selected) onSwitchEmployee(selected);
            }}
            className="bg-slate-100 border border-slate-200 rounded-lg px-2.5 py-1 font-semibold text-teal-800"
          >
            {allEmployees.map((emp) => (
              <option key={emp.id} value={emp.id}>
                {emp.full_name} ({emp.employee_id} - {emp.position_title})
              </option>
            ))}
          </select>
        </div>

        {/* Device Switcher */}
        <div className="flex items-center gap-2">
          <div className="bg-slate-100 p-1 rounded-lg flex items-center gap-1 border border-slate-200">
            <button
              onClick={() => setDeviceFrame('desktop')}
              className={`p-1.5 rounded transition-colors ${deviceFrame === 'desktop' ? 'bg-white text-teal-700 shadow-xs' : 'text-slate-500 hover:text-slate-900'}`}
              title="Desktop View"
            >
              <Monitor className="w-3.5 h-3.5" />
            </button>
            <button
              onClick={() => setDeviceFrame('tablet')}
              className={`p-1.5 rounded transition-colors ${deviceFrame === 'tablet' ? 'bg-white text-teal-700 shadow-xs' : 'text-slate-500 hover:text-slate-900'}`}
              title="Tablet View"
            >
              <Tablet className="w-3.5 h-3.5" />
            </button>
            <button
              onClick={() => setDeviceFrame('mobile')}
              className={`p-1.5 rounded transition-colors ${deviceFrame === 'mobile' ? 'bg-white text-teal-700 shadow-xs' : 'text-slate-500 hover:text-slate-900'}`}
              title="Mobile View"
            >
              <Smartphone className="w-3.5 h-3.5" />
            </button>
          </div>

          <button
            onClick={onToggleLanguage}
            className="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg border border-slate-200"
          >
            {isArabic ? 'English (LTR)' : 'العربية (RTL)'}
          </button>
        </div>
      </div>

      {/* Simulated Browser Frame / Portal Shell */}
      <div className="flex justify-center transition-all">
        <div
          className={`${getContainerWidth()} bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-slate-800 transition-all`}
          dir={isArabic ? 'rtl' : 'ltr'}
        >
          {/* Top Browser Bar */}
          <div className="bg-slate-950 px-4 py-2 flex items-center justify-between border-b border-slate-800 text-[11px] text-slate-400">
            <div className="flex items-center gap-1.5">
              <span className="w-2.5 h-2.5 rounded-full bg-red-500/80 inline-block" />
              <span className="w-2.5 h-2.5 rounded-full bg-amber-500/80 inline-block" />
              <span className="w-2.5 h-2.5 rounded-full bg-emerald-500/80 inline-block" />
            </div>
            <div className="bg-slate-900 border border-slate-800 px-3 py-0.5 rounded text-slate-300 font-mono text-[10px]">
              https://company.corp/employee/?tab={portalTab}
            </div>
            <div className="text-[10px] text-emerald-400 font-medium">SSL Secure &bull; Auth</div>
          </div>

          {/* Portal Application Body */}
          <div className="min-h-[580px] bg-slate-50 flex flex-col md:flex-row text-slate-800">
            {/* Sidebar */}
            <aside className="w-full md:w-60 bg-slate-900 text-slate-200 p-5 flex flex-col justify-between shrink-0">
              <div className="space-y-6">
                {/* Brand */}
                <div className="flex items-center gap-2.5">
                  <div className="w-8 h-8 rounded-lg bg-teal-600 text-white font-bold text-sm flex items-center justify-center">
                    NDS
                  </div>
                  <div>
                    <div className="font-bold text-white text-sm">NDS HR</div>
                    <div className="text-[10px] text-slate-400">{isArabic ? 'بوابة الموظف' : 'Employee Portal'}</div>
                  </div>
                </div>

                {/* Logged in Employee Mini Card */}
                <div className="bg-slate-800/80 p-3 rounded-xl border border-slate-700/60 flex items-center gap-3">
                  <div className="w-9 h-9 rounded-full bg-teal-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                    {currentEmployee.first_name[0]}
                    {currentEmployee.last_name[0]}
                  </div>
                  <div className="overflow-hidden">
                    <div className="font-semibold text-white text-xs truncate">{currentEmployee.full_name}</div>
                    <div className="text-[10px] font-mono text-teal-300">{currentEmployee.employee_id}</div>
                    <div className="text-[10px] text-slate-400 truncate">{currentEmployee.position_title}</div>
                  </div>
                </div>

                {/* Navigation Links */}
                <nav className="space-y-1 text-xs">
                  <button
                    onClick={() => setPortalTab('dashboard')}
                    className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-colors ${
                      portalTab === 'dashboard' ? 'bg-teal-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800'
                    }`}
                  >
                    <LayoutDashboard className="w-4 h-4" />
                    <span>{isArabic ? 'لوحة الموظف' : 'My Dashboard'}</span>
                  </button>

                  <button
                    onClick={() => setPortalTab('profile')}
                    className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-colors ${
                      portalTab === 'profile' ? 'bg-teal-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800'
                    }`}
                  >
                    <User className="w-4 h-4" />
                    <span>{isArabic ? 'الملف الشخصي' : 'My Profile'}</span>
                  </button>

                  {/* Future Phases Nav Items with clear phase pills */}
                  <button
                    onClick={() => setPortalTab('attendance')}
                    className={`w-full flex items-center justify-between px-3 py-2 rounded-lg font-medium transition-colors ${
                      portalTab === 'attendance' ? 'bg-teal-600 text-white' : 'text-slate-400 hover:bg-slate-800'
                    }`}
                  >
                    <div className="flex items-center gap-2.5">
                      <Clock className="w-4 h-4" />
                      <span>{isArabic ? 'الحضور والانصراف' : 'Attendance'}</span>
                    </div>
                    <span className="text-[9px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-400">P2</span>
                  </button>

                  <button
                    onClick={() => setPortalTab('leaves')}
                    className={`w-full flex items-center justify-between px-3 py-2 rounded-lg font-medium transition-colors ${
                      portalTab === 'leaves' ? 'bg-teal-600 text-white' : 'text-slate-400 hover:bg-slate-800'
                    }`}
                  >
                    <div className="flex items-center gap-2.5">
                      <Calendar className="w-4 h-4" />
                      <span>{isArabic ? 'الإجازات والأرصدة' : 'Leaves'}</span>
                    </div>
                    <span className="text-[9px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-400">P3</span>
                  </button>

                  <button
                    onClick={() => setPortalTab('payroll')}
                    className={`w-full flex items-center justify-between px-3 py-2 rounded-lg font-medium transition-colors ${
                      portalTab === 'payroll' ? 'bg-teal-600 text-white' : 'text-slate-400 hover:bg-slate-800'
                    }`}
                  >
                    <div className="flex items-center gap-2.5">
                      <FileText className="w-4 h-4" />
                      <span>{isArabic ? 'مسيرات الرواتب' : 'Payslips'}</span>
                    </div>
                    <span className="text-[9px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-400">P4</span>
                  </button>
                </nav>
              </div>

              {/* Sidebar Footer */}
              <div className="pt-4 border-t border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                <span>{isArabic ? 'بيئة ووردبريس الآمنة' : 'WP Auth Verified'}</span>
                <span className="text-teal-400">&bull; {currentEmployee.employment_status.toUpperCase()}</span>
              </div>
            </aside>

            {/* Main Content Pane */}
            <main className="flex-1 p-6 space-y-6 overflow-y-auto">
              {/* TAB: DASHBOARD */}
              {portalTab === 'dashboard' && (
                <div className="space-y-6">
                  {/* Welcome Banner */}
                  <div className="bg-gradient-to-r from-teal-800 to-teal-950 text-white p-6 rounded-2xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                      <span className="text-[11px] font-semibold text-teal-200 uppercase tracking-wider">
                        {isArabic ? 'بوابة الموارد البشرية الذاتية' : 'Self-Service Portal'}
                      </span>
                      <h2 className="text-xl font-bold mt-1">
                        {isArabic ? `أهلاً بك، ${currentEmployee.first_name}!` : `Welcome back, ${currentEmployee.first_name}!`}
                      </h2>
                      <p className="text-xs text-teal-100 mt-0.5">
                        {currentEmployee.position_title} &bull; {currentEmployee.department_name}
                      </p>
                    </div>
                    <button
                      onClick={() => setPortalTab('profile')}
                      className="px-4 py-2 bg-white text-teal-900 text-xs font-bold rounded-xl shadow-xs hover:bg-teal-50 transition-colors shrink-0"
                    >
                      {isArabic ? 'عرض ملفي الشخصي' : 'View Full Profile'}
                    </button>
                  </div>

                  {/* 4 KPIs */}
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div className="bg-white border border-slate-200 rounded-xl p-3.5 shadow-2xs">
                      <span className="text-[10px] font-semibold text-slate-400 uppercase">
                        {isArabic ? 'حالة التوظيف' : 'Status'}
                      </span>
                      <div className="mt-1 flex items-center gap-1.5">
                        <span className="w-2 h-2 rounded-full bg-emerald-500" />
                        <span className="text-xs font-bold text-slate-900 capitalize">
                          {currentEmployee.employment_status}
                        </span>
                      </div>
                    </div>

                    <div className="bg-white border border-slate-200 rounded-xl p-3.5 shadow-2xs">
                      <span className="text-[10px] font-semibold text-slate-400 uppercase">
                        {isArabic ? 'الرقم الوظيفي' : 'Employee ID'}
                      </span>
                      <div className="mt-1 font-mono text-xs font-bold text-teal-700">
                        {currentEmployee.employee_id}
                      </div>
                    </div>

                    <div className="bg-white border border-slate-200 rounded-xl p-3.5 shadow-2xs">
                      <span className="text-[10px] font-semibold text-slate-400 uppercase">
                        {isArabic ? 'تاريخ المباشرة' : 'Hire Date'}
                      </span>
                      <div className="mt-1 text-xs font-bold text-slate-900">
                        {currentEmployee.hire_date}
                      </div>
                    </div>

                    <div className="bg-white border border-slate-200 rounded-xl p-3.5 shadow-2xs">
                      <span className="text-[10px] font-semibold text-slate-400 uppercase">
                        {isArabic ? 'فترة الخدمة' : 'Tenure'}
                      </span>
                      <div className="mt-1 text-xs font-bold text-slate-900">
                        {tenureYears > 0 ? `${tenureYears}y ${tenureMonths}m` : `${tenureMonths} months`}
                      </div>
                    </div>
                  </div>

                  {/* Modules Overview */}
                  <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-4">
                    <h3 className="text-sm font-bold text-slate-900">
                      {isArabic ? 'الخدمات الذاتية للموظف' : 'My Self-Service Modules'}
                    </h3>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                      <div className="border border-teal-200 bg-teal-50/50 p-4 rounded-xl flex flex-col justify-between">
                        <div>
                          <div className="flex items-center justify-between">
                            <span className="font-bold text-teal-900">{isArabic ? 'الملف الشخصي والبيانات' : 'Profile & Data'}</span>
                            <span className="text-[10px] font-semibold bg-teal-100 text-teal-800 px-2 py-0.5 rounded-full">
                              Active
                            </span>
                          </div>
                          <p className="text-slate-600 text-[11px] mt-1.5 leading-relaxed">
                            {isArabic
                              ? 'استعراض البيانات الشخصية، الهوية الوطنية، المسمى، الأقسام وبيانات الطوارئ.'
                              : 'Review personal information, corporate details, contact numbers, and emergency contacts.'}
                          </p>
                        </div>
                        <button
                          onClick={() => setPortalTab('profile')}
                          className="mt-3 text-teal-700 font-bold text-[11px] hover:underline flex items-center gap-1"
                        >
                          <span>{isArabic ? 'فتح الملف الشخصي' : 'Access Profile'}</span>
                          {isArabic ? <ChevronLeft className="w-3 h-3" /> : <ChevronRight className="w-3 h-3" />}
                        </button>
                      </div>

                      <div className="border border-slate-200 bg-slate-50/70 p-4 rounded-xl flex flex-col justify-between">
                        <div>
                          <div className="flex items-center justify-between">
                            <span className="font-bold text-slate-800">{isArabic ? 'الحضور وتسجيل الدخول' : 'Attendance'}</span>
                            <span className="text-[10px] font-semibold bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full">
                              Phase 2
                            </span>
                          </div>
                          <p className="text-slate-500 text-[11px] mt-1.5 leading-relaxed">
                            {isArabic
                              ? 'تسجيل الدخول اليومي، ساعات العمل، الإضافي، وسجل الحضور المجدول في المرحلة الثانية.'
                              : 'Daily check-in / check-out, working hours, and timesheet logs scheduled for Phase 2.'}
                          </p>
                        </div>
                        <span className="mt-3 text-[11px] text-slate-400 font-medium">Coming in next release</span>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* TAB: PROFILE */}
              {portalTab === 'profile' && (
                <div className="space-y-5">
                  {/* Hero Card */}
                  <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs flex items-center gap-4">
                    <div className="w-16 h-16 rounded-full bg-teal-600 text-white font-bold text-xl flex items-center justify-center shrink-0">
                      {currentEmployee.first_name[0]}
                      {currentEmployee.last_name[0]}
                    </div>
                    <div>
                      <h2 className="text-lg font-bold text-slate-900">{currentEmployee.full_name}</h2>
                      <div className="flex items-center gap-2 mt-1">
                        <span className="font-mono text-xs bg-slate-100 text-slate-800 px-2 py-0.5 rounded border border-slate-200">
                          {currentEmployee.employee_id}
                        </span>
                        <span className="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full">
                          ACTIVE EMPLOYEE
                        </span>
                      </div>
                    </div>
                  </div>

                  {/* 2-Column Info */}
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-3">
                      <h3 className="font-bold text-slate-900 uppercase text-[11px] tracking-wider text-teal-700">
                        {isArabic ? 'البيانات الشخصية' : 'Personal & Contact Information'}
                      </h3>
                      <div className="space-y-2 text-slate-700">
                        <div>
                          <span className="text-slate-400 block text-[11px]">{isArabic ? 'البريد الإلكتروني' : 'Email'}</span>
                          <span className="font-semibold">{currentEmployee.email}</span>
                        </div>
                        <div>
                          <span className="text-slate-400 block text-[11px]">{isArabic ? 'رقم الهاتف' : 'Phone'}</span>
                          <span className="font-semibold">{currentEmployee.phone}</span>
                        </div>
                        <div>
                          <span className="text-slate-400 block text-[11px]">{isArabic ? 'رقم الهوية الوطنية' : 'National ID'}</span>
                          <span className="font-semibold font-mono">{currentEmployee.national_id}</span>
                        </div>
                        <div>
                          <span className="text-slate-400 block text-[11px]">{isArabic ? 'العنوان' : 'Residential Address'}</span>
                          <span>{currentEmployee.address}</span>
                        </div>
                      </div>
                    </div>

                    <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-3">
                      <h3 className="font-bold text-slate-900 uppercase text-[11px] tracking-wider text-teal-700">
                        {isArabic ? 'البيانات الوظيفية وبيانات الطوارئ' : 'Job & Emergency Contacts'}
                      </h3>
                      <div className="space-y-2 text-slate-700">
                        <div>
                          <span className="text-slate-400 block text-[11px]">{isArabic ? 'القسم' : 'Department'}</span>
                          <span className="font-semibold">{currentEmployee.department_name}</span>
                        </div>
                        <div>
                          <span className="text-slate-400 block text-[11px]">{isArabic ? 'المسمى الوظيفي' : 'Position'}</span>
                          <span className="font-semibold">{currentEmployee.position_title}</span>
                        </div>
                        <div>
                          <span className="text-slate-400 block text-[11px]">{isArabic ? 'تاريخ التعيين' : 'Hire Date'}</span>
                          <span className="font-semibold">{currentEmployee.hire_date}</span>
                        </div>
                        <div className="pt-2 border-t border-slate-100">
                          <span className="text-slate-400 block text-[11px]">{isArabic ? 'جهة الاتصال في الطوارئ' : 'Emergency Contact'}</span>
                          <span className="font-semibold">{currentEmployee.emergency_contact_name} ({currentEmployee.emergency_contact_relationship})</span>
                          <div className="text-slate-500">{currentEmployee.emergency_contact_phone}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* ROADMAP NOTICES FOR FUTURE TABS */}
              {['attendance', 'leaves', 'payroll'].includes(portalTab) && (
                <div className="bg-white border border-slate-200 rounded-2xl p-8 shadow-xs text-center max-w-md mx-auto space-y-4">
                  <div className="w-12 h-12 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center mx-auto">
                    <Clock className="w-6 h-6" />
                  </div>
                  <div>
                    <h3 className="text-base font-bold text-slate-900">
                      {isArabic ? 'الوحدة مجدولة في المرحلة القادمة' : 'Module Scheduled in Next Phase'}
                    </h3>
                    <p className="text-xs text-slate-500 mt-1 leading-relaxed">
                      {isArabic
                        ? `وحدة ${portalTab} مصممة وجاهزة في مخطط قاعدة البيانات وسيتم تفعيلها في مرحلتها المخصصة.`
                        : `The ${portalTab} module is fully pre-architected in the database schema registry and will be introduced in its respective phase.`}
                    </p>
                  </div>
                  <button
                    onClick={() => setPortalTab('dashboard')}
                    className="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-lg shadow-xs"
                  >
                    {isArabic ? 'العودة للوحة الموظف' : 'Return to Dashboard'}
                  </button>
                </div>
              )}
            </main>
          </div>
        </div>
      </div>
    </div>
  );
};
