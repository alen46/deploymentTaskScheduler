$().ready(function () {
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
    $("#emailerror").hide();
    $("form[name='registration']").validate({
        rules: {
            name: {
                required: true,
                minlength: 3 // For length of lastname
            },
            password1: {
                required: true,
                minlength: 8,
                strong_password: true
            },
            password2: {
                required: true,
                equalTo: "#password1",
                strong_password: true
            },
            email: {
                required: true,
                email: true
            },
            agree: "required"
        },
        // In 'messages' user have to specify message as per rules
        messages: {
            name: {
                required: " Please enter a username",
                minlength:" Your username must consist of at least 3 characters"
            },
            password1: {
                required: " Please enter a password",
                minlength:" Your password must be consist of at least 8 characters"
            },
            password2: {
                required: " Please enter a password",
                equalTo: " Please enter the same password as above"
            },
            email: {
                remote: "Email already exist"
            }
        }
    });
    $("#addform").submit(function(e) {
        e.preventDefault();
        let email = $('#email').val();
        $.ajax({
            url: `main.php?function=emailcheck&email=${email}`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log(data);
                if(data == 'ok'){
                    emailError = false;
                    alert('email already exist')
                    $("#emailerror").show();
                }else if(data == "xx"){
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
                }
            },
            error: function(error,xhr,status){
                alert(error);
            }
        });
        return false; 
    });

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
