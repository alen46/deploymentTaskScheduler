$(document).ready(()=>{
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

        let table = $('#test').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "ajax": {
                "url": "main.php?function=deploymentstable",
                "type": "GET",
                "datatype": "json",
                "dataSrc": ""
            },
            "columns": [
                {"data": "deployment_date"},
                {"data": "purl"},
                {"data": "portalname"},
                {"data": "username"},
                {"data": "required_days"}
            ]
        });

        $('#test tbody').on('click', 'tr', function () {
        let data = table.row(this).data();
        console.log(data.name);
        window.location.href = "portaldeploymentdetails.html?data=" +data.purl;
    });
});