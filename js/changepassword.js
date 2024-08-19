$(document).ready(function(){
  
    $('#pswdchng').on('submit', function(e) {
        e.preventDefault(e); 
        let formData = {
            'newpassword': $("#newpassword").val(),
            'function': 'changepassword'
        }
        console.log(formData)
        console.log($("#newpassword").val());
        $.ajax({
            type: "POST",
            url: "main.php",
            data: formData,
            dataType: "json",
            // processData: false, 
            // contentType: false, 
            success: function(response) { 
                window.alert(response.response); 
                console.log(response);
                //window.location.href = 'index.html';
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