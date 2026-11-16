<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="max-w-3xl space-y-4">
   <?= view('admin/_messages'); ?>

   <section class="card">
      <div class="card-head">
         <h2 class="card-title inline-flex items-center gap-1">
            Impor petugas dari CSV
            <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Gunakan template CSV yang disediakan. Isi ID guru sesuai data sistem bila akun dihubungkan ke guru. Jangan pakai tanda kutip ganda (") di dalam file.</span></span>
         </h2>
         <a href="<?= base_url('admin/petugas'); ?>" class="btn btn-ghost btn-sm"><i class="material-icons">arrow_back</i>Data petugas</a>
      </div>

      <div class="card-body grid gap-4">
         <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
               <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-semibold text-primary-700">1</span>
               <span class="text-sm font-medium text-slate-800">Unduh template, lalu isi data</span>
            </div>
            <form action="<?= base_url('admin/petugas/downloadCSVFilePost'); ?>" method="post" class="flex flex-wrap gap-2">
               <?= csrf_field(); ?>
               <button type="button" class="btn btn-ghost btn-sm" data-bs-toggle="modal" data-bs-target="#modalGuru">
                  <i class="material-icons">list_alt</i>Lihat ID guru
               </button>
               <button class="btn btn-secondary btn-sm" name="submit" value="csv_petugas_template">
                  <i class="material-icons">download</i>Template CSV
               </button>
            </form>
         </div>

         <div class="grid gap-2.5">
            <div class="flex items-center gap-2.5">
               <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-semibold text-primary-700">2</span>
               <span class="text-sm font-medium text-slate-800">Unggah file CSV</span>
            </div>
            <div class="dm-uploader-container">
               <div id="drag-and-drop-zone" class="dm-uploader cursor-pointer">
                  <i class="material-icons text-3xl! text-slate-400">upload_file</i>
                  <p class="mt-1 text-sm text-slate-600">Tarik file CSV ke sini, atau</p>
                  <div class="btn btn-primary relative mt-3 overflow-hidden">
                     <span>Pilih file</span>
                     <input type="file" title='Click to add Files' class="absolute inset-0 h-full w-full cursor-pointer opacity-0" />
                  </div>
               </div>
            </div>

            <div id="csv_upload_spinner" class="csv-upload-spinner text-center" style="display:none;">
               <strong class="text-csv-importing text-sm font-semibold text-primary-700">Mengimpor data petugas…</strong>
               <strong class="text-csv-import-completed text-sm font-semibold text-success-700" style="display: none;">Selesai.</strong>
               <div class="spinner-bounce"><div class="spinner mt-3"></div></div>
            </div>

            <div class="csv-uploaded-files-container max-h-64 overflow-y-auto">
               <ul id="csv_uploaded_files" class="list-group csv-uploaded-files divide-y divide-slate-100 text-sm [&>li]:py-1.5 [&>.list-group-item-danger]:text-red-700 [&>.list-group-item-success]:text-green-700 [&>.list-group-item-warning]:text-amber-700"></ul>
            </div>
         </div>
      </div>
   </section>
</div>

<!-- Modal Daftar Guru -->
<div class="modal fade" id="modalGuru" tabindex="-1" aria-labelledby="modalGuruTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="modalGuruTitle">Daftar ID guru</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
               <i class="material-icons">close</i>
            </button>
         </div>
         <div class="modal-body max-h-[60vh] overflow-y-auto p-0!">
            <div class="table-wrap">
               <table class="table">
                  <thead>
                     <tr>
                        <th>ID</th>
                        <th>Nama guru</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach ($guru as $value) : ?>
                        <tr>
                           <td class="tabular-nums font-medium text-primary-700">#<?= $value['id_guru']; ?></td>
                           <td class="text-slate-900"><?= $value['nama_guru']; ?></td>
                        </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(function() {
        $('#drag-and-drop-zone').dmUploader({
            url: '<?= base_url("admin/petugas/generateCSVObjectPost"); ?>',
            multiple: false,
            extFilter: ["csv"],
            extraData: function(id) {
                return {
                    '<?= csrf_token() ?>': '<?= csrf_hash(); ?>'
                };
            },
            onDragEnter: function() {
                this.addClass('active');
            },
            onDragLeave: function() {
                this.removeClass('active');
            },
            onNewFile: function(id, file) {
                $("#csv_upload_spinner").show();
                $("#csv_upload_spinner .spinner-bounce").show();
                $("#csv_upload_spinner .text-csv-importing").show();
                $("#csv_upload_spinner .text-csv-import-completed").hide();
                $("#csv_uploaded_files").empty();
            },
            onUploadSuccess: function(id, response) {
                var numberOfItems = 0;
                var txtFileName = "";
                try {
                    var obj = JSON.parse(response);
                    if (obj.result == 1) {
                        numberOfItems = obj.numberOfItems;
                        txtFileName = obj.txtFileName;
                        if (numberOfItems > 0) {
                            addCSVItem(numberOfItems, txtFileName, 1);
                        } else {
                            $("#csv_upload_spinner").hide();
                        }
                    } else {
                        $("#csv_upload_spinner").hide();
                    }

                } catch (e) {
                    alert("Invalid CSV file! Make sure there are no double quotes in your content. Double quotes can break the CSV structure.");
                }
            }
        });
    });

    function addCSVItem(numberOfItems, txtFileName, index) {
        if (index <= numberOfItems) {
            var data = {
                'txtFileName': txtFileName,
                'index': index
            };
            $.ajax({
                type: "POST",
                url: '<?= base_url("admin/petugas/importCSVItemPost"); ?>',
                data: setAjaxData(data),
                success: function(response) {
                    var objSub = JSON.parse(response);
                    if (objSub.result == 1) {
                        var $liSuccess = $('<li class="list-group-item list-group-item-success"><i class="material-icons" style="font-size:14px;vertical-align:middle;margin-right:4px;">check_circle</i> </li>');
                        $liSuccess.append(document.createTextNode(objSub.index + '. ' + objSub.petugas.username + ' - ' + objSub.petugas.email));
                        $("#csv_uploaded_files").prepend($liSuccess);
                    } else {
                        var msg = objSub.message ? objSub.message : '(Gagal/Duplicate)';
                        $("#csv_uploaded_files").prepend('<li class="list-group-item list-group-item-danger"><i class="material-icons" style="font-size:14px;vertical-align:middle;margin-right:4px;">error</i> ' + objSub.index + '. ' + msg + '</li>');
                    }
                    if (objSub.index == numberOfItems) {
                        $("#csv_upload_spinner .text-csv-importing").hide();
                        $("#csv_upload_spinner .spinner-bounce").hide();
                        $("#csv_upload_spinner .text-csv-import-completed").css('display', 'block');
                    }
                    index = index + 1;
                    addCSVItem(numberOfItems, txtFileName, index);
                },
                error: function(xhr, status, thrown) {
                    var errorMsg = 'Ada Kesalahan Pada CSV';
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMsg += ': ' + errorResponse.message;
                        }
                    } catch (e) {
                        if (xhr.responseText) {
                            errorMsg += '. Status: ' + status;
                        }
                    }
                    swal({
                        text: errorMsg,
                        icon: "warning"
                    }).then(function(willDelete) {
                        if (willDelete) {
                            location.reload();
                        }
                    });
                },
            });
        }
    }
</script>
<?= $this->endSection() ?>
