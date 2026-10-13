<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;

/** Relatórios de vendas (US10): por dia, semana ou mês, com exportação CSV. */
final class ReportController extends Controller
{
    public function index(): void
    {
        [$from, $to, $groupBy] = $this->filters();
        $this->view('admin/relatorios/index', [
            'title' => 'Relatórios de vendas',
            'from' => $from,
            'to' => $to,
            'groupBy' => $groupBy,
            'report' => (new Order())->salesReport($from, $to, $groupBy),
        ], 'layouts/admin');
    }

    public function csv(): void
    {
        [$from, $to, $groupBy] = $this->filters();
        $report = (new Order())->salesReport($from, $to, $groupBy);

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"vendas_{$from}_{$to}.csv\"");
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM: acentos corretos no Excel
        fputcsv($out, ['Período', 'Pedidos', 'Faturamento (R$)'], ';');
        foreach ($report['linhas'] as $row) {
            fputcsv($out, [$row['periodo'], $row['pedidos'], number_format((float) $row['faturamento'], 2, ',', '')], ';');
        }
        fputcsv($out, ['Total', $report['totais']['pedidos'], number_format($report['totais']['faturamento'], 2, ',', '')], ';');
        fclose($out);
    }

    /** @return array{0:string, 1:string, 2:string} */
    private function filters(): array
    {
        $valid = static fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
        $from = $this->request->query('de');
        $to = $this->request->query('ate');
        $from = $valid($from) ? $from : date('Y-m-01');
        $to = $valid($to) ? $to : date('Y-m-d');
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        $groupBy = in_array($this->request->query('agrupar'), ['dia', 'semana', 'mes'], true) ? $this->request->query('agrupar') : 'dia';
        return [$from, $to, $groupBy];
    }
}
