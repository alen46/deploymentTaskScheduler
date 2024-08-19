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
                if(response.type != '100'){
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
    
        const today = new Date().toISOString().split('T')[0];
        $('#deployment_date').attr('min', today);
    
    
    $.ajax({
        url: 'main.php?function=fetchportal&from=changedeployment',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var $select = $('#portal_url');
            $.each(data, function(index, item) {
                $select.append($('<option>', {
                    value: item.pid,
                    text: item.purl
                }));
            });
            $('#portal_url').change(function() {
                let url = $(this).val();
                $.ajax({
                    url: `main.php?function=viewdetails&purl=${url}&from=deployment`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
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
                        $("#portal_id").val(data.portal_id);
                        $("#change_date").val(data.new_date);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }     
                });
            });
        
            $('#editdeployment').on('submit', function(e) {
                    e.preventDefault(e); 
                    let formData = new FormData(this);
                    formData.append('function',"editdeployment");
                    console.log(formData);
                    $.ajax({
                        type: "POST",
                        url: "main.php",
                        data: formData,
                        dataType: "json",
                        processData: false, 
                        contentType: false, 
                        success: function(response) { 
                            window.alert(response.message); 
                            console.log(response);
                            //location.reload();
                        },
                        error: function(xhr, textStatus, errorThrown){
                            alert("An error occurred: " + xhr.status + " " + xhr.statusText);
                            console.error("Error:", xhr, textStatus, errorThrown);
                        }
                    });
                    return false;
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });


    

});

