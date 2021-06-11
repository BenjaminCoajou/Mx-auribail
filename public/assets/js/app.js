let app = {
    init: function () {
        let lastPos = 0;
        window.addEventListener("scroll", function() {
           let pos = this.window.pageYOffset;
           if (pos>lastPos){
               console.log('down');
               document.getElementById('wheel-icon').style.animation = "title-rotate-right 2s";
           } else {
            document.getElementById('wheel-icon').style.animation = "title-rotate-left 2s";
           }
           lastPos = pos <= 0 ? 0 : pos;
       });
    }
};

app.init();