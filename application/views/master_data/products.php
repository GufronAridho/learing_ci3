<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <h3 class="mb-0">
                        <i class="bi bi-pencil-square"></i> Products Master Data
                    </h3>
                </div>
                <div class="col-12 col-md-6">
                    <ol class="breadcrumb float-md-end mb-0">
                        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
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
                                <i class="bi bi-table"></i> <strong>PRODUCTS LIST</strong>
                            </h5>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <div class="d-flex flex-wrap justify-content-md-end gap-1">
                                <button class="btn btn-success btn-sm">
                                    <i class="bi bi-plus-circle"></i> Add Product
                                </button>
                                <button class="btn btn-info btn-sm text-white">
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
                    <!-- Contoh responsive table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>John Doe</td>
                                    <td>john@example.com</td>
                                    <td>Admin</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Jane Smith</td>
                                    <td>jane@example.com</td>
                                    <td>User</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>