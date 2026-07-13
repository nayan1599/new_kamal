
      <footer class="admin-footer">
        <div class="container-fluid px-3 px-lg-4">
          <span>Copyright <?php echo date('Y') ;?> Kamal. <br> Developed by <a target="_blank" class="fw-bold text-success" href="#">Md. Nayan</a>   </span>
          <!-- <span>Professional dashboard template.</span> -->
        </div>
      </footer>
    </div>
  </div>

  <script src="./assets/js/bootstrap.bundle.min.js"></script>
  <script src="./assets/js/main.js"></script>
  <script>
function printDiv(divId) {
    var content = document.getElementById(divId).innerHTML;
    var original = document.body.innerHTML;

    document.body.innerHTML = content;
    window.print();
    document.body.innerHTML = original;

    location.reload(); // optional
}
</script>