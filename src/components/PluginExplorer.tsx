import React, { useState } from 'react';
import {
  Download,
  Folder,
  FileCode,
  FileText,
  Copy,
  Check,
  Server,
  Layers,
  ShieldAlert,
  Database,
  Terminal,
  ExternalLink,
} from 'lucide-react';

interface PluginFile {
  path: string;
  name: string;
  category: 'core' | 'database' | 'security' | 'module' | 'admin' | 'portal' | 'template';
  description: string;
  codeSnippet: string;
}

const PLUGIN_FILES: PluginFile[] = [
  {
    path: 'nds-hr/nds-hr.php',
    name: 'nds-hr.php (Plugin Bootstrap)',
    category: 'core',
    description: 'Main plugin entry point with activation hooks, autoloader, and dependency checks.',
    codeSnippet: `<?php
/**
 * Plugin Name:       NDS HR
 * Plugin URI:        https://example.com/nds-hr
 * Description:       Enterprise HR Management System for WordPress.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            NDS HR Engineering Team
 * Text Domain:       nds-hr
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NDS_HR_VERSION', '1.0.0' );
define( 'NDS_HR_DB_VERSION', '1.0.0' );
define( 'NDS_HR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NDS_HR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

register_activation_hook( __FILE__, array( 'NDS_HR_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'NDS_HR_Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'NDS_HR_Plugin', 'get_instance' ) );`,
  },
  {
    path: 'nds-hr/includes/class-database.php',
    name: 'class-database.php (Custom DB Tables)',
    category: 'database',
    description: 'Defines dbDelta schemas for custom tables: employees, departments, positions, audit_logs.',
    codeSnippet: `<?php
class NDS_HR_Database {
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // 1. Employees Table
        $table_employees = $wpdb->prefix . 'nds_hr_employees';
        $sql_employees = "CREATE TABLE $table_employees (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned DEFAULT NULL,
            employee_id varchar(50) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(150) NOT NULL,
            employment_status enum('active','inactive','terminated','suspended') NOT NULL DEFAULT 'active',
            hire_date date NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY employee_id (employee_id),
            UNIQUE KEY email (email),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_employees );
    }
}`,
  },
  {
    path: 'nds-hr/includes/class-roles.php',
    name: 'class-roles.php (Granular RBAC)',
    category: 'security',
    description: 'Registers custom WordPress roles: hr_manager, hr_officer, hr_employee with strict caps.',
    codeSnippet: `<?php
class NDS_HR_Roles {
    public static function register_roles() {
        // 1. HR Manager Role
        add_role( 'hr_manager', 'HR Manager', array(
            'read'                       => true,
            'manage_nds_hr'              => true,
            'view_nds_hr_dashboard'      => true,
            'manage_nds_hr_employees'    => true,
            'create_nds_hr_employees'    => true,
            'edit_nds_hr_employees'      => true,
            'delete_nds_hr_employees'    => true,
            'view_nds_hr_audit_logs'     => true,
        ) );

        // 2. HR Employee Role (Self-Service Portal Only)
        add_role( 'hr_employee', 'HR Employee', array(
            'read'                       => true,
            'access_nds_hr_portal'       => true,
            'view_nds_hr_self_profile'   => true,
            'edit_nds_hr_self_profile'   => false,
        ) );
    }
}`,
  },
  {
    path: 'nds-hr/modules/employees/class-employee-service.php',
    name: 'class-employee-service.php (Business Logic)',
    category: 'module',
    description: 'Handles business transactions, WP user auto-creation/linking, validation, and audit logging.',
    codeSnippet: `<?php
class NDS_HR_Employee_Service {
    public function create_employee( array $raw_data, $account_action = 'none', $account_username = '' ) {
        // 1. Permission check
        if ( ! NDS_HR_Permissions::can_create_employees() ) {
            return new WP_Error( 'forbidden', 'Insufficient capabilities.' );
        }

        // 2. Sanitize & Validate input
        $data = NDS_HR_Security::sanitize_employee_input( $raw_data );

        // 3. Generate Sequential Employee ID
        if ( empty( $data['employee_id'] ) ) {
            $data['employee_id'] = $this->repository->generate_next_employee_id();
        }

        // 4. Handle WordPress User Creation
        if ( 'create' === $account_action ) {
            $user_id = wp_create_user( $username, wp_generate_password(), $data['email'] );
            $user = new WP_User( $user_id );
            $user->set_role( 'hr_employee' );
            $data['user_id'] = $user_id;
        }

        // 5. Insert Record & Write Audit Log
        $emp_id = $this->repository->create( $data );
        NDS_HR_Audit_Logger::log( 'employee_created', 'employee', $emp_id, null, $data );
        return $emp_id;
    }
}`,
  },
  {
    path: 'nds-hr/employee/class-employee-portal.php',
    name: 'class-employee-portal.php (Virtual Frontend)',
    category: 'portal',
    description: 'Registers virtual URL rewrite rule ^employee/?$, auth guards, and renders self-service UI.',
    codeSnippet: `<?php
class NDS_HR_Employee_Portal {
    public function register_rewrites() {
        add_rewrite_rule(
            '^employee/?$',
            'index.php?nds_hr_employee_portal=1',
            'top'
        );
    }

    public function intercept_template() {
        if ( ! get_query_var( 'nds_hr_employee_portal' ) ) {
            return;
        }

        // Auth guard: Require logged in
        if ( ! is_user_logged_in() ) {
            $this->render_view( 'templates/employee/login-required.php' );
            exit;
        }

        // Must have linked employee record
        $employee = $this->get_current_employee_record();
        if ( ! $employee ) {
            $this->render_view( 'templates/employee/unlinked-account.php' );
            exit;
        }

        // Render Self-Service Portal
        $this->render_view( 'templates/employee/portal-layout.php', array(
            'employee' => $employee
        ) );
        exit;
    }
}`,
  },
];

