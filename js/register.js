$(document).ready(function(){

    $.ajax({
            url: 'main.php?function=fetchoptions',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var $select = $('#typesel');
                $.each(data, function(index, item) {
                    $select.append($('<option>', {
                        value: item.typeid,
                        text: item.typename
                    }));
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
    });

    $('#addform').on('submit', function(e) {
    {
        console.log(emailError);
        if(emailError == false){
            e.preventDefault(e); 
            alert("Check Email ID");
        }
        else{
            e.preventDefault(e); 
            let formData = new FormData();
            formData.append('name', $("#name").val());
            formData.append('email', $("#email").val());
            formData.append('phone', $("#phone").val());
            formData.append('password', $("#password1").val());
            formData.append('usertype', $("#typesel").val());
            formData.append('function',"adduser");
            console.log(formData);
            $.ajax({
                type: "POST",
                url: "main.php",
                data: formData,
                dataType: "json",
                processData: false, 
                contentType: false, 
                success: function(response) { 
                    window.alert(response.response); 
                    console.log(response);
                    window.location.href = 'index.html';
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("An error occurred: " + xhr.status + " " + xhr.statusText);
                    console.error("Error:", xhr, textStatus, errorThrown);
                }
            });
            return false;
        }
    }

});
});