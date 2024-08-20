$(document).ready(function(){
  
    $('#loginform').on('submit', function(e) {
        e.preventDefault(e); 
        let formData = new FormData();
        formData.append('email', $("#email").val());
        formData.append('password', $("#password").val());
        formData.append('function',"login");
        console.log(formData);
        $.ajax({
            type: "POST",
            url: "main.php",
            data: formData,
            dataType: "json",
            processData: false, 
            contentType: false, 
            success: function(response) { 
                console.log(response);
                if(response.response == "Email or Password Incorrect"){
                    alert(response.response);
                    location.reload();
                }
                else{
                    window.alert(response.response); 
                    window.location.href = 'index.html';
                } 
            },
            error: function(xhr, textStatus, errorThrown){
                alert("An error occurred: " + xhr.status + " " + xhr.statusText);
                console.error("Error:", xhr, textStatus, errorThrown);
            }
        });
        return false;
    }
);
});