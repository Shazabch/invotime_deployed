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
            <div class="panel-heading" style="background: linear-gradient(135deg,#f3e8ff,#e9d5ff) !important;">
                <span class="panel-title" style="color: #6b21a8 !important;"><i class="fa fa-bar-chart"></i> <?php echo $pageTitle; ?></span>
            </div>
            <div class="panel-body">
                <h3 style="margin-top:0;">All Reports</h3>
                <p class="text-muted">Access comprehensive reports generated from month lock snapshots.</p>
                <hr style="border-top: 1px solid var(--border); margin: 20px 0;">

                <div id="vue-app">
                    <div v-if="loading" style="text-align:center; padding: 40px;">
                        <i class="fa fa-circle-o-notch fa-spin fa-2x text-muted"></i>
                        <p class="text-muted" style="margin-top:10px;">Loading data...</p>
                    </div>
                    <div v-else-if="items.length === 0" class="empty-state" style="text-align: center; padding: 40px 20px; color: var(--secondary);">
                        <i class="fa fa-bar-chart" style="font-size: 48px; opacity: 0.2; margin-bottom: 15px; display: block;"></i>
                        <p>Select a report to generate and view data.</p>
                        <button @click="fetchData" class="btn btn-primary" style="background: linear-gradient(135deg, #6b21a8 0%, #a855f7 100%); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; margin-top: 10px;">
                            <i class="fa fa-refresh"></i> Refresh Data
                        </button>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table" style="margin-bottom:0;">
                            <thead>
                                <tr>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">ID</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">Report Name</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">Type</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">Generated At</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary);">Size</th>
                                    <th style="border-bottom: 1px solid var(--border); color: var(--secondary); text-align:right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in items" :key="item.id">
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9;">{{ item.id }}</td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9; font-weight:bold;">{{ item.name }}</td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9;">
                                        <span v-if="item.type === 'PDF'" style="background:#fee2e2; color:#b91c1c; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:bold;">{{ item.type }}</span>
                                        <span v-else style="background:#dbeafe; color:#1d4ed8; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:bold;">{{ item.type }}</span>
                                    </td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9;">{{ item.generated_at }}</td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9;">{{ item.size }}</td>
                                    <td style="padding: 12px 8px; border-top: 1px solid #f1f5f9; text-align:right;">
                                        <button class="btn btn-sm btn-default" style="border:1px solid #cbd5e1; background:#fff; border-radius:6px;"><i class="fa fa-download"></i> Download</button>
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
                axios.get('<?php echo site_url('monthlocks/reports'); ?>')
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
