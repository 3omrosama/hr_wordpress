/**
 * TypeScript Interfaces for NDS HR Preview Showcase
 */

export interface Employee {
  id: number;
  employee_id: string;
  user_id?: number | null;
  first_name: string;
  last_name: string;
  full_name: string;
  email: string;
  phone: string;
  mobile: string;
  national_id: string;
  date_of_birth: string;
  gender: 'male' | 'female' | 'other';
  hire_date: string;
  department_id: number;
  department_name: string;
  position_id: number;
  position_title: string;
  employment_status: 'active' | 'inactive' | 'terminated' | 'suspended';
  basic_salary: number;
  emergency_contact_name: string;
  emergency_contact_phone: string;
  emergency_contact_relationship: string;
  address: string;
  profile_photo_url?: string;
  created_at: string;
}

export interface Department {
  id: number;
  name: string;
  code: string;
}

export interface Position {
  id: number;
  department_id: number;
  title: string;
}

export interface AuditLog {
  id: number;
  user_id: number;
  actor_name: string;
  action: string;
  entity_type: string;
  entity_id: number;
  old_values: Record<string, any> | null;
  new_values: Record<string, any> | null;
  ip_address: string;
  created_at: string;
}

export interface RoleCapabilityGroup {
  id: string;
  label: string;
  capabilities: {
    slug: string;
    label: string;
    description: string;
  }[];
}

export interface RoleMeta {
  slug: string;
  name: string;
  description: string;
  is_system: boolean;
  assigned_users_count: number;
}

export interface CreatedCredentialsInfo {
  employeeId: string;
  username: string;
  temporaryPassword: string;
  requirePasswordChange: boolean;
}
