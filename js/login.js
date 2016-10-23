if(document.addEventListener){
    window.addEventListener('load',function(){
        var input_password = [];
        var tag_input = document.getElementById('div_reg').getElementsByTagName('input');
        for (var i=0,n=tag_input.length;i<n;i++){
            if (tag_input[i].type=='password'){
                input_password.push(tag_input[i]);
            }
        }
        var show_pass = document.getElementById('show_password');
        show_pass.addEventListener('click',function(){
            if (show_pass.checked){
                for (i=0, n=input_password.length;i<n;i++){
                    input_password[i].type='text';
                }
            }
            else {
                for (i=0, n=input_password.length;i<n;i++){
                    input_password[i].type='password';
                }    
            }
        },false)
    },false)
}