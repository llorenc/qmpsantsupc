<?php 
// Copyright (c) 2013 Llorenç Cerdà-Alabern, http://personals.ac.upc.edu/llorenc
// This file is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// Foobar is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero Public License for more details.
// You should have received a copy of the GNU Affero Public License
// along with Foobar.  If not, see <http://www.gnu.org/licenses/>.

$flist = glob("jsdata/??-??/??-??-??_??-??-??-graf.js") ;
sort($flist) ;
$jflist = array();
foreach($flist as $f) {
    if(filesize($f) > 0) {
      $jflist[] = $f ;
    }
}
# print_r($_POST) ;
if(isset($_POST["currnode"])) {
  $currnode = $_POST["currnode"] ;
 } else {
  $currnode = "" ;
 }
if(isset($_GET["node"])) {
  $currnode = $_GET["node"] ;
 }

$num_files = count($jflist) ;
$last = $num_files-1 ;
if(isset($_POST["curridx"])) {
  $curridx = $_POST["curridx"] ;
 } else {
  $curridx = $last ;
 }

if(isset($_GET["cap"]) && ($_GET["cap"] > 0)) {
  $curridx = $_GET["cap"]-1 ;
 } elseif(isset($_POST["tocap"]) && ($_POST["tocap"] > 0)) {
   $curridx = $_POST["tocap"]-1 ;
   }

if(isset($_POST["shift"])) {
  $shift = $_POST["shift"] ;
 } else {
  $shift = ceil($num_files/10) ;
 }

if(isset($_POST['accio_bbb']) || isset($_POST['accio_bbb_x'])) {
  $curridx = 0 ;
 } elseif(isset($_POST['accio_bb']) || isset($_POST['accio_bb_x'])) {
   $curridx -= $shift ;
   } elseif(isset($_POST['accio_b']) || isset($_POST['accio_b_x'])) {
     $curridx -= 1 ;
     } elseif(isset($_POST['accio_f']) || isset($_POST['accio_f_x'])) {
       $curridx += 1 ;
       } elseif(isset($_POST['accio_ff']) || isset($_POST['accio_ff_x'])) {
	 $curridx += $shift ;
	 } elseif(isset($_POST['accio_fff']) || isset($_POST['accio_fff_x'])) {
	   $curridx = $last ;
	   }
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
$(function(){
    $('#popuplink').click(function(){
	$('#popup').show("slow");
      });
    $('#popupclose').click(function(){
	$('#popup').hide("slow");
      });
  });
$(function () {
    var curr = [[$curr_file, FlotMin], [$curr_file, FlotMax]] ;
    var plotcap = $.plot($("#placeholder1"), 
			 [ { label: "bidir", data: FlotBidir}, 
			   { label: "nodes", data: FlotNodes}, 
			   { label: "unidir", data: FlotUnidir}, 
			   curr ], 
			 { legend:{position: "nw"},
			     grid: { hoverable: true, clickable: true}
			 }) ;
    var previousCapturePoint = null;
    $("#placeholder1").bind("plothover", function (event, pos, item) {
	var str = "(" + pos.x.toFixed(2) + ", " + pos.y.toFixed(2) + ")";
    	$("#hoverdata").text(str);
	if (item) {
	  if (previousCapturePoint != item.dataIndex) {	  
	    previousCapturePoint = item.dataIndex;
	    $("#tooltip").remove();
	    var x = item.datapoint[0].toFixed(2),
	      y = item.datapoint[1].toFixed(2);
	    showTooltip(item.pageX, item.pageY, y);
	  }
	} else {
	  $("#tooltip").remove();
	  previousCapturePoint = null;
	}
      });
    $("#placeholder1").bind("plotclick", function (event, pos, item) {
	if(item) {
	  var x = item.datapoint[0].toFixed(2) ;
	  var url = window.location.href + '?cap=' + parseInt(x) ;
	  // window.location.href = url;
	  window.location = window.location.pathname + '?cap=' + parseInt(x) ;
	}
      });
});

