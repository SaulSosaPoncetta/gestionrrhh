@extends('layouts.app')

@section('title', 'Legajo ' . $empleado->legajo)

@section('content')
@php($e = $empleado)
@php($dato = fn ($v) => filled($v) ? e($v) : '<span class="text-muted">—</span>')

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
@endif

{{-- ENCABEZADO --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap gap-3 align-items-center">
        @if($e->foto_url)
            <img src="{{ $e->foto_url }}" alt="" width="80" height="80" class="rounded-circle object-fit-cover">
        @else
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary fw-semibold fs-4"
                  style="width:80px;height:80px">{{ $e->iniciales }}</span>
        @endif

        <div class="flex-grow-1">
            <div class="small text-muted">Legajo N° {{ $e->legajo }}</div>
            <h2 class="h4 mb-1">{{ $e->nombre_completo }}</h2>
            <div class="text-muted">
                {{ $e->puesto?->nombre ?? 'Sin puesto' }}
                @if($e->area) · {{ $e->area->nombre }} @endif
            </div>
            <div class="mt-1">
                @if($e->activo)
                    <span class="badge text-bg-success">Activo</span>
                    <span class="small text-muted ms-1">Antigüedad: {{ $e->antiguedad }}</span>
                @else
                    <span class="badge text-bg-secondary">Baja {{ $e->fecha_egreso->format('d/m/Y') }}</span>
                    <span class="small text-muted ms-1">{{ $e->motivo_egreso_nombre }} · trabajó {{ $e->antiguedad }}</span>
                @endif
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('empleados.edit', $e) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Editar</a>
            @if($e->activo)
                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalBaja">
                    <i class="bi bi-person-dash me-1"></i>Dar de baja
                </button>
            @else
                <form method="POST" action="{{ route('empleados.reincorporar', $e) }}"
                      onsubmit="return confirm('¿Reincorporar al empleado? Se borra la fecha y el motivo de egreso.')">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i>Reincorporar</button>
                </form>
            @endif
            <div class="dropdown">
                <button class="btn btn-outline-secondary" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form method="POST" action="{{ route('empleados.destroy', $e) }}"
                              onsubmit="return confirm('¿Eliminar el legajo de {{ addslashes($e->nombre_completo) }}? Esta acción es para legajos cargados por error; para egresos usá «Dar de baja».')">
                            @csrf @method('DELETE')
                            <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Eliminar legajo</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- PESTAÑAS --}}
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#datos" type="button">Datos personales</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#laboral" type="button">Laboral</button></li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#familia" type="button">
            Familia <span class="badge text-bg-light">{{ $e->familiares->count() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documentos" type="button">
            Documentos <span class="badge text-bg-light">{{ $e->documentos->count() }}</span>
            @if($e->documentos->contains(fn ($d) => in_array($d->estado_vencimiento, ['vencido', 'por_vencer'])))
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
            @endif
        </button>
    </li>
</ul>

<div class="tab-content bg-white border border-top-0 rounded-bottom shadow-sm p-3 p-md-4 mb-4">

    {{-- DATOS PERSONALES --}}
    <div class="tab-pane fade show active" id="datos">
        <div class="row g-4">
            <div class="col-lg-6">
                <h3 class="h6 text-uppercase text-muted mb-3">Identificación</h3>
                <dl class="row mb-0">
                    <dt class="col-5 fw-normal text-muted">CUIL</dt><dd class="col-7">{!! $dato($e->cuil) !!}</dd>
                    <dt class="col-5 fw-normal text-muted">DNI</dt><dd class="col-7">{!! $dato($e->dni) !!}</dd>
                    <dt class="col-5 fw-normal text-muted">Fecha de nacimiento</dt>
                    <dd class="col-7">
                        @if($e->fecha_nacimiento){{ $e->fecha_nacimiento->format('d/m/Y') }} <span class="text-muted">({{ $e->edad }} años)</span>
                        @else<span class="text-muted">—</span>@endif
                    </dd>
                    <dt class="col-5 fw-normal text-muted">Sexo</dt><dd class="col-7">{!! $dato($e->sexo_nombre) !!}</dd>
                    <dt class="col-5 fw-normal text-muted">Estado civil</dt><dd class="col-7">{!! $dato($e->estado_civil_nombre) !!}</dd>
                    <dt class="col-5 fw-normal text-muted">Nacionalidad</dt><dd class="col-7">{!! $dato($e->nacionalidad) !!}</dd>
                </dl>
            </div>
            <div class="col-lg-6">
                <h3 class="h6 text-uppercase text-muted mb-3">Contacto</h3>
                <dl class="row mb-0">
                    <dt class="col-5 fw-normal text-muted">Domicilio</dt>
                    <dd class="col-7">{!! $dato(collect([$e->domicilio, $e->localidad, $e->provincia, $e->codigo_postal ? 'CP ' . $e->codigo_postal : null])->filter()->implode(', ')) !!}</dd>
                    <dt class="col-5 fw-normal text-muted">Teléfono</dt><dd class="col-7">{!! $dato($e->telefono) !!}</dd>
                    <dt class="col-5 fw-normal text-muted">Email</dt>
                    <dd class="col-7">@if($e->email)<a href="mailto:{{ $e->email }}">{{ $e->email }}</a>@else<span class="text-muted">—</span>@endif</dd>
                    <dt class="col-5 fw-normal text-muted">Emergencia</dt>
                    <dd class="col-7">{!! $dato(collect([$e->contacto_emergencia, $e->telefono_emergencia])->filter()->implode(' · ')) !!}</dd>
                </dl>
            </div>
            @if($e->observaciones)
                <div class="col-12">
                    <h3 class="h6 text-uppercase text-muted mb-2">Observaciones</h3>
                    <p class="mb-0" style="white-space: pre-line">{{ $e->observaciones }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- LABORAL --}}
    <div class="tab-pane fade" id="laboral">
        <div class="row g-4">
            <div class="col-lg-6">
                <h3 class="h6 text-uppercase text-muted mb-3">Relación laboral</h3>
                <dl class="row mb-0">
                    <dt class="col-5 fw-normal text-muted">Ingreso</dt><dd class="col-7">{{ $e->fecha_ingreso->format('d/m/Y') }}</dd>
                    @unless($e->activo)
                        <dt class="col-5 fw-normal text-muted">Egreso</dt>
                        <dd class="col-7">{{ $e->fecha_egreso->format('d/m/Y') }} · {{ $e->motivo_egreso_nombre }}</dd>
                    @endunless
                    <dt class="col-5 fw-normal text-muted">Antigüedad</dt><dd class="col-7">{{ $e->antiguedad }}</dd>
                    <dt class="col-5 fw-normal text-muted">Modalidad</dt><dd class="col-7">{{ $e->modalidad_nombre }}</dd>
                    <dt class="col-5 fw-normal text-muted">Jornada</dt>
                    <dd class="col-7">{{ $e->jornada_nombre }}@if($e->horas_semanales) · {{ rtrim(rtrim(number_format($e->horas_semanales, 2, ',', '.'), '0'), ',') }} hs/sem @endif</dd>
                    <dt class="col-5 fw-normal text-muted">Área</dt><dd class="col-7">{!! $dato($e->area?->ruta) !!}</dd>
                    <dt class="col-5 fw-normal text-muted">Puesto</dt><dd class="col-7">{!! $dato($e->puesto?->nombre) !!}</dd>
                    <dt class="col-5 fw-normal text-muted">Supervisor</dt>
                    <dd class="col-7">
                        @if($e->supervisor)<a href="{{ route('empleados.show', $e->supervisor) }}">{{ $e->supervisor->nombre_completo }}</a>
                        @else<span class="text-muted">—</span>@endif
                    </dd>
                    @if($e->aCargo->isNotEmpty())
                        <dt class="col-5 fw-normal text-muted">Personal a cargo</dt>
                        <dd class="col-7">
                            @foreach($e->aCargo as $sub)
                                <a href="{{ route('empleados.show', $sub) }}" class="d-block">{{ $sub->nombre_completo }}</a>
                            @endforeach
                        </dd>
                    @endif
                </dl>
            </div>
            <div class="col-lg-6">
                <h3 class="h6 text-uppercase text-muted mb-3">Remuneración</h3>
                <dl class="row mb-4">
                    <dt class="col-5 fw-normal text-muted">Convenio</dt><dd class="col-7">{{ $e->convenio?->nombre_completo ?? 'Fuera de convenio' }}</dd>
                    @if($e->categoria)
                        <dt class="col-5 fw-normal text-muted">Categoría</dt><dd class="col-7">{{ $e->categoria->nombre }}</dd>
                    @endif
                    <dt class="col-5 fw-normal text-muted">Básico vigente</dt>
                    <dd class="col-7 fw-semibold">$ {{ number_format($e->basico_vigente, 2, ',', '.') }}</dd>
                </dl>

                <h3 class="h6 text-uppercase text-muted mb-3">Cobro y obra social</h3>
                <dl class="row mb-0">
                    <dt class="col-5 fw-normal text-muted">Banco</dt><dd class="col-7">{!! $dato($e->banco) !!}</dd>
                    <dt class="col-5 fw-normal text-muted">CBU</dt><dd class="col-7 font-monospace">{!! $dato($e->cbu) !!}</dd>
                    <dt class="col-5 fw-normal text-muted">Obra social</dt>
                    <dd class="col-7">{!! $dato(collect([$e->obra_social, $e->codigo_obra_social ? 'RNOS ' . $e->codigo_obra_social : null])->filter()->implode(' · ')) !!}</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- FAMILIA --}}
    <div class="tab-pane fade" id="familia">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 text-uppercase text-muted mb-0">Grupo familiar</h3>
            <button class="btn btn-sm btn-primary" onclick="abrirFamiliar()"><i class="bi bi-plus-lg me-1"></i>Agregar familiar</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Parentesco</th><th>Apellido y nombre</th><th>DNI</th><th>Nacimiento</th>
                        <th class="text-center">A cargo</th><th class="text-center">Discapacidad</th><th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($e->familiares as $f)
                    <tr>
                        <td>{{ $f->parentesco_nombre }}</td>
                        <td class="fw-semibold">{{ $f->apellido_nombre }}</td>
                        <td>{{ $f->dni ?? '—' }}</td>
                        <td>{{ $f->fecha_nacimiento?->format('d/m/Y') ?? '—' }}@if($f->edad !== null) <span class="text-muted small">({{ $f->edad }})</span>@endif</td>
                        <td class="text-center">{!! $f->a_cargo ? '<i class="bi bi-check-lg text-success"></i>' : '—' !!}</td>
                        <td class="text-center">{!! $f->discapacidad ? '<i class="bi bi-check-lg text-success"></i>' : '—' !!}</td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-sm btn-outline-primary" title="Editar"
                                    data-familiar="{{ json_encode([
                                        'url'              => route('empleados.familiares.update', [$e, $f]),
                                        'parentesco'       => $f->parentesco,
                                        'apellido_nombre'  => $f->apellido_nombre,
                                        'dni'              => $f->dni,
                                        'fecha_nacimiento' => $f->fecha_nacimiento?->format('Y-m-d'),
                                        'a_cargo'          => $f->a_cargo,
                                        'discapacidad'     => $f->discapacidad,
                                    ]) }}"
                                    onclick="abrirFamiliar(JSON.parse(this.dataset.familiar))"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('empleados.familiares.destroy', [$e, $f]) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar a {{ addslashes($f->apellido_nombre) }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay familiares cargados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- DOCUMENTOS --}}
    <div class="tab-pane fade" id="documentos">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 text-uppercase text-muted mb-0">Documentación</h3>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalDocumento">
                <i class="bi bi-upload me-1"></i>Subir documento
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Tipo</th><th>Archivo</th><th class="d-none d-md-table-cell">Subido</th><th>Vencimiento</th><th class="text-end"></th></tr>
                </thead>
                <tbody>
                @forelse($e->documentos as $doc)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $doc->tipo_nombre }}</div>
                            @if($doc->descripcion)<div class="small text-muted">{{ $doc->descripcion }}</div>@endif
                        </td>
                        <td>
                            <a href="{{ route('empleados.documentos.ver', [$e, $doc]) }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-file-earmark me-1"></i>{{ \Illuminate\Support\Str::limit($doc->nombre_original, 40) }}
                            </a>
                            <div class="small text-muted">{{ $doc->tamanio_legible }}</div>
                        </td>
                        <td class="d-none d-md-table-cell small text-muted">
                            {{ $doc->created_at->format('d/m/Y') }}<br>{{ $doc->subidoPor?->name }}
                        </td>
                        <td>
                            @if($doc->vencimiento)
                                {{ $doc->vencimiento->format('d/m/Y') }}
                                @if($doc->estado_vencimiento === 'vencido')
                                    <span class="badge text-bg-danger">Vencido</span>
                                @elseif($doc->estado_vencimiento === 'por_vencer')
                                    <span class="badge text-bg-warning">Por vencer</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('empleados.documentos.descargar', [$e, $doc]) }}" class="btn btn-sm btn-outline-secondary" title="Descargar">
                                <i class="bi bi-download"></i>
                            </a>
                            <form method="POST" action="{{ route('empleados.documentos.destroy', [$e, $doc]) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar este documento? El archivo se borra definitivamente.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No hay documentos cargados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL BAJA --}}
