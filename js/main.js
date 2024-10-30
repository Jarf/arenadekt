var el = document.getElementById("copy");
if(el !== null){
	el.onclick = function(){
		document.querySelector("textarea").select();
		document.execCommand("copy");
	}	
}