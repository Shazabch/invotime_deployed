
    <style>
      [v-cloak] { display: none; }

      /* Modern Animations */
      @keyframes slideInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }

      @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
      }

      @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
      }

      @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
      }

      @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
      }

      @keyframes glow {
        0%, 100% { box-shadow: 0 0 5px rgba(76, 175, 80, 0.3); }
        50% { box-shadow: 0 0 15px rgba(76, 175, 80, 0.6); }
      }

      #employee-groups-app {
        animation: fadeIn 0.5s ease-in;
      }

      /* Shimmer Loading Effect */
      .shimmer-wrapper {
        position: relative;
        overflow: hidden;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 1000px 100%;
        animation: shimmer 2s infinite;
        border-radius: 8px;
      }

      .shimmer-table-row {
        height: 50px;
        margin-bottom: 4px;
      }

      /* Header Section */
      .group-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        animation: slideInDown 0.6s ease-out;
      }

      .group-title {
        color: #00c5fb;
        font-size: 28px;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        margin: 0;
      }

      /* Modern Buttons */
      .btn-modern {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
      }

      .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.5);
      }

      .btn-modern:active {
        transform: translateY(0);
      }

      .btn-primary-modern {
        background: linear-gradient(90deg, #00c5fb 0%, #0253cc 100%);
        box-shadow: 0 4px 15px rgba(2, 83, 204, 0.3);
      }

      .btn-primary-modern:hover {
        box-shadow: 0 6px 20px rgba(2, 83, 204, 0.5);
      }

      /* Card Style Table */
      .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        animation: slideInUp 0.6s ease-out;
      }

      .table-card .table {
        margin: 0;
        border: none;
      }

      .table-card thead {
        background: linear-gradient(90deg, #00c5fb 0%, #0253cc 100%);
        color: white;
      }

      .table-card thead th {
        border: none;
        font-weight: 600;
        padding: 12px;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
      }

      .table-card tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
      }

      .table-card tbody tr:hover {
        background-color: #f8f9ff;
        transform: scale(1.01);
        box-shadow: inset 0 0 10px rgba(2, 83, 204, 0.1);
      }

      .table-card tbody td {
        padding: 12px;
        vertical-align: middle;
        border: none;
      }

      .table-card tbody tr:last-child {
        border-bottom: none;
      }

      /* Empty State */
      .empty-state {
        text-align: center;
        padding: 20px 15px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        animation: slideInUp 0.6s ease-out;
      }

      .empty-state i {
        font-size: 64px;
        color: #0253cc;
        margin-bottom: 10px;
      }

      .empty-state p {
        color: #999;
        font-size: 16px;
        margin-bottom: 0;
      }

      /* Badges - Modern Style */
      .badge-modern {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
      }

      .badge-branch {
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        color: white;
        box-shadow: 0 4px 10px rgba(33, 150, 243, 0.3);
      }

      .badge-branch:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(33, 150, 243, 0.5);
      }

      .badge-count {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
        box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
      }

      .badge-count:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(76, 175, 80, 0.5);
      }

      /* Employee Selector */
      .employee-selector {
        max-height: 300px;
        overflow-y: auto;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 8px;
        margin-top: 4px;
        background: linear-gradient(135deg, #f5f7fa 0%, #f9fbfc 100%);
        transition: all 0.3s ease;
      }}

      .employee-selector:focus-within {
        border-color: #0253cc;
        box-shadow: 0 0 20px rgba(2, 83, 204, 0.2);
      }

      /* Employee Items - Checkbox Style */
      .employee-item {
        padding: 8px 10px;
        cursor: pointer;
        border-radius: 8px;
        margin-bottom: 4px;
        background: white;
        border: 2px solid #e0e0e0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .employee-item:hover {
        background-color: #f8f9ff;
        border-color: #0253cc;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(2, 83, 204, 0.15);
      }

      .employee-item.selected {
        /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        border-color: #0253cc;
        color: #0253cc;
        box-shadow: 0 4px 15px rgba(2, 83, 204, 0.3);
      }

      .employee-item i {
        font-size: 16px;
        flex-shrink: 0;
      }

      .employee-item strong {
        font-weight: 600;
      }

      /* Modal Styling */
      .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideInUp 0.4s ease-out;
      }

      /* Extra Large Modal */
      .modal-dialog.modal-xl {
        max-width: 95% !important;
        width: 95% !important;
        margin-top: 0px !important;
      }

      @media (min-width: 1200px) {
        .modal-dialog.modal-xl {
          max-width: 1000px !important;
          width: 90% !important;
          margin-top: 0px !important;
        }
      }

      .modal-header {
        background: linear-gradient(90deg, #00c5fb 0%, #0253cc 100%);
        color: white;
        border: none;
        border-radius: 12px 12px 0 0;
        padding: 10px 12px;
      }

      .modal-header .modal-title {
        font-weight: 700;
        font-size: 18px;
      }

      .modal-header .close {
        color: white;
        opacity: 0.8;
        transition: opacity 0.3s;
      }

      .modal-header .close:hover {
        opacity: 1;
      }

      .modal-body {
        padding: 12px;
      }

      .modal-footer {
        border: none;
        background-color: #f8f9fa;
        border-radius: 0 0 12px 12px;
        padding: 10px;
      }

      /* Form Elements */
      .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
        display: block;
      }

      .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 14px;
        transition: all 0.3s ease;
      }

      .form-control:focus {
        border-color: #0253cc;
        box-shadow: 0 0 0 3px rgba(2, 83, 204, 0.1);
      }

      /* Button Group */
      .btn-group-modern {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 8px;
      }

      .btn-group-modern .btn {
        border-radius: 8px;
        padding: 3px 6px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
      }

      .btn-group-modern .btn-primary {
        background: linear-gradient(90deg, #00c5fb 0%, #0253cc 100%);
        color: white;
      }

      .btn-group-modern .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(2, 83, 204, 0.3);
      }

      .btn-group-modern .btn-default {
        background: white;
        color: #0253cc;
        border: 2px solid #0253cc;
      }

      .btn-group-modern .btn-default:hover {
        background: #0253cc;
        color: white;
      }

      .btn-group-modern .btn-info {
        background: #2196F3;
        color: white;
      }

      .btn-group-modern .btn-info:hover {
        background: #1976D2;
      }

      /* Action Buttons */
      .action-buttons {
        display: flex;
        gap: 6px;
      }

      .action-buttons .btn {
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        transition: all 0.3s ease;
        border: none;
      }

      .action-buttons .btn-info {
        background: #2196F3;
        color: white;
      }

      .action-buttons .btn-info:hover {
        background: #1976D2;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(33, 150, 243, 0.3);
      }

      .action-buttons .btn-warning {
        background: #FF9800;
        color: white;
      }

      .action-buttons .btn-warning:hover {
        background: #F57C00;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
      }

      .action-buttons .btn-danger {
        background: #F44336;
        color: white;
      }

      .action-buttons .btn-danger:hover {
        background: #D32F2F;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(244, 67, 54, 0.3);
      }

      /* Alert Styles */
      .alert {
        border: none;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 14px;
        animation: slideInDown 0.4s ease-out;
      }

      .alert-info {
        background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
        color: #1565C0;
        border-left: 4px solid #2196F3;
      }

      .alert-danger {
        background: linear-gradient(135deg, #FFEBEE 0%, #FFCDD2 100%);
        color: #C62828;
        border-left: 4px solid #F44336;
      }

      /* Loading Spinner */
      .loading-spinner {
        text-align: center;
        padding: 25px 20px;
        animation: fadeIn 0.5s ease-in;
      }

      .loading-spinner i {
        font-size: 48px;
        color: #0253cc;
        animation: pulse 1.5s ease-in-out infinite;
      }

      .loading-spinner p {
        color: #999;
        margin-top: 8px;
        font-weight: 500;
      }

      /* Stats Info */
      .stats-info {
        background: linear-gradient(135deg, #f5f7fa 0%, #f9fbfc 100%);
        padding: 8px 10px;
        border-radius: 8px;
        font-size: 13px;
        color: #666;
        /* margin-bottom: 8px; */
        border-left: 4px solid #0253cc;
      }

      .stats-info small {
        color: #999;
      }

      /* Selection Counter */
      .selection-counter {
        display: inline-block;
        background: linear-gradient(90deg, #00c5fb 0%, #0253cc 100%);
        color: white;
        padding: 8px 14px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        animation: slideInDown 0.4s ease-out;
      }

      /* Selected Employees Display */
      .selected-employees-section {
        background: linear-gradient(135deg, #f5f7fa 0%, #f9fbfc 100%);
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 10px;
        margin-top: 8px;
        animation: slideInUp 0.4s ease-out;
      }

      .selected-employees-header {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        gap: 8px;
      }

      .selected-employees-header i {
        color: #0253cc;
        font-size: 18px;
      }

      .selected-employees-header h5 {
        margin: 0;
        color: #333;
        font-weight: 700;
      }

      .selected-employees-badge {
        background: linear-gradient(90deg, #00c5fb 0%, #0253cc 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 600;
      }

      .selected-emp-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        max-height: 200px;
        overflow-y: auto;
      }

      .selected-emp-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(90deg, #00c5fb 0%, #0253cc 100%);
        color: white;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(2, 83, 204, 0.3);
        animation: slideInDown 0.3s ease-out;
        transition: all 0.3s ease;
        white-space: nowrap;
        max-width: fit-content;
        flex-shrink: 0;
      }

      .selected-emp-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(2, 83, 204, 0.4);
      }

      .selected-emp-badge .remove-btn {
        background: rgba(255, 255, 255, 0.3);
        border: none;
        color: white;
        cursor: pointer;
        padding: 2px 6px;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: all 0.2s ease;
        flex-shrink: 0;
      }

      .selected-emp-badge .remove-btn:hover {
        background: rgba(255, 255, 255, 0.6);
        transform: rotate(90deg);
      }

      /* Two Column Layout */
      .employees-two-columns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 0;
      }

      /* Top Fields - Two Column Layout */
      .form-row-two-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
      }

      .form-row-two-cols .form-group {
        margin-bottom: 0;
      }

      .column-left {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }

      .column-left-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e0e0e0;
      }

      .column-left-header h5 {
        margin: 0;
        color: #333;
        font-weight: 700;
        font-size: 16px;
      }

      .column-left-header i {
        color: #0253cc;
        font-size: 20px;
      }

      .column-right {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }

      .column-right-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e0e0e0;
      }

      .column-right-header h5 {
        margin: 0;
        color: #333;
        font-weight: 700;
        font-size: 16px;
      }

      .column-right-header i {
        color: #4CAF50;
        font-size: 20px;
      }

      .selected-emp-list-vertical {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 6px;
        max-height: 350px;
        overflow-y: auto;
        padding-right: 8px;
      }

      .selected-emp-list-vertical::-webkit-scrollbar {
        width: 8px;
      }

      .selected-emp-list-vertical::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
      }

      .selected-emp-list-vertical::-webkit-scrollbar-thumb {
        background: #0253cc;
        border-radius: 10px;
      }

      .selected-emp-list-vertical::-webkit-scrollbar-thumb:hover {
        background: #00c5fb;
      }

      .employee-selector-vertical {
        max-height: 350px;
        overflow-y: auto;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 8px;
        background: linear-gradient(135deg, #f5f7fa 0%, #f9fbfc 100%);
        transition: all 0.3s ease;
      }

      .employee-selector-vertical:focus-within {
        border-color: #0253cc;
        box-shadow: 0 0 20px rgba(2, 83, 204, 0.2);
      }

      .employee-selector-vertical::-webkit-scrollbar {
        width: 8px;
      }

      .employee-selector-vertical::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
      }

      .employee-selector-vertical::-webkit-scrollbar-thumb {
        background: #0253cc;
        border-radius: 10px;
      }

      .employee-selector-vertical::-webkit-scrollbar-thumb:hover {
        background: #00c5fb;
      }

      /* Responsive - Stack columns on mobile */
      @media (max-width: 1024px) {
        .employees-two-columns {
          grid-template-columns: 1fr;
          gap: 8px;
        }

        .form-row-two-cols {
          grid-template-columns: 1fr;
          gap: 8px;
        }
      }

      /* Clear All Button */
      .btn-group-clear {
        display: flex;
        gap: 6px;
      }

      /* Responsive */
      @media (max-width: 768px) {
        .group-header {
          flex-direction: column;
          align-items: flex-start;
        }

        .group-title {
          font-size: 22px;
          margin-bottom: 8px;
        }

        .action-buttons {
          width: 100%;
        }

        .table-card thead th {
          font-size: 11px;
          padding: 12px;
        }

        .table-card tbody td {
          font-size: 13px;
          padding: 12px;
        }
      }
    </style>

    <div id="employee-groups-app" v-cloak>
      <!-- Header Section -->
      <div class="group-header">
        <h2 class="group-title">
          <i class="fa fa-users" style="margin-right: 10px;"></i>Employee Groups
        </h2>
        <button class="btn btn-modern" @click="openCreateModal">
          <i class="fa fa-plus"></i> Create New Group
        </button>
      </div>

      <!-- Shimmer Loading -->
      <div v-if="loading">
        <div class="table-card">
          <table class="table">
            <thead>
              <tr>
                <th width="5%">#</th>
                <th width="25%">Name</th>
                <th width="20%">Branch</th>
                <th width="15%">Employees</th>
                <th width="20%">Created</th>
                <th width="15%">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="n in 6" :key="n">
                <td colspan="6">
                  <div class="shimmer-wrapper shimmer-table-row"></div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Groups Table -->
      <div v-else>
        <!-- Empty State -->
        <div v-if="groups.length === 0" class="empty-state">
          <i class="fa fa-inbox"></i>
          <h4>No Employee Groups Yet</h4>
          <p>Get started by creating your first employee group to organize your workforce.</p>
          <button class="btn btn-modern" @click="openCreateModal" style="margin-top: 15px;">
            <i class="fa fa-plus"></i> Create First Group
          </button>
        </div>

        <!-- Data Table -->
        <div v-else class="table-card">
          <table class="table">
            <thead>
              <tr>
                <th width="5%">#</th>
                <th width="25%">Name</th>
                <th width="20%">Branch</th>
                <th width="15%">Employees</th>
                <th width="20%">Created</th>
                <th width="15%">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(group, index) in groups" :key="group.id">
                <td>
                  <span style="color: #0253cc; font-weight: 600;">{{ index + 1 }}</span>
                </td>
                <td>
                  <strong style="color: #333; font-size: 15px;">{{ group.name }}</strong>
                </td>
                <td>
                  <span v-if="group.branch_id && group.branch_id != '0'" class="badge-modern badge-branch">
                    <i class="fa fa-building"></i> {{ group.branch_name }}
                  </span>
                  <span v-else class="badge-modern" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white; box-shadow: 0 4px 10px rgba(156, 39, 176, 0.3);">
                    <i class="fa fa-globe"></i> All Branches
                  </span>
                </td>
                <td>
                  <span class="badge-modern badge-count">
                    <i class="fa fa-users"></i> {{ group.employee_count }}
                  </span>
                </td>
                <td>
                  <small style="color: #999;">{{ group.created_at }}</small>
                </td>
                <td>
                  <div class="action-buttons">
                    <button class="btn btn-info" @click="viewGroup(group)" title="View Details">
                      <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-warning" @click="editGroup(group)" title="Edit Group">
                      <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-danger" @click="confirmDelete(group)" title="Delete Group">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <div class="modal fade" id="groupModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">
                <i :class="editMode ? 'fa fa-edit' : 'fa fa-plus-circle'" style="margin-right: 10px;"></i>
                {{ editMode ? 'Edit Employee Group' : 'Create New Employee Group' }}
              </h4>
            </div>
            <div class="modal-body">
              <form @submit.prevent="saveGroup">
                <!-- Top Fields - Group Name & Branch in One Row -->
                <div class="form-row-two-cols">
                  <div class="form-group">
                    <label><i class="fa fa-tag" style="margin-right: 8px;"></i>Group Name <span style="color: #F44336;">*</span></label>
                    <input type="text" class="form-control" v-model="form.name" required placeholder="Enter a unique group name" maxlength="100">
                  </div>

                  <div class="form-group">
                    <label><i class="fa fa-building" style="margin-right: 8px;"></i>Branch <small class="text-muted">(Optional)</small></label>
                    <select class="form-control" v-model="form.branch_id" @change="onBranchChange">
                      <option value="0">📍 All Branches (Company-wide)</option>
                      <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                        📍 {{ branch.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <!-- <label><i class="fa fa-users" style="margin-right: 8px;"></i>Select Employees</label> -->

                  <!-- Control Buttons -->
                  <div class="btn-group-modern">
                    <button type="button" class="btn btn-primary" @click="selectAllEmployees" :disabled="filteredEmployees.length === 0">
                      <i class="fa fa-check-square"></i> Select All
                    </button>
                    <button type="button" class="btn btn-default" @click="clearAllEmployees" :disabled="form.employee_ids.length === 0">
                      <i class="fa fa-square-o"></i> Clear All
                    </button>
                    <button type="button" class="btn btn-info" @click="loadEmployees(form.branch_id)" :disabled="loadingEmployees">
                      <i class="fa fa-refresh" :class="{'fa-spin': loadingEmployees}"></i> Reload
                    </button>
                     <div class="stats-info" v-if="employees.length > 0">
                        <small>
                          <i class="fa fa-info-circle"></i> Total: <strong>{{ employees.length }}</strong> |
                          Showing: <strong>{{ filteredEmployees.length }}</strong>
                        </small>
                      </div>

                  </div>

                  <!-- Two Column Layout -->
                  <div class="employees-two-columns">
                    <!-- LEFT COLUMN: Employee Selection -->
                    <div class="column-left">
                      <!-- <div class="column-left-header">
                        <i class="fa fa-search"></i>
                        <h5>Available Employees</h5>
                      </div> -->

                      <!-- Stats -->

                      <!-- Search Input -->
                      <div style="margin-bottom: 0;">
                        <input
                          type="text"
                          class="form-control"
                          v-model.trim="employeeSearch"
                          placeholder="🔍 Search by ID or name..."
                        >
                      </div>

                      <!-- Employee List -->
                      <div class="employee-selector-vertical">
                        <!-- Loading State -->
                        <div v-if="loadingEmployees" class="loading-spinner">
                          <i class="fa fa-spinner fa-spin fa-2x"></i>
                          <p>Loading employees...</p>
                        </div>

                        <!-- Error State -->
                        <div v-else-if="employeeLoadError" class="alert alert-danger" style="margin-bottom: 0;">
                          <i class="fa fa-exclamation-triangle"></i>
                          <strong>Error:</strong> {{ employeeLoadError }}
                          <br><button type="button" class="btn btn-warning" @click="loadEmployees(form.branch_id)" style="margin-top: 10px;">
                            <i class="fa fa-refresh"></i> Retry
                          </button>
                        </div>

                        <!-- Empty State -->
                        <div v-else-if="filteredEmployees.length === 0" class="text-center text-muted" style="padding: 30px 20px;">
                          <i class="fa fa-inbox fa-2x"></i>
                          <p style="margin-top: 15px;">No employees found</p>
                        </div>

                        <!-- Employee Checkboxes -->
                        <div v-else>
                          <div
                            v-for="emp in filteredEmployees"
                            :key="emp.id"
                            class="employee-item"
                            :class="{selected: isEmployeeSelected(parseInt(emp.id))}"
                            @click="toggleEmployee(parseInt(emp.id))"
                          >
                            <i :class="isEmployeeSelected(parseInt(emp.id)) ? 'fa fa-check-square-o' : 'fa fa-square-o'"></i>
                            <strong>{{ emp.special_id }}</strong>
                            <span style="font-weight: normal;">- {{ emp.first_name }}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- RIGHT COLUMN: Selected Employees -->
                    <div class="column-right">
                      <div class="column-right-header">
                        <div style="display: flex; align-items: center; gap: 10px;">
                          <i class="fa fa-check-circle"></i>
                          <h5>Selected</h5>
                        </div>
                        <span class="selected-employees-badge" @click="openSelectedModal" style="cursor: pointer;" title="Click to view all">
                          {{ form.employee_ids.length }}
                        </span>
                      </div>

                      <!-- Empty State -->
                      <div v-if="form.employee_ids.length === 0" class="text-center text-muted" style="padding: 60px 20px; background: #f5f7fa; border-radius: 10px;">
                        <i class="fa fa-inbox fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                        <p style="margin: 0; color: #999;">Select employees from the left to see them here</p>
                      </div>

                      <!-- Selected Employees List -->
                      <div v-else class="selected-emp-list-vertical">
                        <div
                          v-for="empId in form.employee_ids"
                          :key="empId"
                          class="selected-emp-badge"
                        >
                          <span>
                            <i class="fa fa-user-check"></i>
                            {{ getEmployeeName(empId) }}
                          </span>
                          <button
                            type="button"
                            class="remove-btn"
                            @click.stop="toggleEmployee(empId)"
                            title="Remove this employee"
                          >
                            ✕
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">✕ Cancel</button>
              <button type="button" class="btn btn-primary-modern btn-modern" @click="saveGroup" :disabled="saving">
                <i class="fa fa-spinner fa-spin" v-if="saving"></i>
                <i class="fa fa-save" v-if="!saving"></i>
                {{ saving ? 'Saving...' : 'Save Group' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- View Modal -->
      <div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">
                <i class="fa fa-eye" style="margin-right: 10px;"></i>{{ viewingGroup.name }}
              </h4>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label style="color: #0253cc;"><i class="fa fa-building"></i> Branch:</label>
                <p>
                  <span v-if="viewingGroup.branch_id && viewingGroup.branch_id != '0'" class="badge-modern badge-branch">
                    {{ viewingGroup.branch_name }}
                  </span>
                  <span v-else class="badge-modern" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white;">
                    All Branches
                  </span>
                </p>
              </div>

              <div class="form-group">
                <label style="color: #0253cc;"><i class="fa fa-users"></i> Employees ({{ viewingGroup.employees ? viewingGroup.employees.length : 0 }}):</label>
                <div v-if="viewingGroup.employees && viewingGroup.employees.length > 0" style="max-height: 350px; overflow-y: auto; background: #f5f7fa; border-radius: 8px; padding: 10px;">
                  <div v-for="emp in viewingGroup.employees" :key="emp.id" style="padding: 10px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-user" style="color: #0253cc; width: 20px; text-align: center;"></i>
                    <div>
                      <strong style="color: #333;">{{ emp.special_id }}</strong>
                      <div style="color: #999; font-size: 13px;">{{ emp.first_name }}</div>
                    </div>
                  </div>
                </div>
                <div v-else class="alert alert-info" style="margin-bottom: 0;">
                  <i class="fa fa-info-circle"></i> No employees in this group.
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary-modern btn-modern" data-dismiss="modal">✓ Close</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Selected Employees Modal -->
      <div class="modal fade" id="selectedEmployeesModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">
                <i class="fa fa-check-circle" style="margin-right: 10px;"></i>Selected Employees ({{ form.employee_ids.length }})
              </h4>
            </div>
            <div class="modal-body">
              <div v-if="form.employee_ids.length === 0" class="empty-state" style="padding: 40px 20px;">
                <i class="fa fa-inbox fa-2x"></i>
                <h4>No Employees Selected</h4>
                <p>Please select employees from the form above.</p>
              </div>

              <div v-else>
                <!-- Stats -->
                <div class="stats-info" style="margin-bottom: 20px;">
                  <small>
                    <i class="fa fa-list-ul"></i> Total selected: <strong>{{ form.employee_ids.length }}</strong> employees
                  </small>
                </div>

                <!-- Selected Employees Table -->
                <div class="table-card">
                  <table class="table">
                    <thead>
                      <tr>
                        <th width="5%">#</th>
                        <th width="30%">Employee ID</th>
                        <th width="40%">Name</th>
                        <th width="25%">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(empId, index) in form.employee_ids" :key="empId">
                        <td>
                          <span style="color: #0253cc; font-weight: 600;">{{ index + 1 }}</span>
                        </td>
                        <td>
                          <strong style="color: #333;">{{ getEmployeeId(empId) }}</strong>
                        </td>
                        <td>
                          <span style="color: #555;">{{ getEmployeeFirstName(empId) }}</span>
                        </td>
                        <td>
                          <button class="btn btn-danger" @click="removeSelectedEmployee(empId)" title="Remove employee">
                            <i class="fa fa-trash"></i> Remove
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" @click="clearAllSelected" v-if="form.employee_ids.length > 0">
                <i class="fa fa-times"></i> Clear All
              </button>
              <button type="button" class="btn btn-primary-modern btn-modern" data-dismiss="modal">✓ Done</button>
            </div>
          </div>
        </div>
      </div>
    </div>

<script src="<?php echo base_url(); ?>blue/assets/js/custom-vue.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    new Vue({
      el: '#employee-groups-app',
      data: {
        loading: true,
        loadingEmployees: false,
          employeeLoadError: null,
        saving: false,
        employeeSearch: '',
        groups: [],
        branches: [],
        employees: [],
        editMode: false,
        form: {
          id: null,
          name: '',
          branch_id: '0',
          employee_ids: []
        },
        viewingGroup: {}
      },
      computed: {
        filteredEmployees() {
          let filtered = this.employees;

          if (!this.form.branch_id || this.form.branch_id == '0' || this.form.branch_id == 0) {
            filtered = this.employees;
          } else {
            filtered = this.employees.filter(emp => String(emp.branch_id) == String(this.form.branch_id));
          }

          const keyword = (this.employeeSearch || '').toLowerCase();
          if (!keyword) {
            return filtered;
          }

          return filtered.filter(emp => {
            const sid = String(emp.special_id || '').toLowerCase();
            const name = String(emp.first_name || '').toLowerCase();
            return sid.includes(keyword) || name.includes(keyword);
          });
        }
      },
      mounted() {
        this.loadData();
      },
      methods: {
        showPopup(message, type = 'info', title = 'Notice') {
          if (window.Swal && typeof window.Swal.fire === 'function') {
            return window.Swal.fire({
              icon: type,
              title: title,
              text: message,
              confirmButtonText: 'OK'
            });
          }

          alert(message);
          return Promise.resolve();
        },

        confirmPopup(message, title = 'Please confirm') {
          if (window.Swal && typeof window.Swal.fire === 'function') {
            return window.Swal.fire({
              icon: 'warning',
              title: title,
              text: message,
              showCancelButton: true,
              confirmButtonText: 'Yes',
              cancelButtonText: 'Cancel'
            }).then(result => result.isConfirmed);
          }

          return Promise.resolve(confirm(message));
        },

        async loadData() {
          this.loading = true;
          try {
            await Promise.all([
              this.loadGroups(),
              this.loadBranches(),
              this.loadEmployees('0')
            ]);
          } catch (error) {
            console.error('Error loading data:', error);
          } finally {
            this.loading = false;
          }
        },

        async loadGroups() {
          const response = await fetch('<?php echo base_url("employee_groups_api/get_groups"); ?>');
          const data = await response.json();
          console.log('Groups response:', data);
          if (data.success) {
            this.groups = data.data;
          }
        },

        async loadBranches() {
          const response = await fetch('<?php echo base_url("employee_groups_api/get_branches"); ?>');
          const data = await response.json();
          console.log('Branches response:', data);
          if (data.success) {
            this.branches = data.data;
          }
        },

        async loadEmployees(branchId = '0') {
          this.loadingEmployees = true;
          this.employeeLoadError = null;
          try {
            let url = '<?php echo base_url("employee_groups_api/get_employees"); ?>';
            if (branchId && String(branchId) !== '0') {
              url += '?branch_id=' + encodeURIComponent(branchId);
            }

            const response = await fetch(url);
            if (!response.ok) {
              throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            }

            const data = await response.json();
            if (data.success) {
              this.employees = data.data || [];
            } else {
              this.employeeLoadError = data.error || 'Failed to load employees';
            }
          } catch (error) {
            this.employeeLoadError = error.message;
            console.error('Error loading employees:', error);
          } finally {
            this.loadingEmployees = false;
          }
        },

        openCreateModal() {
          this.editMode = false;
          this.employeeSearch = '';
          this.form = {
            id: null,
            name: '',
            branch_id: '0',
            employee_ids: []
          };

          $('#groupModal').modal('show');
          this.loadEmployees(this.form.branch_id);
        },

        async editGroup(group) {
          this.editMode = true;
          this.employeeSearch = '';

          const response = await fetch('<?php echo base_url("employee_groups_api/get_group/"); ?>' + group.id);
          const data = await response.json();

          if (data.success) {
            this.form = {
              id: data.data.id,
              name: data.data.name,
              branch_id: String(data.data.branch_id || '0'),
              employee_ids: data.data.employees.map(e => parseInt(e.id))
            };
            $('#groupModal').modal('show');
            await this.loadEmployees(this.form.branch_id);
          }
        },

        async viewGroup(group) {
          const response = await fetch('<?php echo base_url("employee_groups_api/get_group/"); ?>' + group.id);
          const data = await response.json();

          if (data.success) {
            this.viewingGroup = data.data;
            $('#viewModal').modal('show');
          }
        },

        async saveGroup() {
          if (!this.form.name.trim()) {
            await this.showPopup('Please enter a group name', 'warning', 'Validation');
            return;
          }

          this.saving = true;

          try {
            const url = this.editMode
              ? '<?php echo base_url("employee_groups_api/update_group/"); ?>' + this.form.id
              : '<?php echo base_url("employee_groups_api/create_group"); ?>';

            const response = await fetch(url, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(this.form)
            });

            const data = await response.json();

            if (data.success) {
              await this.showPopup(data.message, 'success', 'Success');
              $('#groupModal').modal('hide');
              await this.loadGroups();
            } else {
              await this.showPopup(data.error || 'Failed to save group', 'error', 'Error');
            }
          } catch (error) {
            console.error(error);
            await this.showPopup('An error occurred while saving', 'error', 'Error');
          } finally {
            this.saving = false;
          }
        },

        async confirmDelete(group) {
          const isConfirmed = await this.confirmPopup(`Are you sure you want to delete the group "${group.name}"?`, 'Delete Group');
          if (!isConfirmed) {
            return;
          }

          this.saving = true;

          try {
            const response = await fetch('<?php echo base_url("employee_groups_api/delete_group/"); ?>' + group.id, {
              method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
              await this.showPopup(data.message, 'success', 'Deleted');
              await this.loadGroups();
            } else {
              await this.showPopup(data.error || 'Failed to delete group', 'error', 'Error');
            }
          } finally {
            this.saving = false;
          }
        },

        onBranchChange() {
          this.loadEmployees(this.form.branch_id).then(() => {
            const validEmpIds = this.employees.map(e => parseInt(e.id));
            this.form.employee_ids = this.form.employee_ids.filter(id => validEmpIds.includes(id));
          });
        },

        toggleEmployee(empId) {
          const index = this.form.employee_ids.indexOf(empId);
          if (index > -1) {
            this.form.employee_ids.splice(index, 1);
          } else {
            this.form.employee_ids.push(empId);
          }
        },

        isEmployeeSelected(empId) {
          return this.form.employee_ids.includes(empId);
        },

        selectAllEmployees() {
          this.form.employee_ids = this.filteredEmployees.map(e => parseInt(e.id));
        },

        clearAllEmployees() {
          this.form.employee_ids = [];
        },

        getEmployeeName(empId) {
          const employee = this.employees.find(e => parseInt(e.id) === empId);
          return employee ? `${employee.special_id} - ${employee.first_name}` : `Employee #${empId}`;
        },

        getEmployeeId(empId) {
          const employee = this.employees.find(e => parseInt(e.id) === empId);
          return employee ? employee.special_id : `#${empId}`;
        },

        getEmployeeFirstName(empId) {
          const employee = this.employees.find(e => parseInt(e.id) === empId);
          return employee ? employee.first_name : 'Unknown';
        },

        openSelectedModal() {
          $('#selectedEmployeesModal').modal('show');
        },

        removeSelectedEmployee(empId) {
          this.toggleEmployee(empId);
        },

        clearAllSelected() {
          this.form.employee_ids = [];
        }
      }
    });
    </script>


