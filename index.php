<?php 
$flist = glob("jsdata/??-??-??_??-??-??-graf.js") ;
sort($flist) ;
$jflist = array();
foreach($flist as $f) {
    if(filesize($f) > 0) {
      $jflist[] = $f ;
    }
}
$num_files = count($jflist) ;
if(!empty($_POST["accio"])) {
  $accio = $_POST["accio"] ;
  $curridx = $_POST["curridx"] ;
  $shift = $_POST["shift"] ;
 } else {
  $accio = "" ;
  $curridx = 0 ;
  $shift = ceil($num_files/10) ;
 }
if(empty($_POST["shift"])) {
  $shift = ceil($num_files/10) ;
 } else {
  $shift = $_POST["shift"] ;
 }
$last = $num_files-1 ;
switch($accio) {
 case '<<<': 
   $curridx = 0 ;
   break;
 case "<<": 
   $curridx -= $shift ;
   break;
 case "<": 
   $curridx -= 1 ;
   break;
 case ">": 
   $curridx += 1 ;
   break;
 case ">>": 
   $curridx += $shift ;
   break;
 default: 
   $curridx = $last ;
 } ;
if($curridx < 0) {
  $curridx = 0 ;
 } elseif($curridx > $last) {
   $curridx = $last ;
   }
$curr_file = $curridx+1 ;
$ffile = $jflist[$curridx] ;
$pattern = '/(\d\d)-(\d\d)-(\d\d)_(\d\d)-(\d\d)-(\d\d)-graf.js/' ;
preg_match($pattern, $ffile, $matches) ;
$year = $matches[1] ;
$month = $matches[2] ;
$day = $matches[3] ;
$h = $matches[4] ;
$m = $matches[5] ;
$s = $matches[6] ;
$pats = "-e 's/^var json =/{ \"JSgraph\" :/' -e 's/null/\"null\"/' -e '$ s/]$/] }/'" ;
$handle = popen("cat $ffile | sed $pats", 'r') ;
$fj = "" ;
while (!feof($handle)) {
  $fj .= fread($handle, 1024);
}
fclose($handle);
  echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>qMp Sants-UPC</title>
    <link type="text/css" href="jit/css/base.css" rel="stylesheet" />
    <link type="text/css" href="jit/css/Sunburst.css" rel="stylesheet" />
    <!--[if IE]><script language="javascript" type="text/javascript"
        src="jit/Extras/excanvas.js"></script><![endif]-->
    <!-- JIT Library File -->
    <script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.selection.js"></script>
    <script language="javascript" type="text/javascript" src="jit/jit.js"></script>
    <script language="javascript" type="text/javascript" src="jit/qmpsantsupc.js"></script>
<script type="text/javascript">
function downloadURL(url) {
    var hiddenIFrameID = 'hiddenDownloader',
        iframe = document.getElementById(hiddenIFrameID);
    if (iframe === null) {
        iframe = document.createElement('iframe');
        iframe.id = hiddenIFrameID;
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
    }
    iframe.src = url;
};
EOD;
readfile("flot_data.js") ;

$gflot = str_replace(".js", ".flot", $ffile) ;

if(file_exists($gflot)) {
  readfile($gflot) ;
 } else {
  system("./build-flot-data.pl -g $ffile -v 'flotdata,flotgraph' -") ;
 }
echo <<<EOD
$(function () {
    var curr = [[$curr_file, FlotMin], [$curr_file, FlotMax]] ;
    $.plot($("#placeholder1"), [ { label: "bidir", data: FlotBidir}, 
			       { label: "nodes", data: FlotNodes}, 
			       { label: "unidir", data: FlotUnidir}, 
			       curr ], {legend:{position: "nw"} });
});

var options = {
 legend: { show: false },
 grid: {
  hoverable: true,
  clickable: true
 },
 series: {
  lines: { show: true },
  points: { show: true },
   color: 0
 },
 selection: { mode: "xy" }
};

