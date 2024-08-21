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
            if(response.type != '100' && response.type != '101'){
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
                        <input type="submit" class="submit-btn" value="View" id='datebtn'>
                        <input type="submit" class="submit-btn" style="background-color: grey;" value="Download" id='downloadbtn'>
                        </div>`
        $("#form").html(datesel);
        $("#table").html(datetable);
        $("#datebtn").click(()=>{
            if($("#from").val() == '' || $("#to").val() ==''){
                alert("Select Dates")
            }
            else{
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
            }
            })
                $("#downloadbtn").click(()=>{
                    if($("#from").val() == '' || $("#to").val() ==''){
                        alert("Select Dates")
                    }
                    else{
                        let date ={
                            'type':'date',
                            'from':$("#from").val(),
                            'to':$("#to").val()
                        };
                        $.ajax({
                            url: `test3.php`,
                            type: 'POST',
                            dataType: 'json',
                            data: date,
                            success: function(data) {
                                var link = document.createElement('a');
                                link.href = data.file;
                                link.click();
                            }
                        });
                    }
                });    
    }

    if(url == 'alldeployments'){
        let alltable =`
        <input type="submit" class="submit-btn" style="background-color: grey;" value="Download" id='downloadbtn'>
        <div class="container-xl px-4 mt-4">
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
        $("#downloadbtn").click(()=>{
            let date ={
                'type':'alldeployments',
            };
            $.ajax({
                url: `test3.php`,
                type: 'POST',
                dataType: 'json',
                data: date,
                success: function(data) {
                    var link = document.createElement('a');
                    link.href = data.file;
                    link.click();
                }});
        });
    }

    if(url == 'change'){
        let alltable =`
        <input type="submit" class="submit-btn" style="background-color: grey;" value="Download" id='downloadbtn'>
        <div class="container-xl px-4 mt-4">
            <div style=" padding: 25px; padding-left: 10px; padding-right: 10px; background-color: aliceblue;" >
                <table id="test" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Change Date</th>
                            <th>Portal URL</th>
                            <th>Portal Name</th>
                            <th>Old Date</th>
                            <th>New Date</th>
                            <th>Change Info</th>
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
                {"data": "ChangeDate"},
                {"data": "PortalURL"},
                {"data": "PortalName"},
                {"data": "OldDate"},
                {"data": "NewDate"},
                {"data": "ChangeInfo"}
            ]
        });
        $("#downloadbtn").click(()=>{
            let date ={
                'type':'change',
            };
            $.ajax({
                url: `test3.php`,
                type: 'POST',
                dataType: 'json',
                data: date,
                success: function(data) {
                    var link = document.createElement('a');
                    link.href = data.file;
                    link.click();
                }});
        });
    }

        
    if(url == 'user'){
        let usersel = `<div class="form-container">
                            <div class="form-group">
                            <label  for="usrsel">Select User</label>
                            <select class="form-control" id="usrsel" name="usrsel">
                                <option value="" selected disabled>Select User</option>
                            </select>
                            </div>
                            <input type="submit" class="submit-btn" value="View" id='usrbtn'>
                            <input type="submit" class="submit-btn" style="background-color: grey;" value="Download" id='downloadbtn'>
                        </div>`

        let usertbl =`
        <div class="container-xl px-4 mt-4">
            <div style=" padding: 25px; padding-left: 10px; padding-right: 10px; background-color: aliceblue;" >
                <table id="test" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Portal URL</th>
                            <th>Portal Name</th>
                            <th>Deployment Date</th>
                            <th>Required Days</th>
                        </tr>
                    </thead>
                </table>
            </div>   
        </div>`
        $.ajax({
            url: 'main.php?function=fetchportal&from=usr',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var $select = $('#usrsel');
                $.each(data, function(index, item) {
                    $select.append($('<option>', {
                        value: item.userid,
                        text: item.username
                    }));
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });

        $("#form").html(usersel);
        $("#table").html(usertbl);
        $("#usrbtn").click(()=>{
            console.log("user val : " + $("#usrsel").val())
            if($("#usrsel").val() == null){
                alert("select user")
            }
            else{
                let usrdata ={
                    'type':'user',
                    'usr':$("#usrsel").val(),
                };
                let table = $('#test').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "ajax": {
                        "url": `test2.php`,
                        "type": "POST",
                        "datatype": "json",
                        "data": usrdata,
                        "dataSrc": ""
                    },
                    "columns": [
                        {"data": "username"},
                        {"data": "purl"},
                        {"data": "portalname"},
                        {"data": "deployment_date"},
                        {"data": "required_days"}
                    ]
                });
            }
            
        });
        
        $("#downloadbtn").click(()=>{
            if($("#usrsel").val() == null){
                alert("select user")
            }
            else{
                let x = $("#usrsel").val();
                let usrdata ={
                    'type':'user',
                    'usr': x
                };
                console.log(usrdata);
                $.ajax({
                    url: `test3.php`,
                    type: 'POST',
                    dataType: 'json',
                    data: usrdata,
                    success: function(data) {
                        var link = document.createElement('a');
                        link.href = data.file;
                        link.click();
                    }
                });
            }
        });
    }

    if(url == 'portal'){
        let psel = `<div class="form-container">
                            <div class="form-group">
                            <label  for="psel"Select Portal</label>
                            <select class="form-control" id="psel" name="psel">
                                <option value="" selected disabled>Select Portal</option>
                            </select>
                            </div>
                            <input type="submit" class="submit-btn" value="View" id='pbtn'>
                            <input type="submit" class="submit-btn" style="background-color: grey;" value="Download" id='downloadbtn'>
                        </div>`

        let ptbl =`
        <div class="container-xl px-4 mt-4">
            <div style=" padding: 25px; padding-left: 10px; padding-right: 10px; background-color: aliceblue;" >
                <table id="test" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Portal URL</th>
                            <th>Portal Name</th>
                            <th>Deployment Date</th>
                            <th>Required Days</th>
                        </tr>
                    </thead>
                </table>
            </div>   
        </div>`
        $.ajax({
            url: 'main.php?function=fetchportal&from=portl',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var $select = $('#psel');
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

        $("#form").html(psel);
        $("#table").html(ptbl);
        $("#pbtn").click(()=>{
            if($("#psel").val() == null){
                alert("select portal")
            }
            else{
                let pdata ={
                    'type':'portal',
                    'portl':$("#psel").val(),
                };
                let table = $('#test').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "ajax": {
                        "url": `test2.php`,
                        "type": "POST",
                        "datatype": "json",
                        "data": pdata,
                        "dataSrc": ""
                    },
                    "columns": [
                        {"data": "username"},
                        {"data": "purl"},
                        {"data": "portalname"},
                        {"data": "deployment_date"},
                        {"data": "required_days"}
                    ]
                });
            }
        });
        $("#downloadbtn").click(()=>{
            if($("#psel").val() == null){
                alert("select user")
            }
            else{
                let x = $("#psel").val();
                let pdata ={
                    'type':'portal',
                    'portl': x
                };
                console.log(pdata);
                $.ajax({
                    url: `test3.php`,
                    type: 'POST',
                    dataType: 'json',
                    data: pdata,
                    success: function(data) {
                        var link = document.createElement('a');
                        link.href = data.file;
                        link.click();
                    }
                });
            }
        });
    }

    if(url == 'olddeployments'){
        let old =`
        <input type="submit" class="submit-btn" style="background-color: grey;" value="Download" id='downloadbtn'>
        <div class="container-xl px-4 mt-4">
            <div style=" padding: 25px; padding-left: 10px; padding-right: 10px; background-color: aliceblue;" >
                <table id="test" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Portal URL</th>
                            <th>Portal Name</th>
                            <th>Deployment Date</th>
                            <th>User Name</th>
                            <th>Old Version</th>
                            <th>New Version</th>
                        </tr>
                    </thead>
                </table>
            </div>   
        </div>`
        $("#table").html(old)
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
                {"data": "date"},
                {"data": "username"},
                {"data": "oldversion"},
                {"data": "version"}
            ]
        });
        $("#downloadbtn").click(()=>{
            let date ={
                'type':'olddeployments',
            };
            $.ajax({
                url: `test3.php`,
                type: 'POST',
                dataType: 'json',
                data: date,
                success: function(data) {
                    var link = document.createElement('a');
                    link.href = data.file;
                    link.click();
                }});
        });
    }

});