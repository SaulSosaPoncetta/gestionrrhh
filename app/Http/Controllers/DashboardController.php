<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\EmpleadoDocumento;
use App\Models\Empresa;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->esSuperadmin()) {
            return view('dashboard', [
                'user'  => $user,
                'stats' => [
                    'empresas' => Empresa::count(),
                    'activas'  => Empresa::where('activa', true)->count(),
                    'usuarios' => User::count(),
                ],
            ]);
        }

        $inicioMes = now()->startOfMonth();
        $finMes    = now()->endOfMonth();

        $stats = [
            'activos'  => Empleado::activos()->count(),
            'ingresos' => Empleado::whereBetween('fecha_ingreso', [$inicioMes, $finMes])->count(),
            'egresos'  => Empleado::whereBetween('fecha_egreso', [$inicioMes, $finMes])->count(),
            'usuarios' => User::where('empresa_id', $user->empresa_id)->count(),
        ];

        $cumpleanios = Empleado::activos()
            ->whereNotNull('fecha_nacimiento')
            ->whereMonth('fecha_nacimiento', now()->month)
            ->get()
            ->sortBy(fn ($e) => $e->fecha_nacimiento->day);

        $documentosAlerta = EmpleadoDocumento::with('empleado')
            ->whereNotNull('vencimiento')
            ->where('vencimiento', '<=', now()->addDays(30))
            ->whereHas('empleado', fn ($q) => $q->activos())
            ->orderBy('vencimiento')
            ->limit(10)
            ->get();

        $aniversarios = Empleado::activos()
            ->whereMonth('fecha_ingreso', now()->month)
            ->whereYear('fecha_ingreso', '<', now()->year)
            ->get()
            ->sortBy(fn ($e) => $e->fecha_ingreso->day);

        return view('dashboard', compact('user', 'stats', 'cumpleanios', 'documentosAlerta', 'aniversarios'));
    }
}
