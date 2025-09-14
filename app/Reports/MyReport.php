<?php
namespace App\Reports;
use \koolreport\processes\CalculatedColumn;
use \koolreport\processes\Filter;
class MyReport extends \koolreport\KoolReport
{
    use \koolreport\laravel\Friendship;
    // By adding above statement, you have claim the friendship between two frameworks
    // As a result, this report will be able to accessed all databases of Laravel
    // There are no need to define the settings() function anymore
    // while you can do so if you have other datasources rather than those
    // defined in Laravel.
    function settings()
    {
        return array(
            "dataSources"=>array(
                "api"=>array(
                    "connectionString"=>"mysql:host=localhost;dbname=api",
                    "username"=>"root",
                    "password"=>"123456",
                    "charset"=>"utf8"
                ),
            )
        ); 
    }
    function setup()
    {
        // Let say, you have "sale_database" is defined in Laravel's database settings.
        // List of Suppliers country wise

        $this->src("mysql")
    ->query("SELECT country_code, COUNT(*) as client_count 
             FROM clients 
             WHERE type = 'supplier' 
             AND created_at > '2024-02-08 23:59:59' 
             AND supplier_type NOT LIKE 'Association' 
             AND deleted_at IS NULL 
             AND status = 1
             GROUP BY country_code")
    ->pipe($this->dataStore("clients_by_country"));

    $this->src("mysql")
    ->query("SELECT country_code, COUNT(*) as client_count 
             FROM clients 
             WHERE type = 'buyer' 
             AND created_at > '2024-02-08 23:59:59' 
             AND deleted_at IS NULL 
             GROUP BY country_code")
    ->pipe($this->dataStore("buyers_by_country"));

    //     // List of Suppliers
    //     $this->src("mysql")
    //     ->query("SELECT company_name_en, country_code, created_at, profile_percentage, email, mobile_number, status FROM clients 
    //              WHERE type = 'supplier' and created_at > '2024-02-08 23:59:59' and supplier_type not like 'Association' and deleted_at is NULL ")
    //     ->pipe(new CalculatedColumn(array(
    //                 "SlNum"=>"{#}+1",
    //                 // "total"=>"{hour_rate}*{working_hours}"
    //     )))
        
    //    ->pipe($this->dataStore("clients"));  

        // List of Suppliers
        $this->src("mysql")
        ->query("SELECT 
    c.company_name_en, 
    c.country_code, 
    c.created_at, 
    c.profile_percentage, c.email, c.mobile_number,
    c.status,
    IFNULL(p.product_count, 0) AS product_count
FROM 
    clients c
LEFT JOIN (
    SELECT 
        client_id, 
        COUNT(*) AS product_count
    FROM 
        product_suppliers
    GROUP BY 
        client_id
) p ON c.id = p.client_id
WHERE 
    c.type = 'supplier' 
    AND c.created_at > '2024-02-08 23:59:59' 
    AND c.supplier_type NOT LIKE 'Association'
ORDER BY 
    product_count DESC;
 ")
        ->pipe(new CalculatedColumn(array(
                    "SlNum"=>"{#}+1",
                    // "total"=>"{hour_rate}*{working_hours}"
        )))
        
       ->pipe($this->dataStore("clients")); 
   // Active Clients
    
       $this->src("mysql")
        ->query("SELECT company_name_en, country_code, created_at, profile_percentage, status FROM clients 
                 WHERE type = 'supplier' and created_at > '2024-02-08 23:59:59' and supplier_type not like 'Association' ")
        ->pipe(new CalculatedColumn(array(
                    "SlNum"=>"{#}",
                    // "total"=>"{hour_rate}*{working_hours}"
        )))
        ->pipe(new Filter(array(
            array("status",">",0)
        )))
       ->pipe($this->dataStore("active_clients"));  
// Active doctors
       $this->src("mysql")
        ->query("SELECT * FROM clients WHERE type = 'buyer' and buyer_type like 'Doctor' and status = 1 and deleted_at IS NULL")

       ->pipe($this->dataStore("active_doctors"));  
       // Active doctors
       $this->src("mysql")
        ->query("SELECT * FROM clients WHERE type = 'buyer' and buyer_type is NULL and status = 1 and deleted_at IS NULL;")
        ->pipe($this->dataStore("active_buyers"));       
// Product Count
        $this->src("mysql")
        ->query("SELECT * FROM product_supplier_b2b where type LIKE 'b2b'")
        ->pipe($this->dataStore("count_products"));

// Association Product Count
$this->src("mysql")
->query("SELECT product_suppliers.id, products.title_en, product_suppliers.conference_datetime, product_suppliers.status 
FROM `clients`
JOIN `product_suppliers` ON `product_suppliers`.`client_id` = `clients`.`id`
JOIN `products` ON `products`.`id` = `product_suppliers`.`product_id`
AND `clients`.supplier_type like 'Association'")
->pipe($this->dataStore("count_association_products"));

$this->src("mysql")
->query("SELECT product_suppliers.*
FROM product_suppliers
INNER JOIN category_product 
    ON category_product.product_id = product_suppliers.product_id
WHERE category_product.category_id = 3021;")
->pipe($this->dataStore("count_conferences"));

$this->src("mysql")
->query("SELECT product_suppliers.*
FROM product_suppliers
INNER JOIN category_product 
    ON category_product.product_id = product_suppliers.product_id
WHERE category_product.category_id = 3022;")
->pipe($this->dataStore("count_expos"));

$this->src("mysql")
->query("SELECT product_suppliers.*
FROM product_suppliers
INNER JOIN category_product 
    ON category_product.product_id = product_suppliers.product_id
WHERE category_product.category_id = 3020;")
->pipe($this->dataStore("count_workshops"));

$this->src("mysql")
->query("SELECT product_suppliers.*
FROM product_suppliers
INNER JOIN category_product 
    ON category_product.product_id = product_suppliers.product_id
WHERE category_product.category_id = 3422;")
->pipe($this->dataStore("count_webinars"));
// Third category without products
$this->src("mysql")
->query("SELECT c1.id, c1.title_en
FROM categories c1 
LEFT JOIN categories c2 ON c1.id = c2.category_id 
WHERE c2.id IS NULL
AND c1.id NOT IN (SELECT category_id FROM `category_product`);")
->pipe($this->dataStore("third_category_without_products"));

// Third category with products
$this->src("mysql")
->query("SELECT c1.id, c1.title_en
FROM categories c1
LEFT JOIN categories c2 ON c1.id = c2.category_id 
WHERE c2.id IS NULL
AND c1.id NOT IN (
    SELECT cp.category_id
    FROM category_product cp
    INNER JOIN products p ON cp.product_id = p.id
    WHERE p.status = 1
);")
->pipe($this->dataStore("third_category_with_products"));


// Supplier wise products count
$this->src("mysql")
->query("SELECT 
    c.company_name_en AS client_name, c.profile_percentage,
    ps.client_id,
    COUNT(*) AS product_count
FROM 
    product_suppliers ps
JOIN 
    clients c ON ps.client_id = c.id
WHERE 
    c.supplier_type NOT LIKE 'Association'
GROUP BY 
    ps.client_id, c.company_name_en
ORDER BY 
    product_count DESC;

")
->pipe($this->dataStore("supplier_with_productCount"));

// Quotations data - using client_quotes table
$this->src("mysql")
->query("SELECT COUNT(*) as total_quotations FROM client_quotes")
->pipe($this->dataStore("total_quotations"));

// Suppliers who received quotations
$this->src("mysql")
->query("SELECT c.company_name_en, COUNT(cq.id) as quotation_count 
         FROM client_quotes cq 
         JOIN clients c ON cq.client_id = c.id 
         WHERE c.type = 'supplier' 
         GROUP BY c.id, c.company_name_en 
         ORDER BY quotation_count DESC 
         LIMIT 10")
->pipe($this->dataStore("suppliers_quotations"));

// Count of suppliers who received quotations
$this->src("mysql")
->query("SELECT COUNT(DISTINCT cq.supplier_id) as supplier_count 
         FROM client_quotes cq")
->pipe($this->dataStore("suppliers_with_quotes_count"));

// Count of buyers who requested quotations
$this->src("mysql")
->query("SELECT COUNT(DISTINCT cq.client_id) as buyer_count 
         FROM client_quotes cq")
->pipe($this->dataStore("buyers_with_quotes_count"));

// Detailed suppliers with quotations
$this->src("mysql")
->query("SELECT c.company_name_en, c.country_code, c.email, COUNT(cq.id) as quote_count 
         FROM client_quotes cq 
         JOIN clients c ON cq.supplier_id = c.id 
         WHERE c.type = 'supplier' 
         GROUP BY c.id, c.company_name_en, c.country_code, c.email 
         ORDER BY quote_count DESC")
->pipe($this->dataStore("suppliers_quotes_detail"));

// Detailed buyers with quotations
$this->src("mysql")
->query("SELECT c.company_name_en, c.country_code, c.email, COUNT(cq.id) as quote_count 
         FROM client_quotes cq 
         JOIN clients c ON cq.client_id = c.id 
         WHERE c.type = 'buyer' 
         GROUP BY c.id, c.company_name_en, c.country_code, c.email 
         ORDER BY quote_count DESC")
->pipe($this->dataStore("buyers_quotes_detail"));

        
        // $node->pipe(new Limit(array(50)))
        // ->pipe($this->dataStore("recent_products"));
        
        // $node->pipe(new Group(array(
        //     "by"=>"customerNumber",
        //     "sum"=>"amount"
        // )))
        // ->pipe($this->dataStore("sale_by_customer"));


    }
}
