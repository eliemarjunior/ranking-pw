function showMessage(title, msg, type, buttons, size){
    var v_size = size || BootstrapDialog.SIZE_NORMAL;
    BootstrapDialog.show({
        type: type,
        title: title,
        message: msg,
        buttons: buttons,
        size: v_size
    });
}

function blockUi(){
    $.blockUI({ message: 'Aguarde...',
        css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
        }
    });
}

function unblockUi(){
    $.unblockUI();
}

function listRanking(nick, gender, page){
    var v_url = './ajax.php?f=list-ranking';
    if(nick != '')              v_url += '&n='+nick;
    if(gender != '')            v_url += '&g='+gender;
    if(parseInt(page)+0 > 0)    v_url += '&p='+parseInt(page);
    blockUi();
    var title = 'Lista de Ranking';
    var type = BootstrapDialog.TYPE_DANGER;
    $.ajax({
        url: v_url,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#txtRanking').html(data['data']);
            unblockUi(); 
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            msg = 'Ocorreu um erro: ' + errorThrown;
            showMessage(title, msg, type, []);
            unblockUi(); 

        }
    });
}


function detailPlayerRanking(id){
    var v_url = './ajax?f=detail-ranking&id='+id;
    var title = 'Detalhe do Jogador';
    var type = BootstrapDialog.TYPE_DEFAULT;
    var ok = false;
    var v_pie1 = v_pie2 = [];
    blockUi();
    $.ajax({
        url: v_url,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            msg = data['data'];
            ok = data['ok'];
            if(data['ok']){
                eval("v_pie1 = "+data['dp1'].replace(/'/g, '"')+";");
                eval("v_pie2 = "+data['dp2'].replace(/'/g, '"')+";");
            }
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            msg = 'Ocorreu um erro: ' + errorThrown;
            type = BootstrapDialog.TYPE_DANGER
        }
    }).done(function(){
        unblockUi(); 
        
        BootstrapDialog.show({
            type: type,
            title: title,
            message: msg,
            buttons: [],
            size: BootstrapDialog.SIZE_WIDE,
            onshown: function(dialogRef){
                if(ok){
                    var chart = new google.visualization.PieChart(document.getElementById('pie1'));
                    chart.draw(google.visualization.arrayToDataTable(v_pie1), {
                      title: 'Raca que mais ganhou',legend: 'none',
                    });
                    var chart2 = new google.visualization.PieChart(document.getElementById('pie2'));
                    chart2.draw(google.visualization.arrayToDataTable(v_pie2), {
                      title: 'Raca que mais perdeu',legend: 'none',
                    });
                }
            },

        });

        
    });
}