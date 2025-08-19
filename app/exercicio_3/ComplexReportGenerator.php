<?php

class ComplexReportGenerator
{
    private array $salesData;
    private array $customers;
    private array $products;

    public function __construct()
    {
        $this->salesData = [
            ['customerId' => 1, 'productId' => 1, 'categoryId' => 1, 'regionId' => 1, 'amount' => 100, 'date' => '2024-01-01'],
            ['customerId' => 1, 'productId' => 2, 'categoryId' => 2, 'regionId' => 1, 'amount' => 200, 'date' => '2024-01-02'],
            ['customerId' => 2, 'productId' => 1, 'categoryId' => 1, 'regionId' => 2, 'amount' => 150, 'date' => '2024-01-03'],
            ['customerId' => 2, 'productId' => 3, 'categoryId' => 3, 'regionId' => 2, 'amount' => 300, 'date' => '2024-01-04'],
            ['customerId' => 3, 'productId' => 2, 'categoryId' => 2, 'regionId' => 1, 'amount' => 250, 'date' => '2024-01-05'],
            ['customerId' => 3, 'productId' => 3, 'categoryId' => 3, 'regionId' => 3, 'amount' => 400, 'date' => '2024-01-06'],
        ];

        $this->customers = [
            1 => ['name' => 'João Silva', 'tier' => 'premium'],
            2 => ['name' => 'Maria Santos', 'tier' => 'standard'],
            3 => ['name' => 'Pedro Costa', 'tier' => 'premium']
        ];

        $this->products = [
            1 => ['name' => 'Produto A', 'cost' => 50],
            2 => ['name' => 'Produto B', 'cost' => 80],
            3 => ['name' => 'Produto C', 'cost' => 120]
        ];
    }

