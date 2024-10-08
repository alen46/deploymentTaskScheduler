$(document).ready(function(){
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
    
    $.ajax({
        url:'main.php?function=disabledate',
        method:'GET',
        dataType:'json',
        success: function(data){
            console.log(data);
            const disdate = data;
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                datesDisabled: disdate,
                startDate: "today",
                adaptivePosition: true,
                container: '.form-container', 
                autoclose: true,
                daysOfWeekDisabled: [0, 6],
            }).on('show', function(e) {
                $('.datepicker').appendTo($(this).parent()); 
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });

    $.ajax({
        url: 'main.php?function=fetchportal&from=deploymentdetails',
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
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });

    $('#deploymentdetails').on('submit', function(e) {
        e.preventDefault(e); 
        let formData = new FormData(this);
        formData.append('function',"adddeployment");
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
    });
});