var ZoomRanges = null ;
function getFlotData(x1, y1, x2, y2) {
  var d = [];
  for (var i = 0; i < FlotGraph.length; ++i) {
    if (FlotGraph[i].data.length == 1) {
      if(FlotGraph[i].data[0][0] >= x1 && FlotGraph[i].data[0][1] >= y1 &&
	 FlotGraph[i].data[0][0] <= x2 && FlotGraph[i].data[0][1] <= y2) {
	d.push(FlotGraph[i]);
      }
    } else {
      if(FlotGraph[i].data[0][0] >= x1 && FlotGraph[i].data[0][1] >= y1 &&
	 FlotGraph[i].data[0][0] <= x2 && FlotGraph[i].data[0][1] <= y2 && 
	 FlotGraph[i].data[1][0] >= x1 && FlotGraph[i].data[1][1] >= y1 &&
	 FlotGraph[i].data[1][0] <= x2 && FlotGraph[i].data[1][1] <= y2) {
	d.push(FlotGraph[i]);
      }
    }
  }
  return d ;
}

$(function () {
    var ZoomActive = false ;
    var plot = $.plot($("#placeholder2"), FlotGraph, options);
    $("#placeholder2").bind("plotselected", function (event, ranges) {
        // clamp the zooming to prevent eternal zoom
        if (ranges.xaxis.to - ranges.xaxis.from < 0.00001)
	  ranges.xaxis.to = ranges.xaxis.from + 0.00001;
        if (ranges.yaxis.to - ranges.yaxis.from < 0.00001)
	  ranges.yaxis.to = ranges.yaxis.from + 0.00001;
        // do the zooming
	ZoomRanges = ranges ;
        plot = $.plot($("#placeholder2"), 
		      getFlotData(ranges.xaxis.from, ranges.yaxis.from, ranges.xaxis.to ,ranges.yaxis.to),
                      $.extend(true, {}, options, {
			xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to },
					     yaxis: { min: ranges.yaxis.from, max: ranges.yaxis.to }
			}));
	ZoomActive = true ;
      });
    function showTooltip(x, y, contents) {
      $("<div id='tooltip'>" + contents + "</div>").css({
    	position: "absolute",
    	    display: "none",
    	    top: y + 5,
    	    left: x + 5,
    	    border: "1px solid #fdd",
    // 	    padding: "2px",
    	    "background-color": "#fee",
    // 	    opacity: 0.80
    	    }).appendTo("body").fadeIn(200);
    }
    var previousPoint = null;
    $("#placeholder2").bind("plothover", function (event, pos, item) {
	var str = "(" + pos.x.toFixed(2) + ", " + pos.y.toFixed(2) + ")";
    	$("#hoverdata").text(str);
	if (item) {
	  if (previousPoint != item.dataIndex) {	  
	    previousPoint = item.dataIndex;
	    $("#tooltip").remove();
	    var x = item.datapoint[0].toFixed(2),
	      y = item.datapoint[1].toFixed(2);
	    showTooltip(item.pageX, item.pageY, item.series.label);
	    var nn = jsSunburst.graph.getByName(item.series.label);
	    gNodeStyles.hoverNode(nn);
	  }
	} else {
	  $("#tooltip").remove();
	  previousPoint = null;
	}
      });
    $("#placeholder2").bind("plotclick", function (event, pos, item) {
	if(item) {
    	  var nn = jsSunburst.graph.getByName(item.series.label);
	  gNodeStyles.onClick(nn, true);
    	  InfoNode(nn, null, null) ;
	} else {
	  if(ZoomActive) {
	    ZoomActive = false ;
	  } else {
	    ZoomRanges = null ;
	    $.plot($("#placeholder2"), FlotGraph, options);
	  }
	}
      });
  });

