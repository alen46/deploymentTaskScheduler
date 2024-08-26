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
    $("form[name='passwordchange']").validate({
        rules: {
            password1: {
                required: true,
                minlength: 8,
                strong_password: true
            },
            password2: {
                required: true,
                equalTo: "#password1",
                strong_password: true
            }
        },
        // In 'messages' user have to specify message as per rules
        messages: {
           
            password1: {
                required: " Please enter a password",
                minlength:" Your password must be consist of at least 8 characters"
            },
            password2: {
                required: " Please enter a password",
                equalTo: " Please enter the same password as above"
            }
        }
    });
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
);

$.validator.addMethod("strong_password", function (value, element) {
    let password = value;
    if (!(/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@#$%&])(.{8,20}$)/.test(password))) {
        return false;
    }
    return true;
}, function (value, element) {
    let password = $("#password1").val();
    if (!(/^(.{8,20}$)/.test(password))) {
        return 'Password must be between 8 to 20 characters long.';
    }
    else if (!(/^(?=.*[A-Z])/.test(password))) {
        return 'Password must contain at least one uppercase.';
    }
    else if (!(/^(?=.*[a-z])/.test(password))) {
        return 'Password must contain at least one lowercase.';
    }
    else if (!(/^(?=.*[0-9])/.test(password))) {
        return 'Password must contain at least one digit.';
    }
    else if (!(/^(?=.*[@#$%&])/.test(password))) {
        return "Password must contain special characters from @#$%&.";
    }
    return false;
});
});