<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <h3 class="mb-0">
                        <i class="bi bi-pencil-square"></i> Categories Master Data
                    </h3>
                </div>
                <div class="col-12 col-md-6">
                    <ol class="breadcrumb float-md-end mb-0">
                        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Categories</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6 mb-2 mb-md-0">
                            <h5 class="mb-0 text-bold">
                                <i class="bi bi-table"></i> <strong>CATEGORY LIST</strong>
                            </h5>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <div class="d-flex flex-wrap justify-content-md-end gap-1">
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#add_modal">
                                    <i class="bi bi-plus-circle"></i> Add Category
                                </button>
                                <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#import_modal">
                                    <i class="bi bi-file-earmark-arrow-up"></i> Import Excel
                                </button>
                                <button class="btn btn-secondary btn-sm">
                                    <i class="bi bi-file-earmark-arrow-down"></i> Download Excel
                                </button>
                                <button class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Delete Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <select class="form-select form-select-sm w-auto d-inline-block">
                            <option value="">-- Filter by Categories --</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <!-- Contoh responsive table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered    " id="table_details">
                            <thead class="text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Categories</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="add_modal" tabindex="-1" aria-labelledby="add_modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="add_modalLabel">Add Category</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="add_data_form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit_modal" tabindex="-1" aria-labelledby="edit_modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="edit_modalLabel">Edit Category</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="edit_data_form">
                <input type="hidden" id="edit_id" name="id" required>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        console.log("Jquery script loaded");

        // $('#table_details').DataTable();
        let table = $('#table_details').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            responsive: true,
            columns: [{
                    data: 'no',
                    className: 'text-center'
                },
                {
                    data: 'category_name'
                },
                {
                    data: 'description',
                    className: 'text-center'
                },
                {
                    data: 'action',
                    className: 'text-center',
                    orderable: false
                }
            ]
        });

        function get_table() {
            $.ajax({
                url: "<?= base_url('master_data/get_categories') ?>",
                type: "GET",
                dataType: "json",
                success: function(res) {
                    if (res.status === 'success') {
                        table.clear();
                        table.rows.add(res.data);
                        table.draw();
                    } else {
                        $("#table_details tbody").html(
                            `<tr><td colspan="4" class="text-center text-danger">${res.message}</td></tr>`
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    $("#table_details tbody").html(
                        `<tr><td colspan="4" class="text-center text-danger">Failed to load categories.</td></tr>`
                    );
                }
            });
        }

        get_table();

        $("#add_data_form").on("submit", function(e) {
            e.preventDefault();

            let dataForm = $(this).serialize();
            console.log("dataForm:", dataForm);

            Swal.fire({
                title: "Are you sure?",
                text: "Add this Categories!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Confirm!"
            }).then((result) => {
                $.ajax({
                    url: "<?= base_url('master_data/save_categories') ?>",
                    type: "POST",
                    data: dataForm,
                    dataType: "json",
                    success: function(res) {
                        if (res.status === 'success') {

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message
                            }).then(() => {
                                $('#add_data_form')[0].reset();
                                $('#add_modal').modal('hide');
                                get_table();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: res.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Something went wrong: ' + error, 'error');
                    }
                    //error: function(xhr, status, error) {
                    //     Swal.fire({
                    //         icon: 'error',
                    //         title: 'Ajax Error',
                    //         html: `
                    //             <b>Status:</b> ${status} <br>
                    //             <b>Error:</b> ${error} <br>
                    //             <b>Response:</b> ${xhr.responseText}
                    //          `
                    //     });
                    // }
                });
            })
        });

        $(document).on("click", ".edit-btn", function() {
            let id = $(this).data("id");
            let category_name = $(this).data("category_name");
            let description = $(this).data("description");

            $("#edit_id").val(id)
            $("#edit_category_name").val(category_name)
            $("#edit_description").val(description)

            $('#edit_modal').modal('show');

        });

        $("#edit_data_form").on("submit", function(e) {
            e.preventDefault();

            let dataForm = $(this).serialize();
            console.log("dataForm Edit:", dataForm);

            Swal.fire({
                title: "Are you sure?",
                text: "Update this Categories!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Confirm!"
            }).then((result) => {
                $.ajax({
                    url: "<?= base_url('master_data/update_categories') ?>",
                    type: "POST",
                    data: dataForm,
                    dataType: "json",
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message
                            }).then(() => {
                                $('#edit_modal').modal('hide');
                                get_table();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: res.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Something went wrong: ' + error, 'error');
                    }
                });
            })
        });

        $(document).on("click", ".delete-btn", function() {
            let id = $(this).data("id");

            Swal.fire({
                title: "Are you sure?",
                text: "Delete this Categories!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Confirm!"
            }).then((result) => {
                $.ajax({
                    url: "<?= base_url('master_data/delete_categories') ?>",
                    type: "POST",
                    data: {
                        id: id
                    },
                    dataType: "json",
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message
                            }).then(() => {
                                get_table();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: res.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Something went wrong: ' + error, 'error');
                    }
                });
            })


        });

    });
</script>