<div class="page-wrapper">
    <div class="content container-fluid">
        <h4 class="page-title"><?= $pageTitle ?></h4>
        <?php if (!empty($this->session->flashdata('success'))) : ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Success!</strong> <?= $this->session->flashdata('success') ?>
            </div>
        <?php endif ?>
        <?php if (!empty($this->session->flashdata('error'))) : ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Error!</strong> <?= $this->session->flashdata('error') ?>
            </div>
        <?php endif ?>
        <div class="card-box">
            <div class="text-right">
                <a href="<?= base_url('admin/add') ?>" class="btn btn-primary">Add Admin</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Special ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin) : ?>
                            <tr>

                                <td><a href="#"><?= $admin->employee_id ?></a></td>
                                <td><?= $admin->first_name ?></td>
                                <td><?= $admin->email ?></td>
                                <td><?= $admin->job_name ?></td>
                                <td>
                                    <div class="btn-group-xs">
                                        <a href="<?= base_url("admin/edit/$admin->id") ?>" class="btn btn-primary">Edit</a>
                                        <a href="<?= base_url("admin/change_password/$admin->id") ?>" class="btn btn-warning">Edit Password</a>
                                        <a href="<?= base_url("admin/delete/$admin->id") ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to Delete?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                </table>
            </div>
        </div>
    </div>
</div>