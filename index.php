<html>
<head>
<style><?php echo file_get_contents("style.css") ?></style>
</head>
<body onload='start()'>
<div class="vscode">
      <div class="sideNavBar">1</div>
      <div class="fileList">2</div>
      <div class="cmdPalletTabs">3</div>
      <div class="editor">4</div>
      <div class="editor2">5</div>
      <div class="xterm">6</div>
    </div>
<body>
<script>
  function start() {
    const clockdisplay = document.querySelector("div.editor");
    const evt = new EventSource("/clock.php");
    evt.onopen = function (event) {
      evt.addEventListener("tick", ({ data }) => {
        clockdisplay.innerHTML = data.split(" ")[1];
      });
      evt.onclose(() => {
        clockdisplay.innerHTML = "connection lost";
      });
    };
  }
</script>
</body>
</html>
