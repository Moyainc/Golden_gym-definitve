@extends('layouts.app')
@section('title', 'Subir Comprobante')

@section('content')
<div class="wrapper">
  <div class="main">
    <div id="navbar"></div>
    <main class="content">
      <div class="container-fluid p-4">

        <h1 class="h3 text-white mb-4">
          <i data-feather="upload" class="me-2 tittle-color"></i>Subir Comprobante
        </h1>

        <div class="card border-0 shadow-sm">
          <div class="card-body text-light">

            <p>Escanea este QR desde tu dispositivo para subir el comprobante de la venta.</p>

            <div id="qrComprobante" class="mb-3"></div>

            <form id="subirComprobanteForm" action="{{ route('comprobantes.store') }}" method="POST" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="id_venta" id="id_venta" value="{{ $idVenta }}">
              <input type="hidden" name="id_cliente" value="{{ $idCliente }}">
              <input type="hidden" name="id_usuario" value="{{ $idUsuario }}">

              <div class="mb-3">
                <label for="foto_comprobante" class="form-label">Selecciona el comprobante</label>
                <input type="file" name="foto_comprobante" id="foto_comprobante" class="form-control" required>
              </div>

              <button type="submit" class="btn btn-success fw-bold">Subir Comprobante</button>
            </form>

          </div>
        </div>

      </div>
    </main>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const qrContainer = document.getElementById('qrComprobante');
    const idVenta = "{{ $idVenta }}";

    const url = "{{ url('/comprobantes/scan-form') }}/" + idVenta;

    new QRCode(qrContainer, {
      text: url,
      width: 200,
      height: 200
    });
  });
</script>
@endsection
