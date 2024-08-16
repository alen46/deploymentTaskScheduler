$(document).ready(()=>{
    function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}
var url = getQueryParam('report');

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

    let date ={
        'type':'date',
        'from':$("#from").val(),
        'to':$("#to").val()
    };

    

    if(url == 'date'){
        let datetable =`<div class="container-xl px-4 mt-4">
            <div style=" padding: 25px; padding-left: 10px; padding-right: 10px; background-color: aliceblue;" >
                <table id="test" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Portal URL</th>
                            <th>Portal Name</th>
                            <th>Deployment Date</th>
                            <th>User Name</th>
                            <th>Required Days</th>
                        </tr>
                    </thead>
                </table>
            </div>   
        </div>`
        let datesel = `<div class="form-container">
                        <div class="form-group">
                            <label for="from"> From Date</label>
                            <input type="date" id="from" name="from" required>
                        </div>
                        <div class="form-group">
                            <label for="to"> To Date</label>
                            <input type="date" id="to" name="to" required>
                        </div>
                        <input type="submit" class="submit-btn" value="Save" id='datebtn'>
                        </div>`
        $("#form").html(datesel);
        $("#table").html(datetable)
        $("#datebtn").click(()=>{
            let date ={
            'type':'date',
            'from':$("#from").val(),
            'to':$("#to").val()
        };
        let table = $('#test').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "ajax": {
                "url": `test2.php`,
                "type": "POST",
                "data": date,
                "datatype": "json",
                "dataSrc": ""
            },
            "columns": [
                {"data": "purl"},
                {"data": "portalname"},
                {"data": "deployment_date"},
                {"data": "username"},
                {"data": "required_days"}
            ]
        });
        })
    }

    if(url == 'alldeployments'){
        let alltable =`<div class="container-xl px-4 mt-4">
            <div style=" padding: 25px; padding-left: 10px; padding-right: 10px; background-color: aliceblue;" >
                <table id="test" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Portal URL</th>
                            <th>Portal Name</th>
                            <th>Deployment Date</th>
                            <th>User Name</th>
                            <th>Required Days</th>
                        </tr>
                    </thead>
                </table>
            </div>   
        </div>`
        $("#table").html(alltable)
        let table = $('#test').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "ajax": {
                "url": `test2.php?type=${url}`,
                "type": "GET",
                "datatype": "json",
                "dataSrc": ""
            },
            "columns": [
                {"data": "purl"},
                {"data": "portalname"},
                {"data": "deployment_date"},
                {"data": "username"},
                {"data": "required_days"}
            ]
        });
        }

        
        $('#test tbody').on('click', 'tr', function () {
        let data = table.row(this).data();
        console.log(data.name);
        window.location.href = "viewschedulechange.html?data=" +data.purl;
    });
});