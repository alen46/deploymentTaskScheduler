$(document).ready(function(){

    $(".hamburger").click(function(){
        $(this).toggleClass("active");
        $(".nav-menu").toggleClass("active");
    });

        $(".nav-link").click(function(){
        $(".hamburger").removeClass("active");
        $(".nav-menu").removeClass("active");
    });

    $.ajax({
            url: 'main.php?function=checklogin',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.type != '100' && response.type != '102'){
                    window.location.href = 'notfound.html';
                }
                if(response.response == "logout"){
                    $("#login").text("Logout");
                    $("#login").off('click').click(function() {
                        $.ajax({
                            url: 'main.php?function=logout',
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                if(response.response == "login"){
                                    alert(response.response)
                                    window.location.href = 'index.html';   
                                }else{
                                    $("#login").text("Login").attr("href", "login.html");
                                }
                                
                            },
                            error: function(xhr, status, error) {
                                console.error('Error fetching data:', error);
                            }
                        });
                    });     

                }else{
                    $("#login").text("Login").attr("href", "login.html");
                }
                
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });
    $('#addportal').on('submit', function(e) {
       
        e.preventDefault(e); 
        let formData = new FormData();
        formData.append('portal_name', $("#portal_name").val());
        formData.append('portal_url', $("#portal_url").val());
        formData.append('portal_version', $("#portal_version").val());
        formData.append('portal_features', $("#portal_features").val());
        formData.append('function',"addportal");
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
                location.reload();
            },
            error: function(xhr, textStatus, errorThrown){
                alert("An error occurred: " + xhr.status + " " + xhr.statusText);
                console.error("Error:", xhr, textStatus, errorThrown);
            }
        });
        return false;
    

});
});