export const PluginExplorer: React.FC = () => {
  const [selectedFile, setSelectedFile] = useState<PluginFile>(PLUGIN_FILES[0]);
  const [copied, setCopied] = useState(false);

  const handleCopy = () => {
    navigator.clipboard.writeText(selectedFile.codeSnippet);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="space-y-6">
      {/* Header & Download Card */}
      <div className="bg-gradient-to-r from-teal-900 to-slate-900 text-white p-6 rounded-2xl shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6 border border-teal-800/60">
        <div className="space-y-1">
          <div className="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-teal-500/20 text-teal-300 text-xs font-semibold border border-teal-400/30">
            <span className="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse" />
            <span>Ready for Production Deployment</span>
          </div>
          <h2 className="text-xl font-bold">NDS HR — Installable WordPress Plugin Package</h2>
          <p className="text-xs text-slate-300 max-w-2xl leading-relaxed">
            Standard single-plugin ZIP package (`nds-hr.zip`) containing full object-oriented PHP architecture, custom database schemas, granular RBAC, and responsive stylesheets.
          </p>
        </div>

        <a
          href="/nds-hr.zip"
          download="nds-hr.zip"
          className="px-5 py-2.5 bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-2 shrink-0"
        >
          <Download className="w-4 h-4" />
          <span>Download nds-hr.zip (61 KB)</span>
        </a>
      </div>

      {/* 2-Column: File Explorer + Code Viewer */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left: Plugin File Hierarchy */}
        <div className="bg-white border border-slate-200 rounded-xl p-4 shadow-sm space-y-3">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 className="text-xs font-bold text-slate-900 uppercase tracking-wide flex items-center gap-1.5">
              <Folder className="w-4 h-4 text-teal-600" />
              <span>Plugin Architecture Tree</span>
            </h3>
            <span className="text-[10px] text-slate-400 font-mono">100% PHP / WP</span>
          </div>

          <div className="space-y-1 text-xs">
            {PLUGIN_FILES.map((file) => (
              <button
                key={file.path}
                onClick={() => setSelectedFile(file)}
                className={`w-full text-left p-2.5 rounded-lg transition-colors flex items-start gap-2.5 ${
                  selectedFile.path === file.path
                    ? 'bg-teal-50 text-teal-900 font-semibold border border-teal-200'
                    : 'text-slate-600 hover:bg-slate-50'
                }`}
              >
                <FileCode className={`w-4 h-4 shrink-0 mt-0.5 ${selectedFile.path === file.path ? 'text-teal-600' : 'text-slate-400'}`} />
                <div className="overflow-hidden">
                  <div className="truncate text-xs font-mono">{file.name}</div>
                  <div className="text-[10px] text-slate-400 truncate mt-0.5 font-normal">{file.description}</div>
                </div>
              </button>
            ))}
          </div>

          <div className="pt-3 border-t border-slate-100 text-[11px] text-slate-500 space-y-1.5">
            <div className="font-semibold text-slate-700">Package Contents:</div>
            <div>&bull; /includes (Core, DB, Roles, Security, Auth, Logger)</div>
            <div>&bull; /modules/employees (Repo, Service, Controller)</div>
            <div>&bull; /admin (Dashboard, Employees, Views)</div>
            <div>&bull; /employee (Portal, Routing, Profile)</div>
            <div>&bull; /templates (Admin & Frontend Views)</div>
            <div>&bull; /assets/css (admin.css, employee-portal.css + RTL)</div>
            <div>&bull; /languages (nds-hr.pot translation file)</div>
          </div>
        </div>

        {/* Right: Code Inspector */}
        <div className="lg:col-span-2 bg-slate-950 text-slate-100 rounded-xl shadow-sm border border-slate-800 overflow-hidden flex flex-col">
          <div className="px-5 py-3 bg-slate-900 border-b border-slate-800 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <span className="text-xs font-mono text-teal-400">{selectedFile.path}</span>
            </div>
            <button
              onClick={handleCopy}
              className="text-xs text-slate-400 hover:text-white flex items-center gap-1 bg-slate-800 px-2.5 py-1 rounded border border-slate-700 transition-colors"
            >
              {copied ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
              <span>{copied ? 'Copied' : 'Copy Code'}</span>
            </button>
          </div>

          <div className="p-5 flex-1 overflow-x-auto">
            <pre className="font-mono text-xs text-slate-200 leading-relaxed">
              <code>{selectedFile.codeSnippet}</code>
            </pre>
          </div>
        </div>
      </div>

      {/* WordPress Deployment & Installation Guide */}
      <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
        <h3 className="text-base font-bold text-slate-900 flex items-center gap-2">
          <Server className="w-4 h-4 text-teal-600" />
          <span>How to Install & Activate in Any WordPress Site</span>
        </h3>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
          <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
            <div className="w-6 h-6 rounded-full bg-teal-600 text-white font-bold text-xs flex items-center justify-center">
              1
            </div>
            <div className="font-bold text-slate-900">Upload Plugin ZIP</div>
            <p className="text-slate-600 leading-relaxed">
              In your WordPress admin dashboard, navigate to <strong>Plugins &gt; Add New &gt; Upload Plugin</strong> and select <code>nds-hr.zip</code>.
            </p>
          </div>

          <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
            <div className="w-6 h-6 rounded-full bg-teal-600 text-white font-bold text-xs flex items-center justify-center">
              2
            </div>
            <div className="font-bold text-slate-900">Automatic Activation</div>
            <p className="text-slate-600 leading-relaxed">
              Upon clicking <strong>Activate Plugin</strong>, NDS HR executes `create_tables()` to provision custom tables, registers custom roles, and flushes rewrite rules.
            </p>
          </div>

          <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
            <div className="w-6 h-6 rounded-full bg-teal-600 text-white font-bold text-xs flex items-center justify-center">
              3
            </div>
            <div className="font-bold text-slate-900">Access Portals</div>
            <p className="text-slate-600 leading-relaxed">
              Administrators manage HR at <code>/wp-admin/admin.php?page=nds-hr</code>. Employees access their self-service dashboard directly at <code>/employee/</code>.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};
