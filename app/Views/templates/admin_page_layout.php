<!DOCTYPE html>
<html lang="id">

<?= $this->include("templates/head") ?>

<body>
   <div class="flex min-h-screen">
      <?= $this->include("templates/sidebar") ?>

      <div class="flex min-w-0 flex-1 flex-col">
         <?= $this->include("templates/navbar") ?>

         <main class="flex-1 p-4 md:p-6">
            <?= $this->renderSection("content") ?>
         </main>

         <?= $this->include("templates/footer") ?>
      </div>
   </div>

   <?= $this->include("templates/js") ?>

   <script>
      var BaseConfig = {
         baseURL: '<?= base_url() ?>',
         csrfTokenName: '<?= csrf_token() ?>',
         textOk: "Ok",
         textCancel: "Batal"
      };
   </script>

   <?= $this->renderSection("scripts") ?>
</body>

</html>
