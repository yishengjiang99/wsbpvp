<?php
    require_once("../functions.php");
    $params = getParams(['timeseries','title']);
    $title=$params['title'];
?>

<html>
<head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</heead>
<body>
  <h3><?= $title ?>
  <div id="chart_div" style='height:300px;width:100%'></div>
</body>
</html>

<script>
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawLineColors);
var tseries =JSON.parse('<?= $params['timeseries'] ?>');
function drawLineColors() {
      var data = new google.visualization.DataTable();
      data.addColumn('number','Time');
      data.addColumn("number", "Y");
      $.each(tseries,function(t,v){
        t=parseFloat(t);
        v=parseFloat(v);
        data.addRow([t,v]);
      });      
      var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
      chart.draw(data)
}
</script>
