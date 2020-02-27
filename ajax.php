<?php

require 'config.php';
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

ob_start();

$retorno = array();
$retorno['ok'] = false;

$f = get('f');
$conn = new Connection();
$sql = "SELECT count(table_name) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'infochar'";
if($conn->prepareStatement($sql)->executeScalar() == 0){
    echo '<div class="col-sm-10 col-sm-offset-1 alert alert-danger"> 
            <strong>ERRO</strong><br>
            Servidor n&atilde;o preparado para o Ranking. Instale o LiveRanking.
        </div>';
}else{ 
    switch ($f) {
    	case 'list-ranking' :
                $nick = get('n');
                $gender = get('g');
                $page = get('p')+0;
                $per_page = $RegistrosPorPagina;
                $page = ($page > 1 ? $page : 1);
                $first = ($page-1)*$per_page;

                $paramAdd = '1';
                if($nick != '') $paramAdd .= " AND name like '%{$nick}%'";
                if($gender != '') $paramAdd .= " AND gender = ".($gender+0);

                $sql = "SELECT count(roleid) FROM `infochar` WHERE {$paramAdd}";
                $total = $conn->prepareStatement($sql)->executeScalar();
                $pages = ($total > 0 ? ceil($total/$per_page) : 0);

                $sql = "SELECT roleid, name, gender, level, pvp_kills, pvp_deads, (pvp_kills*{$PontosGanhosPorMatar} - pvp_deads*{$PontosPerdidosPorMorrer}) points FROM `infochar` WHERE {$paramAdd} ORDER BY 7 DESC, pvp_kills DESC, pvp_deads ASC, level DESC LIMIT {$first},{$per_page}";
                $rs = $conn->prepareStatement($sql)->executeReader();

                echo '
                  <table cellpadding="0" cellspacing="0" class="table ranking">
                  <thead>
                      <tr>
                          <th>&nbsp;</th>
                          <th class="text-left">Nick</th>
                          <th class="text-center">Classe</th>
                          <th class="text-center">Level</th>
                          <th class="text-center">Kills</th>
                          <th class="text-center">Deads</th>
                          <th class="text-center">Pontos</th>
                          <th class="text-center">Detalhes</th>
                      </tr>
                    </thead>
                    <tbody>
                ';

                if($rs->numRows() > 0){
                    $i = $first;
                    while($rs->next()){
                        $i++;
                        echo '
                        <tr'.($page == 1 && $nick == '' ?' class="fpage"':'').'>
                          <td class="text-center">'.($i<=3 && $nick == ''?'<span class="glyphicon glyphicon-star"></span>':$i).'</td>
                          <td class="text-left">'.utf8_encode($rs->getString('name')).'</td>
                          <td class="text-center"><a href="javascript:;" onclick="$(\'#rk_gender\').val('.$rs->getInt('gender').'); listRanking(\'\', \''.$rs->getInt('gender').'\', 1);">'.$arrClass[$rs->getInt('gender')].'</a></td>
                          <td class="text-center">'.$rs->getInt('level').'</td>
                          <td class="text-center">'.$rs->getInt('pvp_kills').'</td>
                          <td class="text-center">'.$rs->getInt('pvp_deads').'</td>
                          <td class="text-center">'.$rs->getInt('points').'</td>
                          <td class="text-center"><a href="javascript:void(0);" title="Estat&iacute;sticas detalhadas" onclick="detailPlayerRanking('.$rs->getInt('roleid').');"><span class="glyphicon glyphicon-stats"></span></a></td>
                        </tr>
                      ';
                     }
                }else{
                    echo '
                        <tr>
                            <td colspan="8" class="text-center">Nenhum resultado encontrado.</td>
                        </tr>
                      ';
                }
                echo '</tbody></table>';

                if($pages > 1){
                    echo '<div class="text-center" style="margin-top:20px">';
                    $inicio = $page - 2;
                    $fim = $page + 2;
                    if($inicio > 1){
                      echo '<button title="Primeira p&aacute;gina" type="button" onclick="listRanking(\''.$nick.'\', \''.$gender.'\', 1);"" class="btn btn-default btn-md"><span class="glyphicon glyphicon-step-backward"></span></button>';
                    }
                    if ($inicio < 1) {
                        $fim += ($inicio * -1);
                        $inicio = 1;
                    }
                    if ($fim > $pages) {
                        $fim = $pages;
                    }
                    for ($i = $inicio; $i <= $fim; $i++) {
                        echo '&nbsp;<button type="button"  onclick="listRanking(\''.$nick.'\', \''.$gender.'\', '.$i.');" class="btn btn-default btn-md'.($i==$page?' active':'').'">'.$i.'</button>';
                    } 
                    if($pages > $fim ){
                      echo '&nbsp;<button title="&Uacute;ltima p&aacute;gina" type="button" onclick="listRanking(\''.$nick.'\', \''.$gender.'\', '.$pages.');" class="btn btn-default btn-md"><span class="glyphicon glyphicon-step-forward"></span></button>';
                    }
                    echo '</div>';
                }

                break;

            case 'detail-ranking' :
                $id = get('id')+0;
            
            
                $sql = "SELECT roleid, name, gender, level, pvp_kills, pvp_deads, (pvp_kills*{$PontosGanhosPorMatar} - pvp_deads*{$PontosPerdidosPorMorrer}) points FROM `infochar` WHERE roleid = {$id}";
                $rs = $conn->prepareStatement($sql)->executeReader();
                if($rs->next()){
                    echo '<h4>Personagem: '.utf8_encode($rs->getString('name')).' ('.$arrClass[$rs->getInt('gender')].' Lv. '.$rs->getInt('level').')</h4>';
                    echo '<div class="row">';
                    echo '<div class="col-sm-6" id="pie1" style="height: 500px;"></div><div class="col-sm-6" id="pie2" style="height: 500px;"></div>';
                    echo '<div class="col-sm-6"><h4>Quem mais matou</h4></div>';
                    echo '<div class="col-sm-6"><h4>Pra quem mais morreu</h4></div>';
                    echo '<div class="col-sm-6" style="height: 500px; overflow-y: scroll">';
                    $sql = "SELECT c.name, c.gender, c.level, count(k.morto) qtd FROM kills k INNER JOIN infochar c ON k.morto = c.roleid WHERE k.matou = {$id} GROUP BY c.name, c.gender, c.level ORDER BY 4 DESC LIMIT 0,30";
                    $rsK = $conn->prepareStatement($sql)->executeReader();  
                    echo  '<table cellpadding="0" cellspacing="0" class="table"><thead><tr><th class="text-center">Vezes</th><th class="text-left">Nick</th></tr></thead><tbody>';            
                    if($rsK->numRows()){
                        while($rsK->next()){
                            echo '<tr><td class="text-center small">'.$rsK->getInt('qtd').'</td><td class="text-left small">'.utf8_encode($rsK->getString('name')).' ('.$arrClass[$rsK->getInt('gender')].' Lv. '.$rsK->getInt('level').')</td></tr>';
                        } 
                    }else{
                        echo '<tr><td colspan="2" class="text-center">Nenhum resultado encontrado.</td></tr>';
                    }
                    echo '</tbody></table></div>';

                    echo '<div class="col-sm-6" style="height: 500px; overflow-y: scroll">';
                    $sql = "SELECT c.name, c.gender, c.level, count(k.matou) qtd FROM kills k INNER JOIN infochar c ON k.matou = c.roleid WHERE k.morto = {$id} GROUP BY c.name, c.gender, c.level ORDER BY 4 DESC LIMIT 0,30";
                    $rsK = $conn->prepareStatement($sql)->executeReader();  
                    echo  '<table cellpadding="0" cellspacing="0" class="table"><thead><tr><th class="text-center">Vezes</th><th class="text-left">Nick</th></tr></thead><tbody>';            
                    if($rsK->numRows()){
                        while($rsK->next()){
                            echo '<tr><td class="text-center small">'.$rsK->getInt('qtd').'</td><td class="text-left small">'.utf8_encode($rsK->getString('name')).' ('.$arrClass[$rsK->getInt('gender')].' Lv. '.$rsK->getInt('level').')</td></tr>';
                        }
                    }else{
                        echo '<tr><td colspan="3" class="text-center">Nenhum resultado encontrado.</td></tr>';
                    }
                    echo '</tbody></table></div>';
                    echo '</div>';
                    $sql = "SELECT c.gender, count(k.morto) qtd FROM kills k INNER JOIN infochar c ON k.morto = c.roleid WHERE k.matou = {$id} GROUP BY c.gender";
                    $rsK = $conn->prepareStatement($sql)->executeReader();
                    $pie1 = "[ ['Classe', 'Vitorias'],";
                    while($rsK->next()){
                        $pie1 .= "['".html_entity_decode($arrClass[$rsK->getInt('gender')],ENT_QUOTES,'ISO-8859-1')."', {$rsK->getInt('qtd')}],";
                    }
                    $pie1 .= ']';

                    $sql = "SELECT c.gender, count(k.matou) qtd FROM kills k INNER JOIN infochar c ON k.matou = c.roleid WHERE k.morto = {$id} GROUP BY c.gender";
                    $rsK = $conn->prepareStatement($sql)->executeReader();
                    $pie2 = "[ ['Classe', 'Derrotas'],";
                    while($rsK->next()){
                        $pie2 .= "['".html_entity_decode($arrClass[$rsK->getInt('gender')],ENT_QUOTES,'ISO-8859-1')."', {$rsK->getInt('qtd')}],";
                    }
                    $pie2 .= ']';

                    $retorno['dp1'] = utf8_encode($pie1);
                    $retorno['dp2'] = utf8_encode($pie2);
                    $retorno['ok'] = true;

                }else{
                    echo '<p>Personagem n&atilde;o localizado.</p>';
                }

                break;

    	default:
    		
    		break;
    }
}

$retorno['data'] = ob_get_contents();
ob_end_clean();
echo json_encode($retorno);

