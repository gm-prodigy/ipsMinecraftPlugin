;( function($, _, undefined){
    "use strict"
    document.getElementById("button1").onclick = function() {showAuthID()};
    function showAuthID() {
        const x = document.getElementById("div1");
        if (x.style.display !== "none") {
            x.style.display = "none";
            event.target.innerText = 'Show Auth Key'
        } else {
            x.style.display = "block";
            event.target.innerText = 'Hide Auth Key'
        }
    }

console.log("testsssss");

    // function countdown() {
    //     var i = document.getElementById('counter');
    //     // if (parseInt(i.innerHTML)<=0) {
    //     //     location.href = '#';
    //     // }
    //     if (parseInt(i.innerHTML)!=0) {
    //         i.innerHTML = parseInt(i.innerHTML)-1;
    //     }
    // }
    // setInterval(function(){ countdown(); },1000);
}(jQuery, _));