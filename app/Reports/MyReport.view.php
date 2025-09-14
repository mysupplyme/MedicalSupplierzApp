<?php
use \koolreport\widgets\koolphp\Table;
use \koolreport\widgets\google\PieChart;
use \koolreport\widgets\google\GeoChart;
use \koolreport\widgets\google\ColumnChart;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Business Intelligence Dashboard</title>
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background: #343a40;
            padding-top: 20px;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 10px 20px;
            border-radius: 0;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        .sidebar-header {
            color: #fff;
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #495057;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-chart-bar"></i> BI Dashboard</h4>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="#" data-section="overview">
                <i class="fas fa-tachometer-alt"></i> Overview
            </a>
            <a class="nav-link" href="#" data-section="suppliers">
                <i class="fas fa-building"></i> All Suppliers
            </a>
            <a class="nav-link" href="#" data-section="top-suppliers">
                <i class="fas fa-trophy"></i> Top Suppliers
            </a>
            <a class="nav-link" href="#" data-section="active-clients">
                <i class="fas fa-users"></i> Active Clients
            </a>
            <a class="nav-link" href="#" data-section="doctors">
                <i class="fas fa-user-md"></i> Active Doctors
            </a>
            <a class="nav-link" href="#" data-section="buyers">
                <i class="fas fa-shopping-cart"></i> Active Buyers
            </a>
            <a class="nav-link" href="#" data-section="geographic">
                <i class="fas fa-globe"></i> Geographic Analysis
            </a>
            <a class="nav-link" href="#" data-section="suppliers-quotes">
                <i class="fas fa-file-contract"></i> Suppliers with Quotes
            </a>
            <a class="nav-link" href="#" data-section="buyers-quotes">
                <i class="fas fa-handshake"></i> Buyers with Quotes
            </a>
        </nav>
    </div>

    <div class="main-content">
        <!-- Overview Section -->
        <div id="overview" class="section active">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-chart-bar"></i> Medical Supplierz Report - <?php echo date('d-m-Y'); ?></h2>
                <button class="btn btn-primary" onclick="emailReport()">
                    <i class="fas fa-envelope"></i> Email Report
                </button>
            </div>
            
            <h4><i class="fas fa-box"></i> Products</h4>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Total B2B Products</h6>
                            <h3><?php echo $this->dataStore("count_products")->count(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Total Association Products</h6>
                            <h3><?php echo $this->dataStore("count_association_products")->count(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Total Conferences</h6>
                            <h3><?php echo $this->dataStore("count_conferences")->count(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h6>Total Workshops</h6>
                            <h3><?php echo $this->dataStore("count_workshops")->count(); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h6>Total Expos</h6>
                            <h3><?php echo $this->dataStore("count_expos")->count(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body text-center">
                            <h6>Total Webinars</h6>
                            <h3><?php echo $this->dataStore("count_webinars")->count(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h6>Third Categories with Products</h6>
                            <h3 class="text-primary"><?php echo $this->dataStore("third_category_with_products")->count(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <h6>Third Categories without Products</h6>
                            <h3 class="text-danger"><?php echo $this->dataStore("third_category_without_products")->count(); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <h4><i class="fas fa-users"></i> Users</h4>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Total Active Suppliers</h6>
                            <h3><?php echo $this->dataStore("active_clients")->count(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6>Total InActive Suppliers</h6>
                            <h3><?php echo $this->dataStore("clients")->count() - $this->dataStore("active_clients")->count(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Total Doctors</h6>
                            <h3><?php echo $this->dataStore("active_doctors")->count(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h6>Total Buyers</h6>
                            <h3><?php echo $this->dataStore("active_buyers")->count(); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <h4><i class="fas fa-file-invoice"></i> Quotations</h4>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Total Quotations</h6>
                            <h3><?php echo $this->dataStore("total_quotations")->toArray()[0]['total_quotations'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Suppliers with Quotes</h6>
                            <h3><?php echo $this->dataStore("suppliers_with_quotes_count")->toArray()[0]['supplier_count'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Buyers who Requested</h6>
                            <h3><?php echo $this->dataStore("buyers_with_quotes_count")->toArray()[0]['buyer_count'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <h4><i class="fas fa-clock"></i> Latest 5 Suppliers</h4>
            <div class="row">
                <div class="col-12">
                    <?php
                    Table::create(array(
                        "dataSource" => $this->dataStore("clients"),
                        "columns" => array(
                            "company_name_en" => array("label" => "Company Name"),
                            "country_code" => array("label" => "Country"),
                            "created_at" => array("label" => "Registration Date"),
                            "profile_percentage" => array(
                                "label" => "Profile %",
                                "type" => "number",
                                "suffix" => "%"
                            ),
                            "status" => array("label" => "Status")
                        ),
                        "sorting" => array("created_at" => "desc"),
                        "limit" => 5,
                        "themeBase" => "bs4",
                        "cssClass" => array(
                            "table" => "table table-striped table-bordered table-sm"
                        )
                    ));
                    ?>
                </div>
            </div>
        </div>

        <!-- All Suppliers Section -->
        <div id="suppliers" class="section">
            <h2><i class="fas fa-building"></i> All Suppliers</h2>
            <?php
            Table::create(array(
                "dataSource" => $this->dataStore("clients"),
                "columns" => [
                    "SlNum" => array("label" => "#"),
                    "company_name_en" => array("label" => "Company Name"),
                    "country_code" => array("label" => "Country"),
                    "created_at" => array("label" => "Registration Date"),
                    "profile_percentage" => array(
                        "label" => "Profile Percentage",
                        "type" => "number",
                        "suffix" => "%"
                    ),
                    "status" => array("label" => "Active / Inactive status"),
                    "email" => array("label" => "Email"),
                    "mobile_number" => array("label" => "Mobile No."),
                    "product_count" => array("label" => "Product Count")
                ],
                "sorting" => array("created_at" => "desc"),
                "themeBase" => "bs4",
                "cssClass" => array(
                    "table" => "table table-striped table-bordered my-kool-table"
                )
            ));
            ?>
        </div>

        <!-- Top Suppliers Section -->
        <div id="top-suppliers" class="section">
            <h2><i class="fas fa-trophy"></i> Top Suppliers by Product Count</h2>
            <?php
            $totalRows = $this->dataStore("supplier_with_productCount")->count();
            $index = $totalRows;        
            Table::create(array(
                "dataSource" => $this->dataStore("supplier_with_productCount"),
                "columns" => array(
                    "SlNum" => array(
                        "label" => "#",
                        "class" => "serial-column",
                        "formatValue" => function($value, $row) use (&$index) {
                            return $index--;
                        }),
                    "client_name" => array("label" => "Company Name"),
                    "profile_percentage" => array("label" => "Percentage"),
                    "product_count" => array("label" => "Product Count"),
                ),
                "themeBase" => "bs4",
                "cssClass" => array(
                    "table" => "table table-striped table-bordered my-kool-table"
                )
            ));
            ?>
        </div>

        <!-- Active Clients Section -->
        <div id="active-clients" class="section">
            <h2><i class="fas fa-users"></i> Active Clients</h2>
            <?php
            Table::create(array(
                "dataSource" => $this->dataStore("active_clients"),
                "columns" => array(
                    "SlNum" => array("label" => "#"),
                    "company_name_en" => array("label" => "Company Name"),
                    "country_code" => array("label" => "Country"),
                    "profile_percentage" => array(
                        "label" => "Profile %",
                        "type" => "number",
                        "suffix" => "%"
                    ),
                    "status" => array("label" => "Status")
                ),
                "themeBase" => "bs4",
                "cssClass" => array(
                    "table" => "table table-striped table-bordered"
                )
            ));
            ?>
        </div>

        <!-- Active Doctors Section -->
        <div id="doctors" class="section">
            <h2><i class="fas fa-user-md"></i> Active Doctors</h2>
            <?php
            Table::create(array(
                "dataSource" => $this->dataStore("active_doctors"),
                "columns" => array(
                    "id" => array("label" => "ID"),
                    "company_name_en" => array("label" => "Name"),
                    "country_code" => array("label" => "Country"),
                    "email" => array("label" => "Email"),
                    "mobile_number" => array("label" => "Mobile")
                ),
                "themeBase" => "bs4",
                "cssClass" => array(
                    "table" => "table table-striped table-bordered"
                )
            ));
            ?>
        </div>

        <!-- Active Buyers Section -->
        <div id="buyers" class="section">
            <h2><i class="fas fa-shopping-cart"></i> Active Buyers</h2>
            <?php
            Table::create(array(
                "dataSource" => $this->dataStore("active_buyers"),
                "columns" => array(
                    "id" => array("label" => "ID"),
                    "company_name_en" => array("label" => "Name"),
                    "country_code" => array("label" => "Country"),
                    "email" => array("label" => "Email"),
                    "mobile_number" => array("label" => "Mobile")
                ),
                "themeBase" => "bs4",
                "cssClass" => array(
                    "table" => "table table-striped table-bordered"
                )
            ));
            ?>
        </div>

        <!-- Geographic Analysis Section -->
        <div id="geographic" class="section">
            <h2><i class="fas fa-globe"></i> Geographic Analysis</h2>
            <div class="row">
                <div class="col-md-6">
                    <?php
                    GeoChart::create(array(
                        "dataSource" => $this->dataStore("clients_by_country"),
                        "columns" => array(
                            "country_code" => array("label" => "Country"),
                            "client_count" => array("label" => "Number of Clients")
                        ),
                        "options" => array(
                            "title" => "Client Distribution by Country",
                            "colorAxis" => array("colors" => ["#e0f3f8", "#084081"])
                        )
                    ));
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                    PieChart::create(array(
                        "dataSource" => $this->dataStore("clients_by_country"),
                        "columns" => array("country_code", "client_count"),
                        "options" => array("title" => "Supplier Distribution by Country"),
                    ));
                    ?>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <?php
                    ColumnChart::create(array(
                        "dataSource" => $this->dataStore("clients_by_country"),
                        "columns" => array("country_code", "client_count"),
                        "options" => array(
                            "title" => "Supplier Count by Country",
                            "hAxis" => array("title" => "Country"),
                            "vAxis" => array("title" => "Number of Suppliers"),
                            "legend" => "none"
                        )
                    ));
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                    ColumnChart::create(array(
                        "dataSource" => $this->dataStore("buyers_by_country"),
                        "columns" => array("country_code", "client_count"),
                        "options" => array(
                            "title" => "Buyer Count by Country",
                            "hAxis" => array("title" => "Country"),
                            "vAxis" => array("title" => "Number of Buyers"),
                            "legend" => "none"
                        )
                    ));
                    ?>
                </div>
            </div>
        </div>

        <!-- Suppliers with Quotes Section -->
        <div id="suppliers-quotes" class="section">
            <h2><i class="fas fa-file-contract"></i> Suppliers with Quotes</h2>
            <?php
            Table::create(array(
                "dataSource" => $this->dataStore("suppliers_quotes_detail"),
                "columns" => array(
                    "company_name_en" => array("label" => "Company Name"),
                    "country_code" => array("label" => "Country"),
                    "email" => array("label" => "Email"),
                    "quote_count" => array("label" => "Quote Count")
                ),
                "sorting" => array("quote_count" => "desc"),
                "themeBase" => "bs4",
                "cssClass" => array(
                    "table" => "table table-striped table-bordered"
                )
            ));
            ?>
        </div>

        <!-- Buyers with Quotes Section -->
        <div id="buyers-quotes" class="section">
            <h2><i class="fas fa-handshake"></i> Buyers with Quotes</h2>
            <?php
            Table::create(array(
                "dataSource" => $this->dataStore("buyers_quotes_detail"),
                "columns" => array(
                    "company_name_en" => array("label" => "Company Name"),
                    "country_code" => array("label" => "Country"),
                    "email" => array("label" => "Email"),
                    "quote_count" => array("label" => "Quote Count")
                ),
                "sorting" => array("quote_count" => "desc"),
                "themeBase" => "bs4",
                "cssClass" => array(
                    "table" => "table table-striped table-bordered"
                )
            ));
            ?>
        </div>


    </div>

    <?php
    $countries = $this->dataStore("clients_by_country");
    $countryArray = $countries->toArray();
    $countryCodes = array_column($countryArray, 'country_code');
    ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Sidebar navigation
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        const sections = document.querySelectorAll('.section');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                navLinks.forEach(l => l.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));
                
                this.classList.add('active');
                
                const sectionId = this.getAttribute('data-section');
                document.getElementById(sectionId).classList.add('active');
            });
        });
        
        var countries = <?php echo json_encode($countryCodes); ?>;
        
        setTimeout(() => {
            addTableFilters();
            addTableStyling();
            
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('column-filter')) {
                    filterTable(e.target.closest('table'));
                }
            });
            
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('column-filter')) {
                    filterTable(e.target.closest('table'));
                }
            });
        }, 1000);
        
        function addTableFilters() {
            const tables = document.querySelectorAll("table");
            
            tables.forEach(table => {
                const header = table.querySelector("thead tr");
                if (!header || table.querySelector('.filter-row')) return;
                
                const filterRow = document.createElement("tr");
                filterRow.className = 'filter-row';
                
                [...header.children].forEach((th, index) => {
                    const filterCell = document.createElement("th");
                    const columnText = th.textContent.toLowerCase();
                    
                    if (columnText.includes('country')) {
                        let dropdownHTML = `<select class="form-control form-control-sm column-filter" data-column="${index}"><option value="">All Countries</option>`;
                        countries.forEach(country => {
                            dropdownHTML += `<option value="${country}">${country}</option>`;
                        });
                        dropdownHTML += `</select>`;
                        filterCell.innerHTML = dropdownHTML;
                    } else if (columnText.includes('status') || columnText.includes('active')) {
                        filterCell.innerHTML = `<select class="form-control form-control-sm column-filter" data-column="${index}"><option value="">All Status</option><option value="1">Active</option><option value="0">Inactive</option></select>`;
                    } else if (columnText.includes('#') || columnText.includes('id')) {
                        filterCell.innerHTML = '';
                    } else {
                        filterCell.innerHTML = `<input type="text" class="form-control form-control-sm column-filter" placeholder="Filter ${th.textContent}..." data-column="${index}">`;
                    }
                    
                    filterRow.appendChild(filterCell);
                });
                
                table.querySelector("thead").appendChild(filterRow);
            });
        }
        
        function addTableStyling() {
            const tables = document.querySelectorAll("table");
            
            tables.forEach(table => {
                const rows = table.querySelectorAll("tbody tr");
                
                rows.forEach(row => {
                    const cells = row.cells;
                    let statusCell = null;
                    let profileCell = null;
                    
                    // Find status and profile columns by checking cell content
                    for (let i = 0; i < cells.length; i++) {
                        const cellText = cells[i].textContent.trim();
                        if (cellText === '0' || cellText === '1') {
                            statusCell = cells[i];
                        }
                        if (cellText.includes('%')) {
                            profileCell = cells[i];
                        }
                    }
                    
                    if (statusCell && profileCell) {
                        const status = parseInt(statusCell.textContent.trim());
                        const profile = parseInt(profileCell.textContent.trim().replace('%', ''));
                        
                        if (status === 1 && profile !== 100) {
                            row.style.backgroundColor = "#fff3cd";
                            row.style.color = "#856404";
                            row.style.fontWeight = "bold";
                        } else if (status === 0 && profile == 100) {
                            row.style.backgroundColor = "#268504";
                            row.style.fontWeight = "bold";
                        } else if (status === 0) {
                            row.style.backgroundColor = "#ffcccc";
                            row.style.color = "red";
                        }
                    }
                });
            });
        }
        
        function filterTable(table) {
            const rows = table.querySelectorAll("tbody tr");
            const filters = table.querySelectorAll(".column-filter");
            
            rows.forEach(row => {
                let show = true;
                
                filters.forEach(filter => {
                    const colIndex = filter.getAttribute("data-column");
                    const filterVal = filter.value.toLowerCase().trim();
                    const cellText = row.children[colIndex]?.textContent.toLowerCase().trim();
                    
                    if (filterVal && !cellText.includes(filterVal)) {
                        show = false;
                    }
                });
                
                row.style.display = show ? "" : "none";
            });
            
            updateSerialNumbers(table);
        }
        
        function updateSerialNumbers(table) {
            const visibleRows = table.querySelectorAll("tbody tr:not([style*='display: none'])");
            let serialNumber = 1;
            
            visibleRows.forEach(row => {
                const serialCell = row.querySelector('td:first-child');
                if (serialCell && !isNaN(parseInt(serialCell.textContent))) {
                    serialCell.textContent = serialNumber++;
                }
            });
        }
        
        window.emailReport = function() {
            if (confirm('Send this report via email?')) {
                fetch('/reports/email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        report_date: new Date().toISOString().split('T')[0]
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            alert('Report sent successfully!');
                        } else {
                            alert('Failed to send report: ' + (data.message || 'Unknown error'));
                        }
                    } catch (e) {
                        console.log('Response:', text);
                        alert('Report request completed, but response format was unexpected.');
                    }
                })
                .catch(error => {
                    alert('Error sending report: ' + error.message);
                });
            }
        };
    });
    </script>
</body>
</html>