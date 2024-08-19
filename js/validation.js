$(document).ready(function () {

    $("#namecheck").hide();
    let nameerror = true;

    $("#name").keyup(function () {
        validateUsername();
    });

    $("#email").keyup(function () {
        email.dispatchEvent(new Event('blur'));
    });
   
    $("#phone").keyup(function () {
        phone.dispatchEvent(new Event('blur'));
    });

    $("#email").keyup(function () {
        let email = $("#email").val();
        $.ajax({
            url: `main.php?function=emailcheck&email=${email}`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log(data);
                if(data == 'ok'){
                    emailError = false;
                    $('#email').addClass("is-invalid");
                    $("#emailerror").text("email alerady exist");
                    $("#emailerror").show();
                }
            }
        });
    });

    
    function validateUsername() {
        let name = $("#name").val();
        if (name.length === "") {
            $("#namecheck").show();
            nameerror = false;
            return false;
        } else if (name.length < 3 || name.length > 25) {
            $("#namecheck").show();
            $("#namecheck").html("length of username must be between 3 and 25");
            nameerror = false;
            return false;
        } else {
            $("#namecheck").hide();
            nameerror = true;
        }
    }
        
        
    $('#email').on('blur', function() {
            var regex = /^([_\-\.0-9a-zA-Z]+)@([_\-\.0-9a-zA-Z]+)\.([a-zA-Z]){2,7}$/;
            var emailValue = $(this).val();
    
            if(regex.test(emailValue)) {
                $(this).removeClass("is-invalid");
                emailError = false; 
            } else {
                $(this).addClass("is-invalid");
                emailError = true; 
            }
    });


    $('#phone').on('blur', function() {
        var filter = /^\d*(?:\.\d{1,2})?$/;
        var p = $(this).val();
        if(filter.test(p) && p.length==10) {
            $(this).removeClass("is-invalid");
            phoneerror = false; 
        } else {
            $(this).addClass("is-invalid");
            phoneerror = true; 
        }
    });


    $("#passwordvalid").hide();
    let passwordError = true;
    $("#password1").keyup(function () {
        validatePassword();
    });
    function validatePassword() {
        let passwordValue = $("#password1").val();
        if (passwordValue.length === "") {
            $("#passwordvalid").show();
            passwordError = false;
            return false;
        }
        if (passwordValue.length < 8 || passwordValue.length > 25) {
            $("#passwordvalid").show();
            $("#passwordvalid").text( "**length of your password must be between 8 and 25" );
            //$("#passwordvalid").css("color", "red");
            passwordError = false;
            return false;
        } else {
            $("#passwordvalid").hide();
            passwordError = true;
        }
    }

    $("#passwordvalid2").hide();
    let confirmPasswordError = true;
    $("#password2").keyup(function () {
        validateConfirmPassword();
    });
    function validateConfirmPassword() {
        let confirmPasswordValue = $("#password2").val();
        let passwordValue = $("#password1").val();
        if (passwordValue !== confirmPasswordValue) {
            $("#passwordvalid2").show();
            $("#passwordvalid2").html("**Password didn't Match");
            $("#passwordvalid2").css("color", "red");
            confirmPasswordError = false;
            return false;
        } else {
            $("#passwordvalid2").hide();
            confirmPasswordError = true;
        }
    }
    


    $("#submitbtn").click(function () {
        validateUsername();
        email.dispatchEvent(new Event('blur')); 
        phone.dispatchEvent(new Event('blur'));
        validateConfirmPassword();
        validatePassword();
        
        if (
            nameerror &&
            emailError
        ) {
            return true;
        } else {
            return false;
        }
    });
});