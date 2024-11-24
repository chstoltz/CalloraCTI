<div class="w3-display-bottommiddle w3-large w3-tag w3-blue w3-margin w3-round w3-card" id="feedback"><?php echo @$feedback; ?></div>
</div>
<script>$('#feedback').delay(1000).fadeOut('slow').hide(0);</script>
     <script>
        let form = document.getElementById("form");
        form.addEventListener("submit", function (event){ 
          event.preventDefault(); 
          let data = new FormData(form);
          let xhr = new XMLHttpRequest();
          let method = form.getAttribute("method").toUpperCase();
          let url = form.getAttribute("action");
          xhr.open(method, url, true);
          xhr.send(data);
        });
      </script> 
    </body>
</html>
