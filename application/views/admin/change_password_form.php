<div class="page-wrapper">
    <div class="content container-fluid">
        <h4 class="page-title"><?= $pageTitle ?></h4>
        <div class="row card-box">
            <div class="col-md-6">
                <form name="change_password" action="<?= base_url("admin/change_password/$adminId") ?>" method="post">
                    <div class="form-group">
                        <label for="pwd">New Password</label>
                        <input type="password" class="form-control" id="pwd" name="password">
                        <div class="text-danger m-t-5"><b><?= form_error('password') ?></b></div>
                    </div>
                    <div class="form-group">
                        <label for="n_pwd">Confirm Password</label>
                        <input type="password" class="form-control" id="n_pwd" name="passconf">
                        <div class="text-danger m-t-5"><b><?php echo form_error('passconf'); ?></b></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>