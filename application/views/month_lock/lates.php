<style>
    :root {
        --primary: #0d9488;
        --secondary: #64748b;
        --bg-body: #eef2f7;
        --bg-card: #ffffff;
        --text-main: #1e293b;
        --border: #e2e8f0;
        --radius: 10px;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 10px rgba(0, 0, 0, .04);
    }

    #ml-page .panel {
        border: none;
        box-shadow: var(--shadow);
        border-radius: var(--radius);
        background: var(--bg-card);
        margin-bottom: 16px;
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
            <div class="panel-heading" style="background: linear-gradient(135deg,#fff7ed,#ffedd5) !important;">
                <span class="panel-title" style="color: #9a3412 !important;"><i class="fa fa-clock-o"></i> <?php echo $pageTitle; ?></span>
            </div>
            <div class="panel-body">
                <h3 style="margin-top:0;">Latest Lates</h3>
                <p class="text-muted">Review late arrivals registered during the locked periods.</p>
                <hr style="border-top: 1px solid var(--border); margin: 20px 0;">

                <div id="vue-app">
                    <div v-if="loading" style="text-align:center; padding: 40px;">
                        <i class="fa fa-circle-o-notch fa-spin fa-2x text-muted"></i>
                        <p class="text-muted" style="margin-top:10px;">Loading data...</p>
                    </div>
                    <div v-else-if="items.length === 0" class="empty-state" style="text-align: center; padding: 40px 20px; color: var(--secondary);">
                        <i class="fa fa-clock-o" style="font-size: 48px; opacity: 0.2; margin-bottom: 15px; display: block;"></i>
                        <p>No late arrivals recorded recently.</p>
                        <button @click="fetchData" class="btn btn-primary" style="background: linear-gradient(135deg, #9a3412 0%, #f97316 100%); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; margin-top: 10px;">
                            <i class="fa fa-refresh"></i> Refresh Data
                        </button>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table" style="margin-bottom:0;">
                            <thead>
                                <tr>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">ID</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">Employee</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">Department</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">Date</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">Shift</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">Clock In</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">Late By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in items" :key="item.id">
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9;">{{ item.id }}</td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9; font-weight:bold;">{{ item.employee_name }}</td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9;">{{ item.department }}</td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9;">{{ item.date }}</td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9;">{{ item.shift }}</td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9;">{{ item.clock_in }}</td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9;">
                                        <span style="background:#fee2e2; color:#b91c1c; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:bold;">{{ item.late_by }}</span>
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
        el: '#vue-app',
        data: {
            items: [],
            loading: true
        },
        mounted() {
            this.fetchData();
        },
        methods: {
            fetchData() {
                this.loading = true;
                axios.get('<?php echo site_url('monthlocks/lates'); ?>')
                    .then(response => {
                        if (response.data && response.data.status === 'success') {
                            this.items = response.data.data;
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching data:", error);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        }
    });
</script>
