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
                if(response.type != '100' && response.type != '102' ){
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
    
    
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }
        var url = getQueryParam('data');

        $.ajax({
            url: `main.php?function=viewdetails&purl=${url}&from=deployment`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log(data);
                $("#portal_url").val(data.purl);
                $("#portal_name").val(data.portalname);
                $("#user_note").val(data.user_note);
                $("#existing_date").val(data.deployment_date);
                $("#current_version").val(data.version);
                $("#deployment_version").val(data.deployment_version);
                $("#portal_features").val(data.pfeatures);
                $("#new_features").val(data.deployment_note);
                $("#deployment_date").val(data.deployment_date);
                $("#days").val(data.required_days);
                $("#username").val(data.username);
                $("#deployment_id").val(data.deployment_id);
                $("#change_date").val(data.new_date);
                $("#downloadbtn").click(function(event) {
                    event.preventDefault();
                    var fileUrl = data.deployment_plan;
                    var fileName = fileUrl.split('/').pop();
        
                    var a = document.createElement('a');
                    a.href = fileUrl;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }     
    });

    $('#acceptbtn').on('click', function(e) {
    {
        e.preventDefault(e);
        let formData = {
            'status':"Accepted",
            'function':"managechange",
            'deployment_id':$("#deployment_id").val()
        };
        JSON.stringify(formData);
        console.log(formData);
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
               // location.reload();
            },
            error: function(xhr, textStatus, errorThrown){
                alert("An error occurred: " + xhr.status + " " + xhr.statusText);
                console.error("Error:", xhr, textStatus, errorThrown);
            }
        });
        return false;
    }
    }); 

    $('#rejectbtn').on('click', function(e) {
        {
            e.preventDefault(e);
            let formData = {
                'status':"Rejected",
                'function':"managechange",
                'deployment_id':$("#deployment_id").val()
            };
            JSON.stringify(formData);
            console.log(formData);
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
                   // location.reload();
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("An error occurred: " + xhr.status + " " + xhr.statusText);
                    console.error("Error:", xhr, textStatus, errorThrown);
                }
            });
            return false;
        } 
    }); 


});

