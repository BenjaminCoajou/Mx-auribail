let app = {
    init: function () {
        let lastPos = 0;
        window.addEventListener("scroll", function() {
           let pos = this.window.pageYOffset;
           if (pos>lastPos){
               console.log('down');
               document.getElementById('wheel-icon').style.animation = "title-rotate-right 2s";
               document.getElementById('ftco-navbar').style.display ='none';
            } else {
                document.getElementById('wheel-icon').style.animation = "title-rotate-left 2s";
                document.getElementById('ftco-navbar').style.display ='block';
            

           }
           lastPos = pos <= 0 ? 0 : pos;
       });

       konamiCode();  
       
    }
};

let secret = "1";
function konamiCode() {
 document.body.addEventListener("keyup", event => {
   if (event.key) {
         secret = secret + event.key;
         console.log(secret);

     let konamiCode = secret.indexOf("ArrowUpArrowUpArrowDownArrowDownArrowLeftArrowRightArrowLeftArrowRightba");
     if (konamiCode > 0) {
         secret = "1";
         document.location.href = "http://localhost/Mx-auribail/public/dev";
     }
   }
 });
}




app.init();