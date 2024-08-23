$(document).ready(function(){
    $("#passwordvalid").hide();
    let passwordError = true;
    $("#newpassword").keyup(function () {
        validatePassword();
    });
    function validatePassword() {
        let passwordValue = $("#newpassword").val();
        if (passwordValue.length === "") {
            $("#passwordvalid").show();
            passwordError = false;
            return false;
        }
        if (passwordValue.length < 8 || passwordValue.length > 25) {
            $("#passwordvalid").show();
            $("#passwordvalid").text( "password must be between 8 and 25 charecters" );
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
    $("#newpassword2").keyup(function () {
        validateConfirmPassword();
    });
    function validateConfirmPassword() {
        let confirmPasswordValue = $("#newpassword2").val();
        let passwordValue = $("#newpassword").val();
        if (passwordValue !== confirmPasswordValue) {
            $("#passwordvalid2").show();
            $("#passwordvalid2").html("Password didn't Match");
            $("#passwordvalid2").css("color", "red");
            confirmPasswordError = false;
            return false;
        } else {
            $("#passwordvalid2").hide();
            confirmPasswordError = true;
        }
    }
});