function callinfonode(node) {
  var nn = jsSunburst.graph.getByName(node);
  gNodeStyles.onClick(nn, true);
  InfoNode(nn, null, null) ;
}

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
      <form  name="input" action="index.php" method="post">
	<input type="hidden" name="curridx" value="$curridx"/>
        <input type="hidden" name="currnode" value="$currnode"/>
	<div id="container">
	  <div id="left-container">
	    <div id="text-details">
	      <div class="text">
		<h3>qMp Sants-UPC</h3> 
		<table border="0">
		  <tr><td>
                      <input name="accio_bbb" type="image" src="img/arrow-3.gif" 
			     alt="<<<" class="forButton" value="<<<" 
			     style="width:17px;height:10px;vertical-align:middle;"/>
	  	      <input name="accio_bb" type="image" src="img/arrow-2.gif" 
			     alt="<<" class="forButton" value="<<" 
			     style="width:14px;height:10px;vertical-align:middle;"/>
	  	      <input name="accio_b" type="image" src="img/arrow-1.gif" 
			     alt="<" class="forButton" value="<" 
			     style="width:12px;height:10px;vertical-align:middle;"/>
                      <input type="text" name="shift"  class="forButton" value="$shift" size="2"/>
	  	      <input name="accio_f" type="image" src="img/arrow1.gif" 
			     alt=">" class="forButton" value=">" alt=">" 
			     style="width:12px;height:10px;vertical-align:middle;"/>
	  	      <input name="accio_ff" type="image" src="img/arrow2.gif" 
			     alt=">>" class="forButton" value=">>" 
			     style="width:14px;height:10px;vertical-align:middle;"/>
	  	      <input name="accio_fff" type="image" src="img/arrow3.gif" 
			     alt=">>>" class="forButton" value=">>>" 
			     style="width:17px;height:10px;vertical-align:middle;"/>
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
    <li><b><input name="capture" type="submit" value="Capture" class="forButton"/>:</b> 
  <input name="tocap" value="$curr_file" type="text" class="forButton" size="5"/>/$num_files</li>
    <li><b>Date of the capture:</b><br/>${day}-${month}-${year} $h:$m:$s<br/>
	      </li>
	      <li><b>Nodes:</b> $nodes</li>
	      <li><b>Bidir. links:</b> $bidir</li>
	      <li><b>Unidir. links:</b> $unidir<br/>
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
    <div id="popup">
    <div id="content">
    <ul>
      <li>A new <b>capture</b> is added hourly.</li>
      <li><b>Left click</b> to select a node.</li>
      <li><b>Unidirectional</b> links are rendered with thinner lines.</li>
      <li><b>Green nodes</b> advertize a default route.</li>
      <li>Use top left <b>arrows</b> to navigate throught the captures.</li>
      <li>Use the <b>capture</b> button to choose a specific capture.</li>
      <li>Click on the botton <b>capture graph</b> to go to a specific capture.</li>
      <li>In the <b>Graph projection</b> select region to zoom-in, click canvas to zoom-out.</li>
    </ul>
    <input id="popupclose" type="Button" value="Close"/>   
    </div>
    </div>
    <div id="info-container">
       <div class="text">
	  <div style="text-align:left; font-size: 1em;">
          <a id="popuplink" href="#">Usage</a><br/>
	  <a href="download.php?path=$ffile" rel="nofollow">Download graph</a><br/>
          <a href="https://github.com/llorenc/qmpsantsupc">Download code</a><br/>
          <a href="http://guifisants.net/node/30540">About this page</a><br/>
          <a href="https://guifi.net/ca/node/54602">About the network</a><br/>
          <a href="http://guifisants.net">About GuifiSants</a><br/>
          <a href="http://qmp.cat" rel="nofollow">quick Mesh project (qMp)</a><br/>
	    Built by <a href="http://personals.ac.upc.edu/llorenc">Llorenç Cerdà-Alabern</a>
	    using <a href="http://philogb.github.com/jit"> JavaScript InfoVis Toolkit</a>
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
  </div>
    <div id="placeholder2" style="width:300px;height:200px;"></div>
    </div>
    <div id="bottom-container">
    <div id="placeholder1" style="width:800px;height:100px;"></div>
      <div style="text-align:left; vertical-align:text-top; font-size: 0.8em;">
<a rel="license" href="http://www.gnu.org/licenses/agpl.html"><img alt="GNU Affero Public License v3" style="border-width:0; vertical-align:text-top;" src="img/agplv3-88x31.png" /></a>This work is licensed under a <a rel="license" href="http://www.gnu.org/licenses/agpl.html">GNU Affero Public License version 3.0</a>.
    </div>
    </div>
    </div>
</form>
</body>
</html>
EOD;
?>
