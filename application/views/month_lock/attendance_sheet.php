<style>
    #ml-page .panel {
        border: none;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        border-radius: 20px;
        background: #fff;
        margin-bottom: 25px;
        overflow: hidden;
    }

    #ml-page .panel-heading {
        background: linear-gradient(135deg, #f8fafc, #eff6ff) !important;
        border-bottom: 1px solid #edf2f7 !important;
        padding: 20px 25px;
    }

    #ml-page .panel-title {
        font-weight: 700;
        font-size: 16px;
        color: #1e293b !important;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Attendance Status Colors - Premium Palette */
    .color-calendar-check { color: #10b981 !important; font-size: 18px; } /* Present - Emerald */
    .color-calendar-plus { color: #6366f1 !important; font-size: 18px; }  /* Leave - Indigo */
    .color-calendar-times { color: #f43f5e !important; font-size: 18px; } /* Absent - Rose */
    .color-calendar-o { color: #94a3b8 !important; font-size: 18px; }     /* Rest/Off - Slate */
    .color-calendar-minus { color: #f59e0b !important; font-size: 18px; } /* Other - Amber */

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-right: 25px;
        padding: 6px 12px;
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .legend-item:hover {
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .freeze-table {
        border-radius: 15px;
        border: 1px solid #edf2f7;
        overflow: hidden;
    }

    .freeze-table th {
        background: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 15px 10px !important;
    }

    .freeze-table td {
        padding: 12px 10px !important;
        vertical-align: middle !important;
        border-color: #f1f5f9 !important;
    }

    .emp-column {
        position: sticky;
        left: 0;
        background: #fff !important;
        z-index: 10;
        box-shadow: 2px 0 10px rgba(0,0,0,0.02);
        border-right: 2px solid #f1f5f9 !important;
    }

    tr:hover td.emp-column {
        background: #f8fafc !important;
    }
</style>

<div class="page-wrapper" id="ml-page">
    <div class="container-fluid" style="padding: 25px;">
        <div class="panel" id="ml-attendance-app">
            <div class="panel-heading">
                <span class="panel-title">
                    <i class="fa fa-calendar-check-o" style="color: #6366f1;"></i>
                    <?php echo $pageTitle; ?>
                    <span v-if="lockInfo" style="margin-left: 10px; font-weight: 400; color: #64748b; font-size: 14px;">
                        — {{lockInfo.period}}
                    </span>
                </span>
            </div>
            <div class="panel-body" >
                <div class="row" style="margin-bottom: 25px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label style="color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Lock Period</label>
                            <select class="form-control" v-model="selectedLock" @change="fetchData"
                                    style="border-radius: 12px; border: 1px solid #e2e8f0; height: 45px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                <option value="">-- Select Period --</option>
                                <option v-for="lock in locks" :value="lock.id">{{lock.period}} ({{lock.start_date}} to {{lock.end_date}})</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div v-if="loading" style="text-align: center; padding: 80px;">
                    <i class="fa fa-circle-o-notch fa-spin fa-3x" style="color: #6366f1;"></i>
                    <p style="margin-top: 15px; color: #64748b; font-weight: 500;">Generating attendance matrix...</p>
                </div>

                <div v-else-if="!selectedLock" style="text-align: center; padding: 80px; background: #f8fafc; border-radius: 20px; border: 2px dashed #e2e8f0;">
                    <div style="width: 80px; height: 80px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                        <i class="fa fa-mouse-pointer" style="font-size: 30px; color: #cbd5e1;"></i>
                    </div>
                    <p style="color: #64748b; font-size: 16px; font-weight: 500;">Select a period to load attendance data</p>
                </div>

                <div v-else-if="employees.length === 0" style="text-align: center; padding: 80px;">
                    <i class="fa fa-folder-open-o fa-3x" style="color: #cbd5e1;"></i>
                    <p style="margin-top: 15px; color: #64748b;">No records found for this lock period.</p>
                </div>

                <div v-else>
                    <!-- Legend -->
                    <div style="margin-bottom: 25px; padding: 5px;">
                        <div class="legend-item">
                            <i class="fa fa-calendar-check color-calendar-check"></i>
                            <span style="font-size: 13px; font-weight: 600; color: #475569;">Present</span>
                        </div>
                        <div class="legend-item">
                            <i class="fa fa-calendar-plus color-calendar-plus"></i>
                            <span style="font-size: 13px; font-weight: 600; color: #475569;">Leave</span>
                        </div>
                        <div class="legend-item">
                            <i class="fa fa-calendar-times color-calendar-times"></i>
                            <span style="font-size: 13px; font-weight: 600; color: #475569;">Absent</span>
                        </div>
                        <div class="legend-item">
                            <i class="fa fa-calendar-o color-calendar-o"></i>
                            <span style="font-size: 13px; font-weight: 600; color: #475569;">Rest/Off Day</span>
                        </div>
                    </div>

                    <div class="table-responsive freeze-table" style="max-height: calc(100vh - 380px); overflow: auto;">
                        <table class="table table-bordered" style="min-width: 1200px; margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th class="emp-column" style="top: 0; z-index: 20;">Employee Name</th>
                                    <th v-for="date in dates" :key="date" style="text-align: center; min-width: 45px; top: 0; position: sticky; z-index: 15; background: #f8fafc;">
                                        <span style="font-size: 13px;">{{formatDay(date)}}</span><br/>
                                        <span style="font-size: 9px; opacity: 0.7;">{{formatWeekday(date)}}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="emp in employees" :key="emp.id">
                                    <td class="emp-column">
                                        <div style="font-weight: 700; color: #1e293b;">{{emp.name}}</div>
                                        <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase;">{{emp.department}}</div>
                                    </td>
                                    <td v-for="date in dates" :key="date" style="text-align: center;">
                                        <span v-if="emp.attendance[date] && emp.attendance[date].status !== '-'"
                                              :class="['fa', emp.attendance[date].class]"
                                              :title="emp.attendance[date].status + (emp.attendance[date].shift ? ' - ' + emp.attendance[date].shift : '')"
                                              data-toggle="tooltip">
                                        </span>
                                        <span v-else style="color: #e2e8f0; font-size: 14px;">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>blue/assets/js/custom-vue.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
new Vue({
    el: '#ml-attendance-app',
    data: {
        loading: false,
        locks: [],
        selectedLock: '',
        dates: [],
        employees: [],
        lockInfo: null
    },
    mounted: function() {
        this.fetchLocks();
    },
    methods: {
        fetchLocks: function() {
            var self = this;
            axios.get('<?php echo site_url("monthlocks/Locked_data"); ?>')
                .then(function(response) {
                    if (response.data && response.data.status === 'success') {
                        self.locks = response.data.data;
                        if (self.locks.length > 0 && !self.selectedLock) {
                            self.selectedLock = self.locks[0].id;
                            self.fetchData();
                        }
                    }
                })
                .catch(function(error) {
                    console.error('Error fetching locks:', error);
                });
        },
        fetchData: function() {
            if (!this.selectedLock) {
                this.dates = [];
                this.employees = [];
                this.lockInfo = null;
                return;
            }

            this.loading = true;
            var self = this;
            axios.get('<?php echo site_url("monthlocks/Attendance_sheet"); ?>?lock_id=' + this.selectedLock)
                .then(function(response) {
                    if (response.data && response.data.status === 'success') {
                        self.dates = response.data.dates;
                        self.employees = response.data.employees;
                        self.lockInfo = response.data.lock;

                        setTimeout(function() {
                            $('[data-toggle="tooltip"]').tooltip();
                        }, 500);
                    }
                })
                .catch(function(error) {
                    console.error('Error fetching attendance data:', error);
                })
                .finally(function() {
                    self.loading = false;
                });
        },
        formatDay: function(dateStr) {
            var d = new Date(dateStr);
            return d.getDate();
        },
        formatWeekday: function(dateStr) {
            var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            var d = new Date(dateStr);
            return days[d.getDay()];
        }
    }
});
</script>
