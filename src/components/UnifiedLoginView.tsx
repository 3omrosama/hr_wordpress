import React, { useState } from 'react';
import {
  Lock,
  ArrowRight,
  ShieldCheck,
  CheckCircle2,
  AlertCircle,
  Sparkles,
  UserPlus,
  KeyRound,
  LogIn,
} from 'lucide-react';

interface UnifiedLoginViewProps {
  onSimulateLogin: (username: string, simulatedRole: string, destination: string) => void;
  isArabic: boolean;
}

export const UnifiedLoginView: React.FC<UnifiedLoginViewProps> = ({
  onSimulateLogin,
  isArabic,
}) => {
  const [subView, setSubView] = useState<'login' | 'setup' | 'reset'>('login');
  const [username, setUsername] = useState<string>('tariq.admin');
  const [password, setPassword] = useState<string>('••••••••••••');
  const [rememberMe, setRememberMe] = useState<boolean>(true);
  const [selectedQuickRole, setSelectedQuickRole] = useState<'admin' | 'manager' | 'officer' | 'employee'>('admin');
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  // Setup Form State
  const [setupUsername, setSetupUsername] = useState('admin.hr');
  const [setupEmail, setSetupEmail] = useState('admin@company.com');
  const [setupPassword, setSetupPassword] = useState('SecurePass2026!');
  const [setupSuccess, setSetupSuccess] = useState(false);

  // Reset Form State
  const [resetIdentifier, setResetIdentifier] = useState('employee@company.com');
  const [resetSuccess, setResetSuccess] = useState(false);

  const quickRoles = [
    {
      id: 'admin',
      label: isArabic ? 'مدير نظام / HR Admin' : 'HR Administrator',
      roleSlug: 'nds_hr_admin',
      login: 'tariq.admin',
      destination: 'admin',
      desc: isArabic
        ? 'يمتلك nds_hr_access_admin. يُوجَّه إلى /hr/'
        : 'Has nds_hr_access_admin. Routes straight to /hr/',
    },
    {
      id: 'manager',
      label: isArabic ? 'مدير الموارد / Manager' : 'HR Manager',
      roleSlug: 'nds_hr_manager',
      login: 'fatima.manager',
      destination: 'admin',
      desc: isArabic
        ? 'يمتلك nds_hr_access_admin. يُوجَّه إلى لوحة تحكم /hr/'
        : 'Has nds_hr_access_admin. Routes straight to /hr/',
    },
    {
      id: 'officer',
      label: isArabic ? 'مسؤول موارد / Officer' : 'HR Officer',
      roleSlug: 'nds_hr_officer',
      login: 'fahad.officer',
      destination: 'admin',
      desc: isArabic
        ? 'يمتلك nds_hr_access_admin. يُوجَّه إلى /hr/ للقراءة'
        : 'Has nds_hr_access_admin. Routes to /hr/ read-only',
    },
    {
      id: 'employee',
      label: isArabic ? 'موظف / Employee' : 'Employee (Self-Service)',
      roleSlug: 'nds_hr_employee',
      login: 'sarah.jenkins',
      destination: 'portal',
      desc: isArabic
        ? 'يمتلك nds_hr_access_employee فقط. يُوجَّه مباشرة إلى /employee/'
        : 'Has nds_hr_access_employee only. Strictly routed to /employee/',
    },
  ];

  const handleSelectRole = (rId: 'admin' | 'manager' | 'officer' | 'employee') => {
    setSelectedQuickRole(rId);
    const roleObj = quickRoles.find((r) => r.id === rId);
    if (roleObj) {
      setUsername(roleObj.login);
    }
  };

  const handleLoginSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!username.trim()) {
      setErrorMessage(isArabic ? 'يرجى إدخال اسم المستخدم أو البريد الإلكتروني' : 'Please enter your username or email address');
      return;
    }

    const currentRole = quickRoles.find((r) => r.id === selectedQuickRole) || quickRoles[0];
    onSimulateLogin(username, currentRole.roleSlug, currentRole.destination);
  };

  const handleSetupSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setSetupSuccess(true);
    setTimeout(() => {
      onSimulateLogin(setupUsername, 'nds_hr_admin', 'admin');
    }, 1500);
  };

  const handleResetSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setResetSuccess(true);
  };

  return (
    <div className="py-6 flex flex-col items-center justify-center">
      {/* Route Switcher Tabs */}
      <div className="flex items-center gap-2 mb-4 bg-white p-1 rounded-xl shadow-xs border border-slate-200">
        <button
          onClick={() => setSubView('login')}
          className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all ${
            subView === 'login' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <LogIn className="w-3.5 h-3.5" />
          <span>{isArabic ? 'دخول /hr/login/' : 'Login (/hr/login/)'}</span>
        </button>

        <button
          onClick={() => setSubView('setup')}
          className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all ${
            subView === 'setup' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <UserPlus className="w-3.5 h-3.5" />
          <span>{isArabic ? 'تهيئة أولية /hr/setup/' : 'Setup (/hr/setup/)'}</span>
        </button>

        <button
          onClick={() => setSubView('reset')}
          className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all ${
            subView === 'reset' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <KeyRound className="w-3.5 h-3.5" />
          <span>{isArabic ? 'استعادة /hr/reset-password/' : 'Reset (/hr/reset-password/)'}</span>
        </button>
      </div>

      {/* Simulation Info Banner */}
      <div className="max-w-md w-full mb-4 bg-teal-50 border border-teal-200/80 rounded-xl p-3 text-xs text-teal-900 flex items-start gap-2.5">
        <Sparkles className="w-4 h-4 text-teal-600 flex-shrink-0 mt-0.5" />
        <div>
          <strong className="block text-teal-950 font-bold mb-0.5">
            {subView === 'setup'
              ? isArabic ? 'مسار الإعداد الأولي (/hr/setup/):' : 'Primary HR Administrator Setup (/hr/setup/):'
              : subView === 'reset'
              ? isArabic ? 'مسار استعادة كلمة المرور المشفر (/hr/reset-password/):' : 'Secure Token-Based Reset (/hr/reset-password/):'
              : isArabic ? 'تسجيل الدخول المستقل (/hr/login/):' : 'Independent Authentication Gateway (/hr/login/):'}
          </strong>
          <span>
            {subView === 'setup'
              ? isArabic
                ? 'يتم تشغيل هذا المسار عند تثبيت النظام لإنشاء حساب المدير الأساسي في جدول wp_nds_hr_users دون المساس بمستخدمي ووردبريس.'
                : 'Runs when no HR admin exists. Creates primary administrator in wp_nds_hr_users independently of WordPress.'
              : subView === 'reset'
              ? isArabic
                ? 'توليد رموز عشوائية آمنة مع تخزين تجزئة SHA-256 وصلاحية لمدة ساعة واحدة.'
                : 'Generates secure 256-bit reset tokens with SHA-256 hashed storage and 1-hour expiration.'
              : isArabic
              ? 'مصادقة كاملة ومستقلة مع فحص الصلاحيات والتوجيه التلقائي إلى /hr/ أو /employee/.'
              : 'Direct authentication against wp_nds_hr_users with cryptographic session cookies and capability-based routing.'}
          </span>
        </div>
      </div>

      {/* LOGIN CARD */}
      {subView === 'login' && (
        <div className="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200/90 p-8">
          <div className="flex items-center justify-center gap-3 mb-6">
            <div className="w-11 h-11 rounded-xl bg-teal-600 text-white font-black text-lg flex items-center justify-center shadow-xs">
              NDS
            </div>
            <div>
              <div className="text-xl font-extrabold text-slate-900 tracking-tight">NDS HR</div>
              <div className="text-[10px] uppercase font-bold tracking-wider text-teal-700">
                {isArabic ? 'نظام الموارد البشرية المستقل' : 'Standalone HR System'}
              </div>
            </div>
          </div>

          <div className="text-center mb-6">
            <h2 className="text-base font-bold text-slate-900">
              {isArabic ? 'تسجيل الدخول' : 'Enterprise Sign In'}
            </h2>
            <p className="text-xs text-slate-500 mt-1">
              {isArabic
                ? 'أدخل بيانات اعتماد NDS HR للوصول إلى مساحة العمل الخاصة بك'
                : 'Sign in to access your HR workspace or employee self-service portal.'}
            </p>
          </div>

          {errorMessage && (
            <div className="mb-4 bg-rose-50 border border-rose-200 rounded-lg p-3 text-xs text-rose-800 flex items-center gap-2">
              <AlertCircle className="w-4 h-4 text-rose-600 flex-shrink-0" />
              <span>{errorMessage}</span>
            </div>
          )}

          {/* Quick Role Switcher */}
          <div className="mb-5 bg-slate-50 border border-slate-200 rounded-xl p-3">
            <label className="block text-[11px] font-bold text-slate-700 mb-2">
              {isArabic ? 'اختر دوراً لاختبار التوجيه التلقائي:' : 'Select persona to test capability routing:'}
            </label>
            <div className="grid grid-cols-2 gap-2">
              {quickRoles.map((r) => (
                <button
                  key={r.id}
                  type="button"
                  onClick={() => handleSelectRole(r.id as any)}
                  className={`text-left p-2 rounded-lg border text-xs transition-all ${
                    selectedQuickRole === r.id
                      ? 'bg-teal-600 text-white border-teal-600 font-semibold shadow-xs'
                      : 'bg-white text-slate-700 border-slate-200 hover:border-teal-300'
                  }`}
                >
                  <div className="font-bold text-[11px]">{r.label}</div>
                  <div className={`text-[10px] truncate ${selectedQuickRole === r.id ? 'text-teal-100' : 'text-slate-400'}`}>
                    {r.destination === 'admin' ? '→ /hr/' : '→ /employee/'}
                  </div>
                </button>
              ))}
            </div>
          </div>

          {/* Canonical Form */}
          <form onSubmit={handleLoginSubmit} className="space-y-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                {isArabic ? 'اسم المستخدم أو البريد الإلكتروني' : 'Username or Email Address'}
              </label>
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100 transition-all"
                required
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                {isArabic ? 'كلمة المرور' : 'Password'}
              </label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100 transition-all"
                required
              />
            </div>

            <div className="flex items-center justify-between text-xs">
              <label className="flex items-center gap-2 text-slate-600 cursor-pointer">
                <input
                  type="checkbox"
                  checked={rememberMe}
                  onChange={(e) => setRememberMe(e.target.checked)}
                  className="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300"
                />
                <span>{isArabic ? 'تذكرني' : 'Remember Me'}</span>
              </label>

              <button
                type="button"
                onClick={() => setSubView('reset')}
                className="text-teal-600 hover:text-teal-700 font-semibold"
              >
                {isArabic ? 'نسيت كلمة المرور؟' : 'Forgot Password?'}
              </button>
            </div>

            <button
              type="submit"
              className="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 px-4 rounded-lg text-sm shadow-xs transition-colors flex items-center justify-center gap-2"
            >
              <span>{isArabic ? 'تسجيل الدخول إلى NDS HR' : 'Sign In to NDS HR'}</span>
              <ArrowRight className="w-4 h-4" />
            </button>
          </form>

          <div className="mt-6 pt-5 border-t border-slate-100 text-center">
            <div className="inline-flex items-center gap-1.5 text-[11px] text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
              <ShieldCheck className="w-3.5 h-3.5 text-teal-600" />
              <span>
                {isArabic ? 'الوجهة الموجه إليها:' : 'Target Route:'}{' '}
                <strong className="text-slate-800">
                  {selectedQuickRole === 'employee' ? '/employee/ (Portal)' : '/hr/ (HR Admin)'}
                </strong>
              </span>
            </div>
          </div>
        </div>
      )}

      {/* SETUP CARD */}
      {subView === 'setup' && (
        <div className="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200/90 p-8">
          <div className="text-center mb-6">
            <div className="w-12 h-12 rounded-xl bg-teal-600 text-white flex items-center justify-center mx-auto mb-3 shadow-xs">
              <UserPlus className="w-6 h-6" />
            </div>
            <h2 className="text-lg font-bold text-slate-900">
              {isArabic ? 'تهيئة مدير النظام الأساسي' : 'Initial HR Admin Setup'}
            </h2>
            <p className="text-xs text-slate-500 mt-1">
              {isArabic
                ? 'إنشاء حساب المدير الرئيسي الأول للنظام المستقل (/hr/setup/)'
                : 'Create the primary root administrator account for NDS HR.'}
            </p>
          </div>

          {setupSuccess ? (
            <div className="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
              <CheckCircle2 className="w-8 h-8 text-emerald-600 mx-auto mb-2" />
              <div className="text-sm font-bold text-emerald-900">
                {isArabic ? 'تم إنشاء الحساب وتسجيل الدخول بنجاح!' : 'Admin Account Created Successfully!'}
              </div>
              <div className="text-xs text-emerald-700 mt-1">
                {isArabic ? 'جاري التحويل إلى /hr/ ...' : 'Redirecting to /hr/ management workspace...'}
              </div>
            </div>
          ) : (
            <form onSubmit={handleSetupSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                  {isArabic ? 'اسم المستخدم' : 'Admin Username'}
                </label>
                <input
                  type="text"
                  value={setupUsername}
                  onChange={(e) => setSetupUsername(e.target.value)}
                  className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:border-teal-600"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                  {isArabic ? 'البريد الإلكتروني' : 'Admin Email'}
                </label>
                <input
                  type="email"
                  value={setupEmail}
                  onChange={(e) => setSetupEmail(e.target.value)}
                  className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:border-teal-600"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                  {isArabic ? 'كلمة المرور القوية' : 'Master Password'}
                </label>
                <input
                  type="password"
                  value={setupPassword}
                  onChange={(e) => setSetupPassword(e.target.value)}
                  className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:border-teal-600"
                  required
                />
              </div>

              <button
                type="submit"
                className="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 px-4 rounded-lg text-sm shadow-xs transition-colors flex items-center justify-center gap-2"
              >
                <span>{isArabic ? 'إكمال التهيئة وبدء الاستخدام' : 'Complete Setup & Enter /hr/'}</span>
                <ArrowRight className="w-4 h-4" />
              </button>
            </form>
          )}
        </div>
      )}

      {/* RESET CARD */}
      {subView === 'reset' && (
        <div className="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200/90 p-8">
          <div className="text-center mb-6">
            <div className="w-12 h-12 rounded-xl bg-teal-600 text-white flex items-center justify-center mx-auto mb-3 shadow-xs">
              <KeyRound className="w-6 h-6" />
            </div>
            <h2 className="text-lg font-bold text-slate-900">
              {isArabic ? 'استعادة كلمة المرور' : 'Reset Password'}
            </h2>
            <p className="text-xs text-slate-500 mt-1">
              {isArabic
                ? 'إرسال رابط آمن ومشفّر لتعيين كلمة المرور الجديدة (/hr/reset-password/)'
                : 'Request a secure SHA-256 cryptographic reset link.'}
            </p>
          </div>

          {resetSuccess ? (
            <div className="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
              <CheckCircle2 className="w-8 h-8 text-emerald-600 mx-auto mb-2" />
              <div className="text-sm font-bold text-emerald-900">
                {isArabic ? 'تم إرسال رابط الاستعادة!' : 'Reset Link Dispatched!'}
              </div>
              <div className="text-xs text-emerald-700 mt-1">
                {isArabic
                  ? 'إذا كان البريد مسجلاً في wp_nds_hr_users ستصلك رسالة تحتوي على رابط صالح لمدة ساعة.'
                  : 'If the account exists in wp_nds_hr_users, a link expiring in 1 hour has been generated.'}
              </div>
              <button
                onClick={() => {
                  setResetSuccess(false);
                  setSubView('login');
                }}
                className="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-teal-700 hover:text-teal-800"
              >
                <span>{isArabic ? 'العودة لتسجيل الدخول' : 'Back to Login'}</span>
              </button>
            </div>
          ) : (
            <form onSubmit={handleResetSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                  {isArabic ? 'اسم المستخدم أو البريد المسجل' : 'Registered Username or Email'}
                </label>
                <input
                  type="text"
                  value={resetIdentifier}
                  onChange={(e) => setResetIdentifier(e.target.value)}
                  className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:border-teal-600"
                  required
                />
              </div>

              <button
                type="submit"
                className="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 px-4 rounded-lg text-sm shadow-xs transition-colors flex items-center justify-center gap-2"
              >
                <span>{isArabic ? 'إرسال رابط الاستعادة' : 'Send Reset Link'}</span>
                <ArrowRight className="w-4 h-4" />
              </button>

              <div className="text-center pt-2">
                <button
                  type="button"
                  onClick={() => setSubView('login')}
                  className="text-xs text-slate-500 hover:text-teal-700 font-semibold"
                >
                  {isArabic ? 'العودة لتسجيل الدخول' : 'Cancel and return to sign in'}
                </button>
              </div>
            </form>
          )}
        </div>
      )}
    </div>
  );
};