</script>
  </head>
  <body onload="loadScriptList('$ffile', init);">
    <form  name="choosedate" action="index.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="curridx" value="$curridx"/>
    <div id="container">
      <div id="left-container">
	<div id="text-details">
	  <div class="text">
    <h3>qMp Sants-UPC</h3> 
		<table border="0">
		  <tr><td>
	  	      <input name="accio" type="image" src="img/arrow-3.gif" alt="<<<" class="forButton" value="<<<" style="width:17px;height:10px;vertical-align:middle;"/>
	  	      <input name="accio" type="image" src="img/arrow-2.gif" alt="<<" class="forButton" value="<<" style="width:14px;height:10px;vertical-align:middle;"/>
	  	      <input name="accio" type="image" src="img/arrow-1.gif" alt="<" class="forButton" value="<" style="width:12px;height:10px;vertical-align:middle;"/>
                      <input type="text" name="shift"  class="forButton" value="$shift" size="2"/>
	  	      <input name="accio" type="image" src="img/arrow1.gif" alt=">" class="forButton" value=">" alt=">" style="width:12px;height:10px;vertical-align:middle;"/>
	  	      <input name="accio" type="image" src="img/arrow2.gif" alt=">>" class="forButton" value=">>" style="width:14px;height:10px;vertical-align:middle;"/>
	  	      <input name="accio" type="image" src="img/arrow3.gif" alt=">>>" class="forButton" value=">>>" style="width:17px;height:10px;vertical-align:middle;"/>
		    </td>
		  </tr>
		</table>
EOD;
$json = json_decode($fj) ;
$colors = $json->JSgraph[0]->data->colors ;
$bidir = $json->JSgraph[0]->data->bidir ;
$unidir = $json->JSgraph[0]->data->unidir ;
$nodes = $json->JSgraph[0]->data->nodes ;
//var_dump($val) ;
echo <<<EOD
	    <ul>
    <li><b>Capture:</b> $curr_file/$num_files</li>
    <li><b>Date of the capture:</b><br/>${day}-${month}-${year} $h:$m:$s<br/>
	      </li>
	      <li><b>Nodes:</b> $nodes</li>
	      <li><b>Bidir. links:</b> $bidir</li>
	      <li><b>Unidir. links:</b> $unidir<br/>
		<small>Unidirectional links are rendered with thinner lines.<br/>
	               Green nodes advertize a default route.</small></li>
	      <li><b>Channels</b><p/>
              <table>
EOD;
function col_print($col, $ch) {
 static $icol = 0 ;
  if($icol++ % 2 == 0) { echo "<tr>" ; }
echo <<<EOD
		<td><span style="color:$col;font-size:140%">&#9632;</span>&nbsp;$ch&nbsp;</td>
EOD;
 if($icol % 2 == 0) { echo "</tr>" ; }
}
array_walk($colors, 'col_print');
echo <<<EOD
             </table>
	    </ul>
          </div>
	</div>
    </div>
    <div id="info-container">
       <div class="text">
          <b>Left click</b> to select a node.<br/>
	  <a href="download.php?path=$ffile" rel="nofollow">Download graph</a><br/>
          <a href="http://qmp.cat" rel="nofollow">quick Mesh project (qMp)</a><br/>
          <a href="https://guifi.net/ca/node/54602">About the network</a><p/>
	  <div style="text-align:left; font-size: 0.8em;">
	    Built by <a href="http://personals.ac.upc.edu/llorenc">Llorenç Cerdà-Alabern</a><br/>
	    using <a href="http://philogb.github.com/jit"> JavaScript InfoVis Toolkit</a><br/>
            and  <a href="http://www.flotcharts.org"> Flot</a><br/>
         <table border="0" style="margin-left:0;margin-top:0">
         <tr><td><a href="http://confine-project.eu" style="text-decoration:none;">Supported by CONFINE</a></td>
            <td><a href="http://confine-project.eu" style="text-decoration:none;">
                <img src="img/logo_confine_150.png" alt="logo" height="40" style="border:0;"/></a>
            </td></tr></table><br/>
          </div>
	</div>
      </div>
      <div id="center-container">
	<div id="infovis"></div>
      </div>
      <div id="right-container">
	<div id="inner-details"></div>
      </div>
    <div id="geo-container">
  <div style="text-align:midle; font-size: 0.8em;">
  Graph projection, axis are in km<br/>
  Select region to zoom-in, click canvas to zoom-out.
  </div>
    <div id="placeholder2" style="width:300px;height:200px;"></div>
    </div>
    <div id="bottom-container">
    <div id="placeholder1" style="width:800px;height:100px;"></div>
      <div style="text-align:left; font-size: 0.8em;">
<a rel="license" href="http://creativecommons.org/licenses/by/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/80x15.png" /></a>This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/3.0/deed.en_US">Creative Commons Attribution 3.0 Unported License</a>.
    </div>
    </div>
    </div>
</form>
</body>
</html>
EOD;
?>
