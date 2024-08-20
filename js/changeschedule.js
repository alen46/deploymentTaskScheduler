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

    const today = new Date().toISOString().split('T')[0];
    $('#new_date').attr('min', today);

    $.ajax({
        url: 'main.php?function=fetchportal&from=changedeployment',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var $select = $('#portal_url');
            $.each(data, function(index, item) {
                $select.append($('<option>', {
                    value: item.portal_id,
                    text: item.purl
                }));
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });

    $('#portal_url').change(function() {
        var selectedValue = $(this).val();
        console.log("Selected Portal URL ID: " + selectedValue);
        $.ajax({
            url: `main.php?function=fetchscheduledetails&id=${selectedValue}`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log(data);
                $("#portal_name").val(data.portalname);
                $("#portal_version").val(data.deployment_version);
                $("#existing_date").val(data.deployment_date);
                $("#deployment_id").val(data.deployment_id);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });
    });

    $('#changeschedule').on('submit', function(e) {
        e.preventDefault(e); 
        let formData = new FormData(this);
        formData.append('function',"changeschedule");
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