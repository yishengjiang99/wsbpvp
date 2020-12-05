function start(){
	const clockdisplay=document.querySelector("div");
const evt = new EventSource("/clock.php");
evt.onopen = function(event) {
	evt.addEventListener("tick",({data})=>{
		clockdisplay.innerHTML = data.split(" ")[1];
	})
	evt.onclose(()=>{
		clockdisplay.innerHTML='connection lost';
	})
}
}
