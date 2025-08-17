function openOrderModal() {
      document.getElementById("orderModal").style.display = "block";
    }
    function closeOrderModal() {
      document.getElementById("orderModal").style.display = "none";
    }
    window.onclick = function(event) {
      let modal = document.getElementById("orderModal");
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }