<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="mx-auto max-w-3xl space-y-4">
   <?= view('admin/_messages'); ?>

   <section class="card">
      <div class="card-head">
         <div>
            <h2 class="card-title inline-flex items-center gap-1">
               Import guru dari CSV
               <span class="tip" tabindex="0"><i class="material-icons">info</i><span class="tip-text">Pakai template CSV. Jangan pakai tanda kutip ganda (") di isi file.</span></span>
            </h2>
         </div>
         <a href="<?= base_url('admin/guru'); ?>" class="btn btn-ghost btn-sm"><i class="material-icons">arrow_back</i>Kembali</a>
      </div>

      <ol class="divide-y divide-slate-100">
         <li class="flex gap-3 p-4">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-semibold text-primary-700">1</span>
            <div class="min-w-0 flex-1">
               <h3 class="font-semibold text-slate-900">Unduh template</h3>
               <form action="<?= base_url('admin/guru/downloadCSVFilePost'); ?>" method="post" class="mt-2 flex flex-wrap gap-2">
                  <?= csrf_field(); ?>
                  <button class="btn btn-secondary btn-sm" name="submit" value="csv_guru_template">
                     <i class="material-icons">download</i>Template CSV
                  </button>
               </form>
            </div>
         </li>

         <li class="flex gap-3 p-4">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-semibold text-primary-700">2</span>
            <div class="min-w-0 flex-1">
               <h3 class="font-semibold text-slate-900">Unggah CSV</h3>
               <div class="dm-uploader-container mt-2">
                  <div id="drag-and-drop-zone" class="dm-uploader !p-4">
                     <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-2">
                        <i class="material-icons text-slate-400">upload_file</i>
                        <span class="text-slate-600">Tarik file CSV ke sini atau</span>
                        <div class="btn btn-primary btn-sm relative overflow-hidden">
                           <span>Pilih file</span>
                           <input type="file" title="Pilih file CSV" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" />
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </li>

         <li class="flex gap-3 p-4">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-semibold text-primary-700">3</span>
            <div class="min-w-0 flex-1">
               <h3 class="font-semibold text-slate-900">Hasil</h3>
               <div id="csv_upload_spinner" class="csv-upload-spinner mt-2" style="display:none;">
                  <strong class="text-csv-importing font-semibold text-primary-700">Mengimpor data guru…</strong>
                  <strong class="text-csv-import-completed font-semibold text-success-700" style="display: none;">Selesai.</strong>
                  <div class="spinner-bounce spinner !ml-2 !mt-0 !inline-block !h-4 !w-4 align-middle">
                     <div class="bounce1"></div>
                     <div class="bounce2"></div>
                     <div class="bounce3"></div>
                  </div>
               </div>
               <div class="csv-uploaded-files-container mt-1 max-h-64 overflow-y-auto">
                  <ul id="csv_uploaded_files" class="csv-uploaded-files divide-y divide-slate-100">
                     <li class="py-1.5 text-slate-500">Belum ada file diunggah.</li>
                  </ul>
               </div>
            </div>
         </li>
      </ol>
   </section>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(function() {
        $('#drag-and-drop-zone').dmUploader({
            url: '<?= base_url("admin/guru/generateCSVObjectPost"); ?>',
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
                    alert("Invalid CSV file! Make sure there are no double quotes in your content. Double quotes can brake the CSV structure.");
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
                url: '<?= base_url("admin/guru/importCSVItemPost"); ?>',
                data: setAjaxData(data),
                success: function(response) {
                    var objSub = JSON.parse(response);
                    if (objSub.result == 1) {
                        $("#csv_uploaded_files").prepend('<li class="flex items-center gap-1.5 py-1.5 text-slate-700"><i class="material-icons text-base text-success-700">check_circle</i> ' + objSub.index + '. ' + objSub.guru.nuptk + ' - ' + objSub.guru.nama_guru + '</li>');
                    } else {
                        $("#csv_uploaded_files").prepend('<li class="flex items-center gap-1.5 py-1.5 text-danger-700"><i class="material-icons text-base">cancel</i> ' + objSub.index + '. Gagal</li>');
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
                    swal({
                        text: 'Ada Kesalahan Pada CSV silahkan Cek Log',
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
