<?php

namespace App\Http\Controllers;

use App\Models\NightSession;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Services\NightSessionService;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    protected NightSessionService $sessionService;

    public function __construct(NightSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function index(Request $request)
    {
        $activeSession = $this->sessionService->resolveSession($request->get('session_id'));

        // Normalizar día de la sesión activa
        $sessionDayRaw = $activeSession ? $activeSession->day_name : null;
        $sessionDayNorm = $sessionDayRaw ? $this->normalizeDay($sessionDayRaw) : null;

        // Determinar si la noche activa es un día extraordinario (no es viernes, sabado, domingo)
        $isExtraDay = $sessionDayNorm && !in_array($sessionDayNorm, ['viernes', 'sabado', 'domingo']);

        // Si el usuario especificó ?day=..., usarlo. Si no, usar el día de la noche activa si existe; sino 'viernes'
        if ($request->has('day')) {
            $currentDay = $this->normalizeDay($request->get('day'));
        } elseif ($sessionDayNorm) {
            $currentDay = $sessionDayNorm;
        } else {
            $currentDay = 'viernes';
        }

        // Obtener personal según el día
        if ($currentDay === 'todos') {
            $staffMembers = Staff::where('is_active', true)->get();
        } elseif ($isExtraDay && $currentDay === $sessionDayNorm) {
            // Noche extraordinaria activa (ej: Jueves):
            // Si la noche no tiene asistencias aún, la lista inicia vacía (0 seleccionados) como solicitó Don Ludo
            if ($activeSession) {
                $attendances = StaffAttendance::with('staff')
                    ->where('night_session_id', $activeSession->id)
                    ->get();
                $staffMembers = $attendances->map(fn($att) => $att->staff)->filter()->values();
            } else {
                $staffMembers = collect();
            }
        } elseif ($currentDay === 'viernes') {
            if ($activeSession && $this->normalizeDay($activeSession->day_name) === 'viernes' && $activeSession->staffAttendances()->exists()) {
                $attendances = StaffAttendance::with('staff')->where('night_session_id', $activeSession->id)->get();
                $staffMembers = $attendances->map(fn($att) => $att->staff)->filter()->values();
            } else {
                $staffMembers = Staff::where('is_active', true)->where('works_friday', true)->get();
            }
        } elseif ($currentDay === 'sabado') {
            if ($activeSession && $this->normalizeDay($activeSession->day_name) === 'sabado' && $activeSession->staffAttendances()->exists()) {
                $attendances = StaffAttendance::with('staff')->where('night_session_id', $activeSession->id)->get();
                $staffMembers = $attendances->map(fn($att) => $att->staff)->filter()->values();
            } else {
                $staffMembers = Staff::where('is_active', true)->where('works_saturday', true)->get();
            }
        } elseif ($currentDay === 'domingo') {
            if ($activeSession && $this->normalizeDay($activeSession->day_name) === 'domingo' && $activeSession->staffAttendances()->exists()) {
                $attendances = StaffAttendance::with('staff')->where('night_session_id', $activeSession->id)->get();
                $staffMembers = $attendances->map(fn($att) => $att->staff)->filter()->values();
            } else {
                $staffMembers = Staff::where('is_active', true)->where('works_sunday', true)->get();
            }
        } else {
            // Cualquier otro día sin noche activa
            $staffMembers = collect();
        }

        // Orden estricto oficial de Don Ludo:
        // 1. Seguridad, 2. Barra, 3. Mozos, 4. Limpieza (y alfabético por nombre)
        $staffMembers = $staffMembers->sort(function ($a, $b) {
            $rankA = $a->getRoleCategoryRank();
            $rankB = $b->getRoleCategoryRank();
            if ($rankA === $rankB) {
                return strcasecmp($a->name, $b->name);
            }
            return $rankA <=> $rankB;
        })->values();

        // Conteos para pestañas
        $countViernes = ($activeSession && $this->normalizeDay($activeSession->day_name) === 'viernes' && $activeSession->staffAttendances()->exists())
            ? $activeSession->staffAttendances()->count()
            : Staff::where('is_active', true)->where('works_friday', true)->count();

        $countSabado = ($activeSession && $this->normalizeDay($activeSession->day_name) === 'sabado' && $activeSession->staffAttendances()->exists())
            ? $activeSession->staffAttendances()->count()
            : Staff::where('is_active', true)->where('works_saturday', true)->count();

        $countDomingo = ($activeSession && $this->normalizeDay($activeSession->day_name) === 'domingo' && $activeSession->staffAttendances()->exists())
            ? $activeSession->staffAttendances()->count()
            : Staff::where('is_active', true)->where('works_sunday', true)->count();

        $countTodos = Staff::where('is_active', true)->count();

        // Datos para noche extraordinaria
        $extraDayName = $isExtraDay ? $sessionDayRaw : null;
        $extraDayKey = $isExtraDay ? $sessionDayNorm : null;
        $countExtra = ($isExtraDay && $activeSession) ? $activeSession->staffAttendances()->count() : 0;

        // Lista completa de todo el personal activo ordenado por rol para el modal de turno
        $allActiveStaff = Staff::where('is_active', true)
            ->get()
            ->sort(function ($a, $b) {
                $rankA = $a->getRoleCategoryRank();
                $rankB = $b->getRoleCategoryRank();
                if ($rankA === $rankB) {
                    return strcasecmp($a->name, $b->name);
                }
                return $rankA <=> $rankB;
            })->values();

        $currentSessionStaffIds = $activeSession 
            ? StaffAttendance::where('night_session_id', $activeSession->id)->pluck('staff_id')->toArray() 
            : [];

        return view('staff.index', compact(
            'staffMembers',
            'currentDay',
            'countViernes',
            'countSabado',
            'countDomingo',
            'countTodos',
            'activeSession',
            'isExtraDay',
            'extraDayName',
            'extraDayKey',
            'countExtra',
            'allActiveStaff',
            'currentSessionStaffIds'
        ));
    }

    /**
     * Sincroniza en lote el personal de turno para la noche activa vía AJAX
     */
    public function syncAttendance(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:night_sessions,id',
            'staff_ids' => 'nullable|array',
            'staff_ids.*' => 'integer|exists:staff,id',
        ]);

        $session = NightSession::findOrFail($validated['session_id']);
        if (!$session->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden modificar turnos en una noche cerrada.'
            ], 422);
        }

        $selectedIds = collect($validated['staff_ids'] ?? [])->map(fn($id) => (int)$id)->all();

        // Eliminar del turno a los desmarcados (solo si no están pagados aún)
        StaffAttendance::where('night_session_id', $session->id)
            ->whereNotIn('staff_id', $selectedIds)
            ->where('is_paid', false)
            ->delete();

        // Obtener IDs que ya están registrados
        $existingStaffIds = StaffAttendance::where('night_session_id', $session->id)
            ->pluck('staff_id')
            ->toArray();

        // Agregar los nuevos seleccionados
        $toAdd = array_diff($selectedIds, $existingStaffIds);
        $dayName = $session->day_name;

        foreach ($toAdd as $staffId) {
            $member = Staff::find($staffId);
            if ($member) {
                StaffAttendance::create([
                    'night_session_id' => $session->id,
                    'staff_id' => $member->id,
                    'pay_amount' => $member->getPayForDay($dayName),
                    'is_paid' => false,
                ]);
            }
        }

        // Recalcular arqueo y cierre
        $this->sessionService->recalculateClosing($session);

        $newCount = StaffAttendance::where('night_session_id', $session->id)->count();

        return response()->json([
            'success' => true,
            'message' => "Turno sincronizado correctamente ({$newCount} asignados)",
            'count' => $newCount,
        ]);
    }

    private function normalizeDay(string $day): string
    {
        $day = mb_strtolower(trim($day));
        return str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n'],
            $day
        );
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:100',
            'default_pay' => 'required|numeric|min:0',
            'assigned_bar' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'friday_pay' => 'nullable|numeric|min:0',
            'saturday_pay' => 'nullable|numeric|min:0',
            'sunday_pay' => 'nullable|numeric|min:0',
        ]);

        $validated['works_friday'] = $request->boolean('works_friday', true);
        $validated['works_saturday'] = $request->boolean('works_saturday', true);
        $validated['works_sunday'] = $request->boolean('works_sunday', true);

        Staff::create($validated);

        return redirect()->route('staff.index');
    }

    public function edit(Staff $staff)
    {
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:100',
            'default_pay' => 'required|numeric|min:0',
            'assigned_bar' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
            'friday_pay' => 'nullable|numeric|min:0',
            'saturday_pay' => 'nullable|numeric|min:0',
            'sunday_pay' => 'nullable|numeric|min:0',
        ]);

        $validated['works_friday'] = $request->boolean('works_friday');
        $validated['works_saturday'] = $request->boolean('works_saturday');
        $validated['works_sunday'] = $request->boolean('works_sunday');

        $staff->update($validated);

        return redirect()->route('staff.index');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();
        return redirect()->route('staff.index');
    }
}
