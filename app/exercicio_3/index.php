<?php


$reportGenerator = new ComplexReportGenerator();
$complexReport = $reportGenerator->generateComplexReport();

echo "<p>Relatório complexo gerado com {$complexReport['summary']['totalRegions']} regiões</p>";
echo "<p>Total geral: R$ {$complexReport['summary']['grandTotal']}</p>";
echo "<p>Lucro total: R$ {$complexReport['summary']['grandProfit']}</p>";

// Faça mais testes para entender e garantir o funcionamento.

?>
