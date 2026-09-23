import React, { useState } from 'react';
import { RoleMeta, RoleCapabilityGroup } from '../types';
import {
  Shield,
  ShieldCheck,
  RotateCcw,
  Save,
  CheckCircle2,
  Info,
  Layers,
  Users,
  Lock,
} from 'lucide-react';

interface RolesPermissionsViewProps {
  roles: RoleMeta[];
  capabilityGroups: RoleCapabilityGroup[];
  matrix: Record<string, Record<string, boolean>>;
  onSaveMatrix: (newMatrix: Record<string, Record<string, boolean>>) => void;
  onResetMatrix: () => void;
  isArabic: boolean;
}

export const RolesPermissionsView: React.FC<RolesPermissionsViewProps> = ({
  roles,
  capabilityGroups,
  matrix: initialMatrix,
  onSaveMatrix,
  onResetMatrix,
  isArabic,
}) => {
  const [currentMatrix, setCurrentMatrix] = useState<Record<string, Record<string, boolean>>>(initialMatrix);
  const [hasChanges, setHasChanges] = useState<boolean>(false);
  const [successBanner, setSuccessBanner] = useState<string | null>(null);

  const handleToggleCapability = (roleSlug: string, capSlug: string) => {
    // Administrator locked capabilities cannot be toggled off
    if (
      roleSlug === 'administrator' &&
      ['nds_hr_access_admin', 'nds_hr_manage_roles', 'nds_hr_manage_settings'].includes(capSlug)
    ) {
      return;
    }

    const updated = {
      ...currentMatrix,
      [roleSlug]: {
        ...currentMatrix[roleSlug],
        [capSlug]: !currentMatrix[roleSlug]?.[capSlug],
      },
    };

    setCurrentMatrix(updated);
    setHasChanges(true);
    setSuccessBanner(null);
  };

  const handleSave = () => {
    onSaveMatrix(currentMatrix);
    setHasChanges(false);
    setSuccessBanner(
      isArabic
        ? 'تم حفظ مصفوفة الصلاحيات ومزامنتها بنجاح مع أدوار ووردبريس.'
        : 'Role capabilities matrix saved and synchronized successfully.'
    );
    setTimeout(() => setSuccessBanner(null), 5000);
  };

  const handleReset = () => {
    if (
      window.confirm(
        isArabic
          ? 'هل أنت متأكد من رغبتك في إعادة تعيين الصلاحيات للوضع الافتراضي؟'
          : 'Are you sure you want to reset all role permissions to factory defaults?'
      )
    ) {
      onResetMatrix();
      setHasChanges(false);
      setSuccessBanner(
        isArabic
          ? 'تمت إعادة تعيين الأدوار والصلاحيات إلى الإعدادات الافتراضية.'
          : 'Roles and permissions have been reset to default installation presets.'
      );
      setTimeout(() => setSuccessBanner(null), 5000);
    }
  };

  return (
    <div className="space-y-6">
      {/* Header Bar */}
      <div className="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-200">
            <Shield className="w-5 h-5" />
          </div>
          <div>
            <h1 className="text-lg font-bold text-slate-900">
              {isArabic ? 'إدارة الأدوار والصلاحيات' : 'Roles & Permissions Management'}
            </h1>
            <p className="text-xs text-slate-500">
              {isArabic
                ? 'التحكم الدقيق في صلاحيات ومسارات النظام لكل دور وظيفي'
                : 'Centralized capability-based access control engine. Roles map directly to discrete privileges.'}
            </p>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={handleReset}
            className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 transition-colors"
          >
            <RotateCcw className="w-3.5 h-3.5" />
            <span>{isArabic ? 'إعادة ضبط للافتراضي' : 'Reset to Defaults'}</span>
          </button>

          <button
            onClick={handleSave}
            disabled={!hasChanges}
            className={`inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold rounded-lg shadow-xs transition-all ${
              hasChanges
                ? 'bg-teal-600 hover:bg-teal-700 text-white cursor-pointer'
                : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed'
            }`}
          >
            <Save className="w-3.5 h-3.5" />
            <span>{isArabic ? 'حفظ الصلاحيات' : 'Save Changes'}</span>
          </button>
        </div>
      </div>

      {/* Success Notification */}
      {successBanner && (
        <div className="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 text-emerald-800 text-xs flex items-center gap-2">
          <CheckCircle2 className="w-4 h-4 text-emerald-600 flex-shrink-0" />
          <span>{successBanner}</span>
        </div>
      )}

      {/* Role Cards Overview */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        {roles.map((role) => (
          <div
            key={role.slug}
            className="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between"
          >
            <div>
              <div className="flex items-center justify-between mb-2">
                <div className="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                  <ShieldCheck className="w-4 h-4" />
                </div>
                {role.is_system ? (
                  <span className="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                    {isArabic ? 'نظامي' : 'System'}
                  </span>
                ) : (
                  <span className="text-[10px] font-bold uppercase tracking-wider bg-teal-50 text-teal-700 px-2 py-0.5 rounded border border-teal-200">
                    {isArabic ? 'إدارة الموارد' : 'HR Managed'}
                  </span>
                )}
              </div>
              <h3 className="font-bold text-sm text-slate-900 leading-tight mb-0.5">{role.name}</h3>
              <code className="text-[10px] text-slate-400 block mb-2">{role.slug}</code>
              <p className="text-xs text-slate-500 leading-relaxed mb-3">{role.description}</p>
            </div>

            <div className="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
              <span className="flex items-center gap-1 text-[11px]">
                <Users className="w-3 h-3 text-slate-400" />
                <strong>{role.assigned_users_count}</strong> {isArabic ? 'مستخدم' : 'Users'}
              </span>
              <span
                className={`text-[10px] font-semibold ${
                  currentMatrix[role.slug]?.nds_hr_access_admin
                    ? 'text-teal-700'
                    : 'text-amber-700'
                }`}
              >
                {currentMatrix[role.slug]?.nds_hr_access_admin
                  ? isArabic
                    ? 'وصول لوحة الإدارة'
                    : 'wp-admin'
                  : isArabic
                  ? 'بوابة الموظف فقط'
                  : 'Portal Only'}
              </span>
            </div>
          </div>
        ))}
      </div>

      {/* Architectural Callout: Authorization Rule */}
      <div className="bg-slate-900 text-slate-300 rounded-xl p-4 border border-slate-800 text-xs flex items-start gap-3">
        <Info className="w-5 h-5 text-teal-400 flex-shrink-0 mt-0.5" />
        <div>
          <span className="font-bold text-white block mb-0.5">
            {isArabic ? 'القاعدة المعمارية لصلاحيات NDS HR:' : 'NDS HR Authorization Architecture:'}
          </span>
          <p className="leading-relaxed">
            {isArabic
              ? 'لا تعتمد الصلاحيات على أسماء الأدوار. يتم التحقق عبر (Role -> Capabilities -> Permission Check -> Access). يتم توجيه المستخدمين تلقائياً بعد تسجيل الدخول الموحد وفقاً للقدرات الممنوحة أدناه.'
              : 'Authorization is capability-first (Role → Capabilities → NDS_HR_Permissions::can() → Route). Post-login routing inspects nds_hr_access_admin and nds_hr_access_employee rather than hardcoded role names.'}
          </p>
        </div>
      </div>

      {/* Permission Matrix Table */}
      <div className="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div className="p-4 sm:p-5 border-b border-slate-200/80 flex items-center justify-between">
          <div>
            <h2 className="text-sm font-bold text-slate-900">
              {isArabic ? 'مصفوفة الصلاحيات حسب الوحدات' : 'Capability Matrix by Module'}
            </h2>
            <p className="text-xs text-slate-500">
              {isArabic
                ? 'انقر على خانات الاختيار لمنح أو سحب الصلاحيات المحددة.'
                : 'Toggle checkboxes to grant or revoke specific privileges. Administrators retain critical recovery controls.'}
            </p>
          </div>
          {hasChanges && (
            <span className="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
              {isArabic ? 'تغييرات غير محفوظة' : 'Unsaved Changes'}
            </span>
          )}
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs border-collapse">
            <thead>
              <tr className="bg-slate-50 border-b border-slate-200">
                <th className="py-3 px-4 font-bold text-slate-700 min-w-[280px]">
                  {isArabic ? 'الصلاحية / الامتياز' : 'Capability / Privilege'}
                </th>
                {roles.map((role) => (
                  <th key={role.slug} className="py-3 px-3 text-center min-w-[140px]">
                    <div className="font-bold text-slate-800 text-xs">{role.name}</div>
                    <code className="text-[10px] text-slate-400 font-normal">{role.slug}</code>
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {capabilityGroups.map((group) => (
                <React.Fragment key={group.id}>
                  {/* Category Header Row */}
                  <tr className="bg-slate-100/70 border-t border-b border-slate-200/80">
                    <td colSpan={roles.length + 1} className="py-2.5 px-4">
                      <div className="flex items-center gap-2 font-bold text-[11px] uppercase tracking-wider text-teal-800">
                        <Layers className="w-3.5 h-3.5 text-teal-600" />
                        <span>{group.label}</span>
                      </div>
                    </td>
                  </tr>

                  {/* Capability Rows */}
                  {group.capabilities.map((cap) => (
                    <tr key={cap.slug} className="hover:bg-slate-50/70 transition-colors">
                      <td className="py-3 px-4">
                        <div className="font-semibold text-slate-800 text-xs">{cap.label}</div>
                        <div className="text-[11px] text-slate-500 leading-tight mb-0.5">
                          {cap.description}
                        </div>
                        <code className="text-[10px] text-slate-400 bg-slate-100 px-1 py-0.5 rounded">
                          {cap.slug}
                        </code>
                      </td>

                      {roles.map((role) => {
                        const isGranted = !!currentMatrix[role.slug]?.[cap.slug];
                        const isLocked =
                          role.slug === 'administrator' &&
                          ['nds_hr_access_admin', 'nds_hr_manage_roles', 'nds_hr_manage_settings'].includes(
                            cap.slug
                          );

                        return (
                          <td key={role.slug} className="py-3 px-3 text-center align-middle">
                            <label className="inline-flex items-center justify-center cursor-pointer p-1">
                              <input
                                type="checkbox"
                                checked={isGranted}
                                disabled={isLocked}
                                onChange={() => handleToggleCapability(role.slug, cap.slug)}
                                className={`w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 ${
                                  isLocked ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer'
                                }`}
                              />
                            </label>
                            {isLocked && (
                              <span title="Administrator capability locked to prevent lockout" className="block text-[9px] text-slate-400 mt-0.5">
                                <Lock className="w-2.5 h-2.5 inline" />
                              </span>
                            )}
                          </td>
                        );
                      })}
                    </tr>
                  ))}
                </React.Fragment>
              ))}
            </tbody>
          </table>
        </div>

        {/* Footer Actions */}
        <div className="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
          <button
            onClick={handleReset}
            className="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg hover:bg-slate-200 transition-colors"
          >
            {isArabic ? 'إلغاء' : 'Reset'}
          </button>
          <button
            onClick={handleSave}
            disabled={!hasChanges}
            className={`inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold rounded-lg shadow-xs transition-all ${
              hasChanges
                ? 'bg-teal-600 hover:bg-teal-700 text-white cursor-pointer'
                : 'bg-slate-200 text-slate-400 cursor-not-allowed'
            }`}
          >
            <Save className="w-3.5 h-3.5" />
            <span>{isArabic ? 'حفظ الصلاحيات' : 'Save Capabilities'}</span>
          </button>
        </div>
      </div>
    </div>
  );
};
