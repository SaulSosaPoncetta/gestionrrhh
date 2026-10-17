<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ConvenioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EmpleadoDocumentoController;
use App\Http\Controllers\EmpleadoFamiliarController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware(['auth', 'empresa.activa'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Solo superadmin (el middleware deja pasar siempre al superadmin)
    Route::middleware('rol:superadmin')->group(function () {
        Route::resource('empresas', EmpresaController::class)->except('show');
        Route::patch('empresas/{empresa}/toggle', [EmpresaController::class, 'toggle'])->name('empresas.toggle');
    });

    // Superadmin y administradores de RRHH
    Route::middleware('rol:admin_rrhh')->group(function () {
        Route::resource('usuarios', UsuarioController::class)
            ->except('show')
            ->parameters(['usuarios' => 'usuario']);
    });

    // Módulos por empresa (administrador de RRHH)
    Route::middleware(['rol:admin_rrhh', 'requiere.empresa'])->group(function () {
        // Estructura
        Route::resource('areas', AreaController::class)->except('show');
        Route::resource('puestos', PuestoController::class)->except('show');
        Route::resource('convenios', ConvenioController::class)->except('show');
        Route::resource('convenios.categorias', CategoriaController::class)
            ->except('show')
            ->shallow();
        Route::post('convenios/{convenio}/aumento', [CategoriaController::class, 'aumento'])
            ->name('convenios.aumento');

        // Legajos
        Route::resource('empleados', EmpleadoController::class);
        Route::patch('empleados/{empleado}/baja', [EmpleadoController::class, 'baja'])->name('empleados.baja');
        Route::patch('empleados/{empleado}/reincorporar', [EmpleadoController::class, 'reincorporar'])->name('empleados.reincorporar');

        Route::post('empleados/{empleado}/familiares', [EmpleadoFamiliarController::class, 'store'])
            ->name('empleados.familiares.store');
        Route::put('empleados/{empleado}/familiares/{familiar}', [EmpleadoFamiliarController::class, 'update'])
            ->name('empleados.familiares.update');
        Route::delete('empleados/{empleado}/familiares/{familiar}', [EmpleadoFamiliarController::class, 'destroy'])
            ->name('empleados.familiares.destroy');

        Route::post('empleados/{empleado}/documentos', [EmpleadoDocumentoController::class, 'store'])
            ->name('empleados.documentos.store');
        Route::get('empleados/{empleado}/documentos/{documento}/ver', [EmpleadoDocumentoController::class, 'ver'])
            ->name('empleados.documentos.ver');
        Route::get('empleados/{empleado}/documentos/{documento}/descargar', [EmpleadoDocumentoController::class, 'descargar'])
            ->name('empleados.documentos.descargar');
        Route::delete('empleados/{empleado}/documentos/{documento}', [EmpleadoDocumentoController::class, 'destroy'])
            ->name('empleados.documentos.destroy');
    });

    // Paso 5 en adelante: asistencia, licencias, liquidación...
});
