<? require 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title><?=$GLOBALS['NomeServidor']?> - Ranking</title>
        <meta charset="iso-8859-1">
        <meta http-equiv="content-language" content="pt-br" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="keywords" content="" />
        <meta name="description" content="" />
        <meta name="author" content="Flexpoint Sistemas Flexiveis" />
        <meta name="reply-to" content="contato@flexpoint.com.br" />
        <meta name="robots" content="index,follow" />
        <meta name="verify-v1" content="" />
    
        <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/css/bootstrap-dialog.min.css" rel="stylesheet" type="text/css" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/js/bootstrap-dialog.min.js"></script>
		<script src="./assets/jquery.blockUI.js"></script>
		
		<link href="./assets/style.css" rel="stylesheet" type="text/css" />
		<script src="./assets/rank.js"></script>
		
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>google.charts.load('current', {'packages':['corechart']});</script>

	</head>
    <body class="home">
     	 <div class="header"></div>
	<section>    
		
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1 alert alert-success"><p>Avisos de sucesso, remova se quiser</p></div> 
		<div class="col-sm-10 col-sm-offset-1 alert alert-warning"><p>Avisos de alerta, remova se quiser</p></div> 
		<div class="col-sm-10 col-sm-offset-1 alert alert-danger"><p>Avisos de erro, remova se quiser</p></div> 
	</div>
	<div class="ranking-group group wrapper">

	  <form method="post" onsubmit="listRanking($('#rk_name').val(), $('#rk_gender').val(), 1); return false;" class="ranking-form">
	    <div class="container">
	      <div class="row">
	      	<div class="col-sm-6">
	        	<input type="text" id="rk_name" class="form-control" placeholder="Busque pelo nick" />
	      	</div>
	      	<div class="col-sm-4">
	        	<select id="rk_gender" class="form-control">
	        		<option value="">Todas as Classes</option>
	        		<?php foreach($arrClass as $key=>$value) { ?>
	        			<option value="<?=$key?>"><?=$value?></option>
	        		<?php } ?>
	        	</select>		
	      	</div>
	      	<div class="col-sm-2">
	        	<button type="submit" class="btn btn-circle btn-block btn-primary">Buscar</button>
	      	</div>
	      </div>
	    </div>
	  </form>
	          
	  <div class="ranking-result-group">
	    <div class="container">
	      <div id="txtRanking">
	        Aguarde, buscando dados...
	      </div>
	      </table>
	    </div>
	  </div>

	</div>

	</section>  

    <script type="text/javascript">
		$(function () {
			listRanking('', 0, 1);
		});
	</script>  
    </body>
</html>



