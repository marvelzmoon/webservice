@extends('layouts.layout')

@section('main')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.bootstrap5.css">

    <p class="fs-4">Referensi Pendaftaran Antrol</p>
    <div class="row g-3 align-items-center">
        <div class="col-auto">
            <a href="{{ route('monev') }}">
                <button type="button" class="btn btn-warning btn-sm">Kembali</button>
            </a>
        </div>
        <div class="col-auto">
            <label for="formTgl" class="col-form-label">Tanggal Pantauan</label>
        </div>
        <div class="col-auto">
            <input type="date" id="formTgl" class="form-control" aria-describedby="tglform" value="{{  date('Y-m-d'); }}">
        </div>
        <div class="col-auto">
            <button type="button" id="btnCari" class="btn btn-success">Cari</button>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <table id="myTable" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>kode booking</th>
                        <th>Poli</th>
                        <th>Dokter</th>
                        <th>Jam Praktek</th>  
                        <th>Sumber data</th>
                        <th>Status</th>
                        <th>Batal</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div id="loadingOverlay" style="
        position: fixed;
        top:0; left:0;
        width:100%; height:100%;
        background: rgba(255,255,255,0.7);
        display: none;
        z-index: 9999;
        backdrop-filter: blur(2px);
    ">
        <div class="d-flex justify-content-center align-items-center" style="height:100%;">
            <div class="spinner-border text-primary" style="width:3rem; height:3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBatal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Batalkan Antrian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p id="textKodeBooking" class="fw-bold"></p>

                    <div class="mb-3">
                        <label class="form-label">Keterangan Pembatalan</label>
                        <textarea id="inputKet" class="form-control" rows="3" placeholder="Masukkan alasan pembatalan"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-danger" id="btnSubmitBatal">Batalkan</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/responsive.bootstrap5.js"></script>
    <script>
        $(document).ready(function(){
            let table = $('#myTable').DataTable({
                columns: [
                    { data: "kodebooking" },
                    { data: "kodepoli" },
                    { data: "kodedokter" },
                    { data: "jampraktek" },
                    { data: "sumberdata" },
                    { data: "status" },

                    // Kolom Action (index terakhir)
                    { 
                        data: null,
                        render: function(data, type, row) {
                            if (row.status && row.status.toLowerCase() === "batal") {
                                return ""; // jangan tampilkan apa pun
                            }

                            return `
                                <button class="btn btn-sm btn-danger btnUpdate" 
                                    data-id="${row.kodebooking}">
                                    Batal
                                </button>
                            `;
                        }
                    }
                ]
            });

            function loadData() {
                var tgl = $('#formTgl').val();

                $("#loadingOverlay").show();

                $.ajax({
                    method: "POST",
                    url: "{{ route('monev.antrolterdaftar.api') }}",
                    data: {
                        "tgl" : tgl
                    },
                    success: function(response) {
                        if (response['code'] != 200) {
                            alert(response['message']);
                        } else {
                            var currentSearch = table.search();

                            table.clear();
                            table.rows.add(response.data);
                            table.draw();

                            table.search(currentSearch).draw();
                        }
                    },
                    error: function(err) {
                        console.log("Error:", err);
                    },
                    complete: function(){
                        $("#loadingOverlay").hide();
                    }
                });
            }

            $("#btnCari").click(function(){
                loadData();
            });

            $('#myTable').on('click', '.btnUpdate', function () {
                let kode = $(this).data('id'); // ambil kodebooking

                // Simpan kodebooking ke variabel global (atau data attribute)
                window.kodeToCancel = kode;

                // Set teks pada modal
                $('#textKodeBooking').text("Kode Booking : " + kode);

                // Reset input ket
                $('#inputKet').val("");

                // Tampilkan modal
                var modal = new bootstrap.Modal(document.getElementById('modalBatal'));
                modal.show();
            });

            $('#btnSubmitBatal').click(function() {
                let ket = $('#inputKet').val().trim();
                if (ket === "") {
                    alert("Keterangan tidak boleh kosong!");
                    return;
                }

                $("#loadingOverlay").show();

                $.ajax({
                    method: "POST",
                    url: "{{ route('monev.antrolterdaftar.batal') }}",
                    data: {
                        "kode" : window.kodeToCancel,
                        "ket"  : ket
                    },
                    success: function(response) {
                        console.log(response);
                        
                        if (response['code'] == 200) {
                            alert('Kodebooking ' + window.kodeToCancel + ' berhasil dibatalkan');

                            loadData();

                            var modalEl = document.getElementById('modalBatal');
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            modal.hide();
                        } else {
                            alert(response['message']);

                            loadData();

                            var modalEl = document.getElementById('modalBatal');
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            modal.hide();
                        }
                    },
                    error: function(err) {
                        console.log("Error:", err);
                    },
                    complete: function(){
                        $("#loadingOverlay").hide();
                    }
                });
            });
        });
    </script>
@endsection