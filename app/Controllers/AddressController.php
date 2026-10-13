<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Address;
use App\Models\DeliveryZone;
use App\Services\Validator;

/** Endereços salvos (US15): até 3 por conta, visíveis apenas para o dono. */
final class AddressController extends Controller
{
    public function index(): void
    {
        $addresses = (new Address())->forUser(Auth::id());
        $zones = new DeliveryZone();
        foreach ($addresses as &$address) {
            $address['zona'] = $zones->findByCep($address['cep']);
        }
        unset($address);

        $this->view('enderecos/index', [
            'title' => 'Meus endereços',
            'addresses' => $addresses,
            'max' => Address::MAX_PER_USER,
            'return' => $this->request->query('voltar') === 'checkout' ? 'checkout' : '',
        ]);
    }

    public function store(): void
    {
        $model = new Address();
        $back = '/enderecos' . ($this->request->input('voltar') === 'checkout' ? '?voltar=checkout' : '');
        if ($model->countForUser(Auth::id()) >= Address::MAX_PER_USER) {
            Session::flash('error', 'Você já tem 3 endereços salvos. Exclua um para cadastrar outro.');
            redirect($back);
        }
        [$data, $errors] = Validator::address($this->request->all());
        if ($errors !== []) {
            $this->backWithErrors($back, $errors);
        }
        $model->create(Auth::id(), $data);
        Session::flash('success', 'Endereço salvo.');
        redirect($this->request->input('voltar') === 'checkout' ? '/checkout' : '/enderecos');
    }

    public function edit(int $id): void
    {
        $address = $this->owned($id);
        $this->view('enderecos/edit', ['title' => 'Editar endereço', 'address' => $address]);
    }

    public function update(int $id): void
    {
        $this->owned($id);
        [$data, $errors] = Validator::address($this->request->all());
        if ($errors !== []) {
            $this->backWithErrors("/enderecos/{$id}/editar", $errors);
        }
        (new Address())->update($id, Auth::id(), $data);
        Session::flash('success', 'Endereço atualizado.');
        redirect('/enderecos');
    }

    public function destroy(int $id): void
    {
        $this->owned($id);
        (new Address())->delete($id, Auth::id());
        Session::flash('success', 'Endereço excluído.');
        redirect('/enderecos');
    }

    private function owned(int $id): array
    {
        $address = (new Address())->findForUser($id, Auth::id());
        if ($address === null) {
            throw new HttpException(404, 'Endereço não encontrado.');
        }
        return $address;
    }
}
