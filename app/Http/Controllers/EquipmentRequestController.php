<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Equipment;
use App\Models\ActivityLog;
use App\Models\EquipmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EquipmentRequestController extends Controller
{
    /**
     * Список заявок для администратора (списание и перемещение).
     */
    public function index(Request $request): View
    {
        $query = EquipmentRequest::query()
            ->with(['equipment', 'user', 'fromDepartment', 'toDepartment', 'requestType', 'requestStatus'])
            ->join('equipment_request_statuses as ers', 'equipment_requests.request_status_id', '=', 'ers.id')
            ->select('equipment_requests.*')
            ->orderByRaw("CASE WHEN ers.code = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('equipment_requests.created_at');

        $filterStatus = $request->input('status', '');
        if ($filterStatus === 'pending' || $filterStatus === 'approved' || $filterStatus === 'rejected') {
            $query->whereRelation('requestStatus', 'code', $filterStatus);
        }

        $filterType = $request->input('type', '');
        if ($filterType === 'writeoff' || $filterType === 'move') {
            $query->whereRelation('requestType', 'code', $filterType);
        }

        $requests = $query->paginate(20)->withQueryString();

        $pendingCount = EquipmentRequest::whereRelation('requestStatus', 'code', EquipmentRequest::STATUS_PENDING)->count();

        return view('equipment-requests.index', [
            'requests' => $requests,
            'pendingCount' => $pendingCount,
            'filterStatus' => $filterStatus,
            'filterType' => $filterType,
        ]);
    }
    public function storeWriteoff(Request $request, Equipment $equipment): RedirectResponse
    {
        if (! $request->user()?->isSeniorNurse()) {
            abort(403, 'Только старшая медсестра может отправлять заявки на списание.');
        }

        if ($equipment->isWrittenOff()) {
            return back()->with('error', 'Оборудование уже списано.');
        }

        if ($equipment->isWriteoffRequested()) {
            return back()->with('error', 'Заявка на списание уже отправлена и ожидает решения администратора.');
        }

        $data = $request->validate([
            'comment' => 'required|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'comment.required' => 'Опишите, почему нужно списать оборудование.',
            'photo.image' => 'Файл с фото должен быть изображением.',
            'photo.mimes' => 'Допустимые форматы фото: JPEG, PNG, GIF, WebP.',
            'photo.max' => 'Максимальный размер фото — 5 МБ.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photoPath = $request->file('photo')->store('equipment_writeoff_photos', 'public');
        }

        $equipmentRequest = EquipmentRequest::create([
            'equipment_id' => $equipment->id,
            'user_id' => $request->user()->id,
            'type' => EquipmentRequest::TYPE_WRITEOFF,
            'status' => EquipmentRequest::STATUS_PENDING,
            'from_department_id' => $equipment->department_id,
            'comment' => $data['comment'],
            'photo' => $photoPath,
        ]);

        ActivityLog::record(
            EquipmentRequest::class,
            $equipmentRequest->id,
            'created',
            'Заявка на списание: '.$equipment->name,
            'Автор: '.$request->user()->name.'. Комментарий: '.$data['comment'],
        );

        $equipment->writeoff_status = 'requested';
        $equipment->save();

        return back()->with('success', 'Заявка на списание отправлена администратору.');
    }

    public function storeMove(Request $request, Equipment $equipment): RedirectResponse
    {
        if (! $request->user()?->isSeniorNurse()) {
            abort(403, 'Только старшая медсестра может отправлять заявки на перемещение.');
        }

        $data = $request->validate([
            'to_department_id' => ['required', Rule::exists('departments', 'id')->whereNull('deleted_at')],
            'comment' => 'nullable|string|max:1000',
        ]);

        $equipmentRequest = EquipmentRequest::create([
            'equipment_id' => $equipment->id,
            'user_id' => $request->user()->id,
            'type' => EquipmentRequest::TYPE_MOVE,
            'status' => EquipmentRequest::STATUS_PENDING,
            'from_department_id' => $equipment->department_id,
            'to_department_id' => $data['to_department_id'],
            'comment' => $data['comment'] ?? null,
        ]);

        $toDept = Department::find($data['to_department_id']);
        ActivityLog::record(
            EquipmentRequest::class,
            $equipmentRequest->id,
            'created',
            'Заявка на перемещение: '.$equipment->name,
            'Автор: '.$request->user()->name.'. В отдел: '.($toDept?->name ?? '—').($data['comment'] ? '. Комментарий: '.$data['comment'] : ''),
        );

        return back()->with('success', 'Заявка на перемещение отправлена администратору.');
    }

    public function approveWriteoff(Request $request, Equipment $equipment): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Только администратор может подтверждать списание.');
        }

        $equipmentRequest = EquipmentRequest::where('equipment_id', $equipment->id)
            ->whereRelation('requestType', 'code', EquipmentRequest::TYPE_WRITEOFF)
            ->whereRelation('requestStatus', 'code', EquipmentRequest::STATUS_PENDING)
            ->latest()
            ->first();

        if (! $equipmentRequest) {
            return back()->with('error', 'Нет активной заявки на списание для этого оборудования.');
        }

        $equipmentRequest->status = EquipmentRequest::STATUS_APPROVED;
        $equipmentRequest->save();

        $oldStatus = $equipment->writeoff_status;
        $equipment->writeoff_status = 'approved';
        $equipment->save();

        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            'writeoff_approved',
            '№'.$equipment->number.' — '.$equipment->name,
            'Списание подтверждено администратором.',
            null,
            'writeoff_status',
            $oldStatus,
            'approved',
            $request->user()->id,
        );

        return back()->with('success', 'Списание оборудования подтверждено. Оборудование помечено как списанное, но не удалено из системы.');
    }

    /**
     * Подтвердить заявку на перемещение (админ): перенос оборудования в новое отделение.
     */
    public function approveMove(Request $request, EquipmentRequest $equipmentRequest): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Только администратор может подтверждать перемещение.');
        }

        if ($equipmentRequest->type !== EquipmentRequest::TYPE_MOVE) {
            abort(400, 'Некорректный тип заявки.');
        }

        if ($equipmentRequest->status !== EquipmentRequest::STATUS_PENDING) {
            return back()->with('error', 'Эта заявка уже обработана.');
        }

        if (! $equipmentRequest->to_department_id) {
            return back()->with('error', 'В заявке не указано целевое отделение.');
        }

        $equipment = $equipmentRequest->equipment;
        $oldDepartmentId = $equipment->department_id;
        $equipment->department_id = $equipmentRequest->to_department_id;
        $equipment->save();

        $equipmentRequest->status = EquipmentRequest::STATUS_APPROVED;
        $equipmentRequest->save();

        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            'move_approved',
            '№'.$equipment->number.' — '.$equipment->name,
            'Перемещение подтверждено администратором.',
            null,
            'department_id',
            (string) $oldDepartmentId,
            (string) $equipmentRequest->to_department_id,
            $request->user()->id,
        );

        return back()->with('success', 'Перемещение выполнено. Оборудование перенесено в выбранное отделение.');
    }

    /**
     * Отклонить заявку (админ).
     */
    public function reject(Request $request, EquipmentRequest $equipmentRequest): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Только администратор может отклонять заявки.');
        }

        if ($equipmentRequest->status !== EquipmentRequest::STATUS_PENDING) {
            return back()->with('error', 'Эта заявка уже обработана.');
        }

        $equipmentRequest->status = EquipmentRequest::STATUS_REJECTED;
        $equipmentRequest->save();

        if ($equipmentRequest->type === EquipmentRequest::TYPE_WRITEOFF) {
            $equipmentRequest->equipment->writeoff_status = 'none';
            $equipmentRequest->equipment->save();
        }

        ActivityLog::record(
            EquipmentRequest::class,
            $equipmentRequest->id,
            'rejected',
            'Заявка #'.$equipmentRequest->id.' ('.($equipmentRequest->type ?? '').')',
            'Заявка отклонена администратором.',
        );

        return back()->with('success', 'Заявка отклонена.');
    }
}

