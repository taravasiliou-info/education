<!-- Enable dropdown on hover -->
<script>
  document.querySelectorAll('.navbar .dropdown').forEach(function (dropdown) {
    dropdown.addEventListener('mouseenter', function () {
      let menu = bootstrap.Dropdown.getOrCreateInstance(this.querySelector('.dropdown-toggle'));
      menu.show();
    });
    dropdown.addEventListener('mouseleave', function () {
      let menu = bootstrap.Dropdown.getOrCreateInstance(this.querySelector('.dropdown-toggle'));
      menu.hide();
    });
  });
</script>