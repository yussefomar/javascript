 
 $(document).ready(function(){
     
     $(".nick-input").blur(function(){
         
         var nick = this.value;
        
        $.ajax({
            
            url: '/symfony3/curso-social-network/web/app_dev.php/nick-test',
            data:{nick : nick},
            type: 'POST',
            success: function(response){
                
                if(response=="used"){
                    $(".nick-input").css("border","1px solid red");
                    
                }else{
                    $(".nick-input").css("border","1px solid green");
                }
                
            }
        });
         
         
     });
        
        
 });