    public function generateComplexReport(): array
    {
        $complexReport = [];

        for ($regionId = 1; $regionId <= 3; $regionId++) {
            $regionData = [];
            $regionTotal = 0;
            $regionProfit = 0;
            $regionCustomers = [];
            $regionCategories = [];

            for ($categoryId = 1; $categoryId <= 3; $categoryId++) {
                $categoryData = [];
                $categoryTotal = 0;
                $categoryProfit = 0;
                $categoryCustomers = [];
                $categoryProducts = [];

                for ($customerId = 1; $customerId <= 3; $customerId++) {
                    $customerData = [];
                    $customerTotal = 0;
                    $customerProfit = 0;
                    $customerProducts = [];
                    $customerOrders = 0;

                    // Buscar vendas para esta combinação específica
                    foreach ($this->salesData as $sale) {
                        if ($sale['regionId'] == $regionId && 
                            $sale['categoryId'] == $categoryId && 
                            $sale['customerId'] == $customerId) {
                            
                            $customerTotal += $sale['amount'];
                            $customerOrders++;
                            
                            // Calcular lucro
                            $productCost = $this->products[$sale['productId']]['cost'];
                            $profit = $sale['amount'] - $productCost;
                            $customerProfit += $profit;
                            
                            // Armazenar dados do produto
                            if (!isset($customerProducts[$sale['productId']])) {
                                $customerProducts[$sale['productId']] = [
                                    'name' => $this->products[$sale['productId']]['name'],
                                    'totalSales' => 0,
                                    'totalProfit' => 0,
                                    'orderCount' => 0
                                ];
                            }
                            
                            $customerProducts[$sale['productId']]['totalSales'] += $sale['amount'];
                            $customerProducts[$sale['productId']]['totalProfit'] += $profit;
                            $customerProducts[$sale['productId']]['orderCount']++;
                            
                            // Adicionar aos produtos da categoria
                            if (!isset($categoryProducts[$sale['productId']])) {
                                $categoryProducts[$sale['productId']] = [
                                    'name' => $this->products[$sale['productId']]['name'],
                                    'totalSales' => 0,
                                    'totalProfit' => 0,
                                    'orderCount' => 0
                                ];
                            }
                            
                            $categoryProducts[$sale['productId']]['totalSales'] += $sale['amount'];
                            $categoryProducts[$sale['productId']]['totalProfit'] += $profit;
                            $categoryProducts[$sale['productId']]['orderCount']++;
                        }
                    }

                    // Se o cliente teve vendas nesta região/categoria
                    if ($customerTotal > 0) {
                        $customerData = [
                            'customerId' => $customerId,
                            'customerName' => $this->customers[$customerId]['name'],
                            'customerTier' => $this->customers[$customerId]['tier'],
                            'totalSales' => $customerTotal,
                            'totalProfit' => $customerProfit,
                            'orderCount' => $customerOrders,
                            'averageOrderValue' => $customerTotal / $customerOrders,
                            'profitMargin' => ($customerProfit / $customerTotal) * 100,
                            'products' => $customerProducts
                        ];

                        $categoryCustomers[$customerId] = $customerData;
                        $categoryTotal += $customerTotal;
                        $categoryProfit += $customerProfit;
                        
                        // Adicionar à lista de clientes da região
                        if (!isset($regionCustomers[$customerId])) {
                            $regionCustomers[$customerId] = [
                                'customerId' => $customerId,
                                'customerName' => $this->customers[$customerId]['name'],
                                'customerTier' => $this->customers[$customerId]['tier'],
                                'totalSales' => 0,
                                'totalProfit' => 0,
                                'orderCount' => 0
                            ];
                        }
                        
                        $regionCustomers[$customerId]['totalSales'] += $customerTotal;
                        $regionCustomers[$customerId]['totalProfit'] += $customerProfit;
                        $regionCustomers[$customerId]['orderCount'] += $customerOrders;
                    }
                }

                // Se a categoria teve vendas nesta região
                if ($categoryTotal > 0) {
                    $categoryData = [
                        'categoryId' => $categoryId,
                        'totalSales' => $categoryTotal,
                        'totalProfit' => $categoryProfit,
                        'customerCount' => count($categoryCustomers),
                        'productCount' => count($categoryProducts),
                        'averageSalePerCustomer' => $categoryTotal / count($categoryCustomers),
                        'profitMargin' => ($categoryProfit / $categoryTotal) * 100,
                        'customers' => $categoryCustomers,
                        'products' => $categoryProducts
                    ];

                    $regionCategories[$categoryId] = $categoryData;
                    $regionTotal += $categoryTotal;
                    $regionProfit += $categoryProfit;
                }
            }

            // Se a região teve vendas
            if ($regionTotal > 0) {
                $regionData = [
                    'regionId' => $regionId,
                    'totalSales' => $regionTotal,
                    'totalProfit' => $regionProfit,
                    'customerCount' => count($regionCustomers),
                    'categoryCount' => count($regionCategories),
                    'averageSalePerCustomer' => $regionTotal / count($regionCustomers),
                    'profitMargin' => ($regionProfit / $regionTotal) * 100,
                    'categories' => $regionCategories,
                    'customers' => array_values($regionCustomers)
                ];

                $complexReport['regions'][$regionId] = $regionData;
            }
        }

        // Calcular totais gerais
        $grandTotal = 0;
        $grandProfit = 0;
        $totalCustomers = [];
        $totalCategories = [];

        foreach ($complexReport['regions'] as $region) {
            $grandTotal += $region['totalSales'];
            $grandProfit += $region['totalProfit'];
            
            foreach ($region['customers'] as $customer) {
                $totalCustomers[$customer['customerId']] = true;
            }
            
            foreach ($region['categories'] as $category) {
                $totalCategories[$category['categoryId']] = true;
            }
        }

        $complexReport['summary'] = [
            'grandTotal' => $grandTotal,
            'grandProfit' => $grandProfit,
            'totalRegions' => count($complexReport['regions']),
            'totalCustomers' => count($totalCustomers),
            'totalCategories' => count($totalCategories),
            'overallProfitMargin' => ($grandProfit / $grandTotal) * 100,
            'averageSalePerRegion' => $grandTotal / count($complexReport['regions'])
        ];

        return $complexReport;
    }
}