@if($e->activo)
<div class="modal fade" id="modalBaja" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('empleados.baja', $e) }}" class="modal-content">
            @csrf @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title">Registrar baja de {{ $e->nombre_completo }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-5">
                    <label class="form-label">Fecha de egreso *</label>
                    <input type="date" name="fecha_egreso" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
                </div>
                <div class="col-md-7">
                    <label class="form-label">Motivo *</label>
                    <select name="motivo_egreso" class="form-select" required>
                        <option value="">— Seleccionar —</option>
                        @foreach($motivosEgreso as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 small text-muted">
                    El legajo queda guardado. Si el empleado tiene usuario de autogestión, se desactiva.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-warning">Registrar baja</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL FAMILIAR (alta y edición) --}}
<div class="modal fade" id="modalFamiliar" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('empleados.familiares.store', $e) }}" id="formFamiliar" class="modal-content">
            @csrf
            <input type="hidden" name="_method" id="familiarMetodo" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="familiarTitulo">Agregar familiar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-5">
                    <label class="form-label">Parentesco *</label>
                    <select name="parentesco" class="form-select" required>
                        <option value="">— Seleccionar —</option>
                        @foreach($parentescos as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label">Apellido y nombre *</label>
                    <input type="text" name="apellido_nombre" class="form-control" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">DNI</label>
                    <input type="text" name="dni" class="form-control" maxlength="8" inputmode="numeric">
                </div>
                <div class="col-md-7">
                    <label class="form-label">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control">
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="a_cargo" value="1" id="a_cargo" class="form-check-input" checked>
                        <label for="a_cargo" class="form-check-label">A cargo</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="discapacidad" value="1" id="discapacidad" class="form-check-input">
                        <label for="discapacidad" class="form-check-label">Con discapacidad</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL DOCUMENTO --}}
