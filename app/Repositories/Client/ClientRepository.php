<?php

namespace App\Repositories\Client;

use App\Models\Client\Client;
use App\Models\Client\ClientConfirmation;
use App\Repositories\MasterDataRepository;

class ClientRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Client::class;
    }

    public static function datatable()
    {
        return Client::query();
    }

    public static function getMagicLink($batchName = null, $claimType = null, $pic = null, $data_type = null)
    {
        return Client::query()
            ->whereDoesntHave('esign')
            ->whereDoesntHave('confirmation')
            ->where('clients.claim_type', '!=', Client::CLAIM_TYPE_KOKUMIN)
            ->when($batchName  != 'all', function ($q) use ($batchName) {
                $q->where('clients.source_file_name', $batchName);
            })
            ->when($pic, function ($q) use ($pic) {
                $q->where('clients.pic', 'ILIKE', '%' . $pic . '%');
            })
            ->when($data_type && $data_type === Client::FILTER_DATA_TYPE_CONFIRMATION, function ($q) {
                $q->whereHas('confirmation');
            })
            ->when($data_type && $data_type === Client::FILTER_DATA_TYPE_NOT_CONFIRMATION, function ($q) {
                $q->whereDoesntHave('confirmation')
                    ->whereHas('user');
            })
            ->when($data_type && $data_type === Client::FILTER_DATA_TYPE_REGISTERED, function ($q) {
                $q->whereHas('user');
            })
            ->when($data_type && $data_type === Client::FILTER_DATA_TYPE_NOT_REGISTERED, function ($q) {
                $q->whereDoesntHave('user');
            });
    }

    public static function getClientReEntry($batchName = null, $claimType = null, $pic = null)
    {
        return Client::query()
            ->where('clients.claim_type', '!=', Client::CLAIM_TYPE_KOKUMIN)
            ->join('client_confirmations', function ($j) {
                $j->on('client_confirmations.client_id', '=', 'clients.id')
                    ->where('client_confirmations.claim_type', Client::CLAIM_TYPE_NORMAL)
                    ->where('client_confirmations.current_position', ClientConfirmation::CURRENT_POSITION_INDONESIA)
                    ->where('client_confirmations.back_to_jp_type', ClientConfirmation::BACK_TO_JP_TYPE_YES)
                    ->whereNull('client_confirmations.deleted_at');
            })
            ->when($batchName && $batchName != 'all', function ($q) use ($batchName) {
                $q->where('clients.source_file_name', $batchName);
            })
            ->whereHas('confirmation')
            ->select('clients.*')
            ->selectRaw("
            EXISTS (
                SELECT 1
                FROM client_esigns
                WHERE client_esigns.client_id = clients.id
                    AND client_esigns.deleted_at IS NULL
            ) as esign_exists
        ")
            ->orderByDesc('clients.is_exported')
            ->orderBy('clients.exported_at', 'ASC')
            ->orderByDesc('clients.no_jp');
    }

    public static function getClientRegistered($batchName = null, $claimType = null, $pic = null, $closing_id = null, $orderByClaimType = false)
    {
        return Client::query()
            ->where('clients.claim_type', '!=', Client::CLAIM_TYPE_KOKUMIN)
            ->join('client_confirmations', function ($j) {
                $j->on('client_confirmations.client_id', '=', 'clients.id')
                    ->whereNull('client_confirmations.deleted_at');
            })
            ->when($closing_id, function ($q) use ($closing_id) {
                $q->where('client_confirmations.client_closing_history_id', $closing_id);
            })
            ->when($batchName && $batchName != 'all', function ($q) use ($batchName) {
                $q->where('clients.source_file_name', $batchName);
            })
            ->when($claimType && $claimType === Client::CLAIM_TYPE_NORMAL, function ($q) {
                $q->where('client_confirmations.claim_type', Client::CLAIM_TYPE_NORMAL);
            })
            ->when($claimType &&
                in_array(
                    $claimType,
                    [Client::CLAIM_TYPE_SPEED, Client::CLAIM_TYPE_ADJUSTMENT]
                ), function ($q) use ($claimType) {
                $q->where('client_confirmations.claim_type', $claimType)
                    ->whereHas('esign');
            })
            ->when($claimType && $claimType === Client::STATUS_PENDING, function ($q) {
                $q->whereIn('client_confirmations.claim_type', [Client::CLAIM_TYPE_ADJUSTMENT, Client::CLAIM_TYPE_SPEED])
                    ->whereDoesntHave('esign');
            })
            ->when($pic, function ($q) use ($pic) {
                $q->where('clients.pic', 'ILIKE', '%' . $pic . '%');
            })
            ->whereHas('confirmation')
            ->select('clients.*')
            ->selectRaw("
            EXISTS (
                SELECT 1
                FROM client_esigns
                WHERE client_esigns.client_id = clients.id
                    AND client_esigns.deleted_at IS NULL
            ) as esign_exists
        ")
            ->orderByDesc('clients.is_exported')
            ->when($orderByClaimType, function ($q) {
                $q->orderByDesc('client_confirmations.claim_type', 'DESC');
            })
            ->orderBy('clients.exported_at', 'ASC')
            ->orderByDesc('clients.no_jp');
    }
    public static function getClientUnclosing($batchName = null, $claimType = null, $pic = null, $closing_id = null, $orderByClaimType = false)
    {
        return Client::query()
            ->where('clients.claim_type', '!=', Client::CLAIM_TYPE_KOKUMIN)
            ->join('client_confirmations', function ($j) {
                $j->on('client_confirmations.client_id', '=', 'clients.id')
                    ->whereNull('client_confirmations.deleted_at');
            })
            ->where('client_confirmations.client_closing_history_id', null)
            ->when($batchName && $batchName != 'all', function ($q) use ($batchName) {
                $q->where('clients.source_file_name', $batchName);
            })
            ->when($claimType && $claimType === Client::CLAIM_TYPE_NORMAL, function ($q) {
                $q->where('client_confirmations.claim_type', Client::CLAIM_TYPE_NORMAL);
            })
            ->when($claimType &&
                in_array(
                    $claimType,
                    [Client::CLAIM_TYPE_SPEED, Client::CLAIM_TYPE_ADJUSTMENT]
                ), function ($q) use ($claimType) {
                $q->where('client_confirmations.claim_type', $claimType)
                    ->whereHas('esign');
            })
            ->when($claimType && $claimType === Client::STATUS_PENDING, function ($q) {
                $q->whereIn('client_confirmations.claim_type', [Client::CLAIM_TYPE_ADJUSTMENT, Client::CLAIM_TYPE_SPEED])
                    ->whereDoesntHave('esign');
            })
            ->when($pic, function ($q) use ($pic) {
                $q->where('clients.pic', 'ILIKE', '%' . $pic . '%');
            })
            ->select('clients.*')
            ->selectRaw("
            EXISTS (
                SELECT 1
                FROM client_esigns
                WHERE client_esigns.client_id = clients.id
                    AND client_esigns.deleted_at IS NULL
            ) as esign_exists
        ")
            ->orderByDesc('clients.is_exported')
            ->orderBy('clients.exported_at', 'ASC')
            ->orderByDesc('clients.no_jp');
    }
    public static function getClientUnregistered($batchName = null, $claimType = null, $pic = null, $closing_id = null, $orderByClaimType = false)
    {
        return Client::query()
            ->where('clients.claim_type', '!=', Client::CLAIM_TYPE_KOKUMIN)
            ->leftJoin('client_confirmations', function ($j) {
                $j->on('client_confirmations.client_id', '=', 'clients.id')
                    ->whereNull('client_confirmations.deleted_at');
            })
            ->when($closing_id, function ($q) use ($closing_id) {
                $q->where('client_confirmations.client_closing_history_id', $closing_id);
            })
            ->when($batchName && $batchName != 'all', function ($q) use ($batchName) {
                $q->where('clients.source_file_name', $batchName);
            })
            ->when($claimType && $claimType === Client::CLAIM_TYPE_NORMAL, function ($q) {
                $q->where('client_confirmations.claim_type', Client::CLAIM_TYPE_NORMAL);
            })
            ->when($claimType &&
                in_array(
                    $claimType,
                    [Client::CLAIM_TYPE_SPEED, Client::CLAIM_TYPE_ADJUSTMENT]
                ), function ($q) use ($claimType) {
                $q->where('client_confirmations.claim_type', $claimType)
                    ->whereHas('esign');
            })
            ->when($claimType && $claimType === Client::STATUS_PENDING, function ($q) {
                $q->whereIn('client_confirmations.claim_type', [Client::CLAIM_TYPE_ADJUSTMENT, Client::CLAIM_TYPE_SPEED])
                    ->whereDoesntHave('esign');
            })
            ->when($pic, function ($q) use ($pic) {
                $q->where('clients.pic', 'ILIKE', '%' . $pic . '%');
            })
            ->whereDoesntHave('confirmation')
            ->select('clients.*')
            ->selectRaw("
            EXISTS (
                SELECT 1
                FROM client_esigns
                WHERE client_esigns.client_id = clients.id
                    AND client_esigns.deleted_at IS NULL
            ) as esign_exists
        ")
            ->orderByDesc('clients.is_exported')
            ->when($orderByClaimType, function ($q) {
                $q->orderByDesc('client_confirmations.claim_type', 'DESC');
            })
            ->orderByDesc('clients.no_jp');
    }
    public static function getClientKokumin($batchName = null, $claimType = null, $pic = null, $closing_id = null, $orderByClaimType = false)
    {
        return Client::query()
            ->where('clients.claim_type', '=', Client::CLAIM_TYPE_KOKUMIN)
            ->leftJoin('client_confirmations', function ($j) {
                $j->on('client_confirmations.client_id', '=', 'clients.id')
                    ->whereNull('client_confirmations.deleted_at');
            })
            ->when($closing_id, function ($q) use ($closing_id) {
                $q->where('client_confirmations.client_closing_history_id', $closing_id);
            })
            ->when($batchName && $batchName != 'all', function ($q) use ($batchName) {
                $q->where('clients.source_file_name', $batchName);
            })
            ->when($claimType && $claimType === Client::CLAIM_TYPE_NORMAL, function ($q) {
                $q->where('client_confirmations.claim_type', Client::CLAIM_TYPE_NORMAL);
            })
            ->when($claimType &&
                in_array(
                    $claimType,
                    [Client::CLAIM_TYPE_SPEED, Client::CLAIM_TYPE_ADJUSTMENT]
                ), function ($q) use ($claimType) {
                $q->where('client_confirmations.claim_type', $claimType)
                    ->whereHas('esign');
            })
            ->when($claimType && $claimType === Client::STATUS_PENDING, function ($q) {
                $q->whereIn('client_confirmations.claim_type', [Client::CLAIM_TYPE_ADJUSTMENT, Client::CLAIM_TYPE_SPEED])
                    ->whereDoesntHave('esign');
            })
            ->when($pic, function ($q) use ($pic) {
                $q->where('clients.pic', 'ILIKE', '%' . $pic . '%');
            })
            ->whereDoesntHave('confirmation')
            ->select('clients.*')
            ->selectRaw("
            EXISTS (
                SELECT 1
                FROM client_esigns
                WHERE client_esigns.client_id = clients.id
                    AND client_esigns.deleted_at IS NULL
            ) as esign_exists
        ")
            ->orderByDesc('clients.is_exported')
            ->when($orderByClaimType, function ($q) {
                $q->orderByDesc('client_confirmations.claim_type', 'DESC');
            })
            ->orderByDesc('clients.no_jp');
    }

    public static function getClientUpodSurvey($batchName = null, $claimType = null, $pic = null)
    {
        return Client::query()
            ->where('clients.claim_type', '!=', Client::CLAIM_TYPE_KOKUMIN)
            ->when($batchName && $batchName != 'all', function ($q) use ($batchName) {
                $q->where('clients.source_file_name', $batchName);
            })
            ->when($claimType, function ($q) use ($claimType) {
                $q->where('clients.claim_type', $claimType);
            })
            ->when($pic, function ($q) use ($pic) {
                $q->where('clients.pic', $pic);
            })
            ->whereHas('upodSurveyResponse')
            ->select('clients.*');
    }
}
