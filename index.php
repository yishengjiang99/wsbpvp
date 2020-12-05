<html>
<head>
<style><?php echo file_get_contents("style.css")?></style>
</head>
<body>
<clock>

</clock>
<body>
<script>
const clockdisplay=document.querySelector("clock");

const evt = new EventSource("/clock.php");
evt.onopen = function(event) {
	evt.addEventListener("tick",({data})=>{
		clockdisplay.innerHTML = data.split(" ")[1];
	})
	evt.onclose(()=>{
		clockdisplay.innerHTML='connection lost';
	})

}
</script>
</body>
