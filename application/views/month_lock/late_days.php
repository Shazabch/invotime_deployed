    #ml-page .panel {
        border: none;
        box-shadow: var(--ml-card-shadow);
        border-radius: 16px;
        background: #fff;
        margin-bottom: 24px;
        overflow: hidden;
    }

    #ml-page .panel-heading {
        background: transparent !important;
        border-bottom: 1px solid var(--border) !important;
        padding: 13px 18px;
        color: var(--text-main) !important;
    }

    #ml-page .panel-title {
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: var(--secondary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #ml-page .panel-body {
        padding: 16px 18px;
    }
</style>

<div class="page-wrapper" id="ml-page">
    <div class="container-fluid" style="padding-top: 20px;padding: 20px;">
        <div class="panel">
            <div class="panel-heading" style="background: linear-gradient(135deg,#f0fdfa,#eff6ff) !important;">
                <span class="panel-title" style="color: #1e293b !important;"><i class="fa fa-clock-o"></i> <?php echo $pageTitle; ?></span>
            </div>
            <div class="panel-body" id="ml-late-app">
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Select Lock Period</label>
                            <select class="form-control" v-model="selectedLock" @change="fetchData">
                                <option value="">-- Select Period --</option>
                                <option v-for="lock in locks" :value="lock.id">{{lock.period}} ({{lock.start_date}} to {{lock.end_date}})</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div v-if="loading" style="text-align: center; padding: 40px;">
                    <i class="fa fa-circle-o-notch fa-spin fa-3x" style="color: #0d9488;"></i>
                    <p style="margin-top: 10px; color: #64748b;">Loading data...</p>
                </div>

                <div v-else-if="!selectedLock" style="text-align: center; padding: 40px;">
                    <i class="fa fa-hand-o-up fa-3x" style="color: #e2e8f0;"></i>
                    <p style="margin-top: 10px; color: #64748b;">Select a Lock Period to view the Late sheet</p>
                </div>

                <div v-else-if="employees.length === 0" style="text-align: center; padding: 40px;">
                    <i class="fa fa-inbox fa-3x" style="color: #e2e8f0;"></i>
                    <p style="margin-top: 10px; color: #64748b;">No data found for this period</p>
                </div>

                <div v-else>
                    <div style="margin-bottom: 15px;">
                        <span class="text-danger" style="font-weight: bold;">00:00</span> Late &nbsp;&nbsp;&nbsp;
                        <span class="text-success" style="font-weight: bold;">00:00</span> Void Late &nbsp;&nbsp;&nbsp;
                        <span style="color: #ccc;">-</span> On Time
                    </div>

                    <div class="table-responsive freeze-table" style="max-height: 600px; overflow: auto;">
                        <table class="table table-bordered table-striped" style="min-width: 1200px; font-size: 13px;">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px; position: sticky; left: 0; background: #fff; z-index: 10; border-bottom: 2px solid #e2e8f0;">Employee</th>
                                    <th v-for="date in dates" :key="date" style="text-align: center; min-width: 45px; border-bottom: 2px solid #e2e8f0;">
                                        <b>{{formatDay(date)}}</b><br/>
                                        <small>{{formatWeekday(date)}}</small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="emp in employees" :key="emp.id">
                                    <td style="position: sticky; left: 0; background: #fff; z-index: 5; box-shadow: inset -1px 0 0 #e2e8f0;">
                                        <strong>{{emp.name}}</strong><br/>
                                        <small class="text-muted">{{emp.special_id}}</small>
                                    </td>
                                    <td v-for="date in dates" :key="date" style="text-align: center; vertical-align: middle;">
                                        <span v-if="emp.data[date] && emp.data[date].value !== '-'"
                                              :class="emp.data[date].class"
                                              style="font-weight: bold; font-size: 11px;"
                                              :title="emp.data[date].shift"
                                              data-toggle="tooltip">
                                              {{ emp.data[date].value }}
                                        </span>
                                        <span v-else style="color: #ccc;">-</span>
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
    el: '#ml-late-app',
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
                return;
            }

            this.loading = true;
            var self = this;
            axios.get('<?php echo site_url("monthlocks/Late_days"); ?>?lock_id=' + this.selectedLock)
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
                    console.error('Error fetching data:', error);
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
