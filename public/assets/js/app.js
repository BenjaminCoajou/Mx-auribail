let app = {
    init: function () {
        let lastPos = 0;
        window.addEventListener("scroll", function() {
           let pos = this.window.pageYOffset;
           if (pos>lastPos){
               if( document.getElementById('wheel-icon')) {
                 document.getElementById('wheel-icon').style.animation = "title-rotate-right 2s";
               }
               document.getElementById('ftco-navbar').style.display ='none';
            } else {
              if( document.getElementById('wheel-icon')) {
                document.getElementById('wheel-icon').style.animation = "title-rotate-left 2s";
              }
                document.getElementById('ftco-navbar').style.display ='block';
            

           }
           lastPos = pos <= 0 ? 0 : pos;
       });

       konamiCode();
       diaporama();  
       
    }
};

let secret = "1";
function konamiCode() {
 document.body.addEventListener("keyup", event => {
   if (event.key) {
         secret = secret + event.key;

     let konamiCode = secret.indexOf("ArrowUpArrowUpArrowDownArrowDownArrowLeftArrowRightArrowLeftArrowRightba");
     if (konamiCode > 0) {
         secret = "1";
         document.location.href = "http://localhost/Mx-auribail/public/dev";
     }
   }
 });
}

let i = 2;
function diaporama() {
    setInterval(function() {
        let image = document.getElementById('home');
        image.style.backgroundImage = "url(http://localhost/Mx-auribail/public/assets/img/motocross-"+i+".jpg)";
          i++;
          if (i == 6) {
            i = 1;
          }
    }, 120000);
    
}




app.init();