<div class="modal fade" id="modalDocumento" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('empleados.documentos.store', $e) }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Subir documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-select" required>
                        <option value="">— Seleccionar —</option>
                        @foreach($tiposDocumento as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" class="form-control" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Vencimiento</label>
                    <input type="date" name="vencimiento" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Archivo * <span class="text-muted small">(PDF, imagen, Word o Excel · máx. 10 MB)</span></label>
                    <input type="file" name="archivo" class="form-control" required
                           accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary"><i class="bi bi-upload me-1"></i>Subir</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Abrir la pestaña indicada en la URL (#familia, #documentos...)
(function () {
    const hash = window.location.hash;
    if (hash) {
        const btn = document.querySelector(`[data-bs-target="${hash}"]`);
        if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
    }
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', e => history.replaceState(null, '', e.target.dataset.bsTarget));
    });
})();

// Modal de familiar: vacío para alta, cargado para edición
const urlAltaFamiliar = @json(route('empleados.familiares.store', $e));
function abrirFamiliar(datos) {
    const form = document.getElementById('formFamiliar');
    form.reset();
    form.action = datos ? datos.url : urlAltaFamiliar;
    document.getElementById('familiarMetodo').value = datos ? 'PUT' : 'POST';
    document.getElementById('familiarTitulo').textContent = datos ? 'Editar familiar' : 'Agregar familiar';
    if (datos) {
        form.parentesco.value = datos.parentesco ?? '';
        form.apellido_nombre.value = datos.apellido_nombre ?? '';
        form.dni.value = datos.dni ?? '';
        form.fecha_nacimiento.value = datos.fecha_nacimiento ?? '';
        form.a_cargo.checked = !!datos.a_cargo;
        form.discapacidad.checked = !!datos.discapacidad;
    }
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalFamiliar')).show();
}
</script>
@endpush
