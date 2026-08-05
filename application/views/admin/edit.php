<div class="page-wrapper">
    <div class="content container-fluid">
        <h4 class="page-title"><?= $pageTitle ?></h4>
        <form action="<?= base_url("admin/edit/$admin->id") ?>" method="post">
            <div class="row card-box">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="outlet">Outlet <span class="text-danger">*</span></label>
                        <select name="outlet" id="outlet" class="select">
                            <option value="">Select Outlet</option>
                            <?php foreach ($branches as $branch) : ?>
                                <option <?= $branch->id === $admin->branch_id ? 'selected' : '' ?> value="<?= $branch->id ?>"><?= $branch->name ?></option>
                            <?php endforeach ?>
                        </select>
                        <div class="text-danger m-t-5"><b><?= form_error('outlet') ?></b></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="admin-type">Admin Type<span class="text-danger">*</span></label>
                        <select name="adminType" id="admin-type" class="select">
                            <option value="">Select Type</option>
                            <?php foreach ($roles as $role) : ?>
                                <option <?= $role->id == $admin->role_id ? 'selected' : '' ?> value="<?= $role->id ?>"><?= $role->job_name ?></option>
                            <?php endforeach ?>
                        </select>
                        <div class="text-danger m-t-5"><b><?php echo form_error('adminType'); ?></b></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Name<span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= $admin->first_name ?>">
                        <div class="text-danger m-t-5"><b><?php echo form_error('name'); ?></b></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">E-mail<span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= $admin->email ?>">
                        <div class="text-danger m-t-5"><b><?php echo form_error('email'); ?></b></div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Admin</button>
            </div>
        </form>
    </div>
</div>