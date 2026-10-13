<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Models\DeliveryZone;
use App\Models\Setting;
use App\Services\Validator;

/** Parâmetros de entrega e taxas definidos pelo gerente (US04 RN#1/RN#2, US06 RN#1/RN#2). */
final class SettingsController extends Controller
{
    public function index(): void
    {
        $this->view('admin/configuracoes/index', [
            'title' => 'Configurações',
            'settings' => (new Setting())->values(),
            'zones' => (new DeliveryZone())->all(),
        ], 'layouts/admin');
    }

    public function update(): void
    {
        $in = $this->request->all();
        $errors = [];
        $values = [];

        foreach (['hora_inicio', 'hora_fim'] as $key) {
            if (!preg_match('/^([01]\d|2[0-3]):00$/', (string) ($in[$key] ?? ''))) {
                $errors[$key] = 'Use horário cheio, por exemplo 09:00.';
            }
            $values[$key] = (string) ($in[$key] ?? '');
        }
        if (!isset($errors['hora_inicio']) && !isset($errors['hora_fim']) &&($values['hora_inicio'] >= $values['hora_fim'])) {
            $errors['hora_fim'] = 'O fim deve ser depois do início.';
        }
        foreach (['antecedencia_horas' => [0, 72], 'dias_maximos' => [1, 90]] as $key => [$min, $max]) {
            $v = (string) ($in[$key] ?? '');
            if (!ctype_digit($v) || (int) $v < $min || (int) $v > $max) {
                $errors[$key] = "Informe um número inteiro entre {$min} e {$max}.";
            }
            $values[$key] = $v;
        }
        foreach (['valor_minimo', 'taxa_pedido_minimo', 'taxa_urgencia'] as $key) {
            $money = Validator::money((string) ($in[$key] ?? ''));
            if ($money === null || $money > 9999) {
                $errors[$key] = 'Informe um valor válido, por exemplo 10,00.';
            }
            $values[$key] = $money !== null ? number_format($money, 2, '.', '') : '';
        }

        if ($errors !== []) {
            $this->backWithErrors('/admin/configuracoes', $errors);
        }
        (new Setting())->save($values);
        Session::flash('success', 'Configurações salvas.');
        redirect('/admin/configuracoes');
    }
}
