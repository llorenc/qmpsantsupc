// qmpsantsupc.js is a modified version of the following example provided with the jit library:
// more info: http://philogb.github.com/jit/static/v20/Jit/Examples/Sunburst/example1.code.html
// Jit library copyright applies to this file.

var labelType, useGradients, nativeTextSupport, animate;

(function() {
    var ua = navigator.userAgent,
    iStuff = ua.match(/iPhone/i) || ua.match(/iPad/i),
    typeOfCanvas = typeof HTMLCanvasElement,
    nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'),
    textSupport = nativeCanvasSupport 
        && (typeof document.createElement('canvas').getContext('2d').fillText == 'function');
    //I'm setting this based on the fact that ExCanvas provides text support for IE
    //and that as of today iPhone/iPad current text support is lame
    labelType = (!nativeCanvasSupport || (textSupport && !iStuff))? 'Native' : 'HTML';
    nativeTextSupport = labelType == 'Native';
    useGradients = nativeCanvasSupport;
    animate = !(iStuff || !nativeCanvasSupport);
})();

var Log = {
    elem: false,
    write: function(text){
	if (!this.elem) 
	    this.elem = document.getElementById('log');
	this.elem.innerHTML = text;
	this.elem.style.left = (500 - this.elem.offsetWidth / 2) + 'px';
    }
};

var loadScriptList = (function loadScript() {
    if (arguments.length === 0)  this.doError();
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    var args = Array.prototype.slice.call(arguments);
    script.src = args.shift();
    if(args.length == 1) {
	var callback = args[0];
    } else {
	var callback = function () {
	    loadScript(args);
	};
    }
    script.onreadystatechange = callback;
    script.onload = callback;
    // fire the loading
    head.appendChild(script);
}) ;

function ClickNode(node) {
    var nStyles = jsSunburst.config.NodeStyles.stylesClick;
    if(!nStyles) return;
    //if the node is selected then unselect it
    if(node.selected) {
	// jsSunburst.toggleStylesOnClick(node, false);
	// delete node.selected;
    } else {
	//unselect all selected nodes...
	jsSunburst.graph.eachNode(function(n) {
	    if(n.selected) {
		for(var s in nStyles) {
		    n.setData(s, n.styles['$' + s], 'end');
		}
		delete n.selected;
	    }
	});
	//select clicked node
	node.selected = true;
	// delete node.hovered;
	// jsSunburst.hoveredNode = false;
    }
}
    
function InfoNode(node, eventInfo, e) {
    if (!node || (node.name == '')) {
	return;
    }
    if(!node.selected) {
        document.getElementById("right-container").style.backgroundImage = "url('img/antenes-mv.jpg')";
        $jit.id('inner-details').innerHTML = "" ;
	document.input.currnode.value = "" ;
	return;
    }
    var capture_number = parseInt(document.getElementById("curridx").value)+1;
    var link_capture = window.location.href.toString().split(window.location.host)[1] ;
    link_capture = link_capture.replace("#", "");
    link_capture = link_capture.split('?')[0] + 
	"?cap=" + capture_number + "&node=" + node.name ;
    var html = "<h3><a href='" + link_capture + "'>" + node.name + "</a></h3>" ;
    var ans = [];
    if(document.input.currnode) {
	    document.input.currnode.value = node.name ;
    }
    node.eachAdjacency(function(adj){
        // if on the same level i.e siblings
        if (adj.nodeTo._depth == node._depth) {
	    var info = "" ;
	    if(adj.data.rtt) {
		info = "<font color='green'>" + adj.data.rtt + "</font>" ;
	    } 
	    info = info + "/" ;
	    if(adj.data.bw) {
		var bw = "<font color='red'>" + adj.data.bw + "</font>" ;
		info = info + bw ;
	    }
	    info = info + "/" ;
	    if(adj.data.channel) {
		var channel = "<font color='" + adj.data.$color + "'>" + adj.data.channel + "</font>" ;
		info = info + channel ;
	    }
	    ans.push(adj.nodeTo.name + " (" + info + ")");
        }
    });
    if(ans.length > 0) {
	html =  html + "<h4>connections <span style='font-size: 70%'>(<font color='green'>RTT[ms]</font>/<font color='red'>Bw[Mbps]</font>/<font color='black'>ch</font>)</span></h4><ol><li>" ;
	html =  html + ans.join("</li><li>") + "</li></ol>" ;
    }
    if(node.data.gw) {
	html = html + "<h4>" + "Default gateway <span style='font-size: 70%'>(<font color='green'>RTT[ms]</font>/<font color='red'>Bw[Mbps]</font>)</span></h4><ul>" ;
	var info ;
	if(node.data.gwrtt) {
	    info = "<font color='green'>" + node.data.gwrtt + "</font>" + "/" ;
	}
	if(node.data.gwbw) {
	    var gwbw = "<font color='red'>" + node.data.gwbw + "</font>" ;
	    if(info) {
		info = info + gwbw ;
	    } else {
		info = "/" + gwbw ;
	    }
	}
	if(info) {
	    html = html + "<li>" + node.data.gw  + " (" + info + ")" + "</li>" ;
	} else {
	    html = html + "<li>" + node.data.gw + "</li>" ;
	}
	html = html + "</ul>";
    }
    if(node.data.gwpath && node.data.gwpath.length > 0) {
	html = html + "<h4>" + "Hops to default gateway</h4><ol>" ;
	for (var i in node.data.gwpath) {
	    html = html + "<li>" + node.data.gwpath[i] + "</li>" ;
	}
	html = html + "</ol>" ;
    }
    if(node.data.community_gw && (node.data.community_gw != node.data.gw)) {
	html = html + "<h4>" + "Community gateway <span style='font-size: 70%'>(<font color='green'>RTT[ms]</font>/<font color='red'>Bw[Mbps]</font>)</span></h4><ul>" ;
	var community_info ;
	if(node.data.community_gwrtt) {
	    community_info = "<font color='green'>" + node.data.community_gwrtt + "</font>" + "/" ;
	}
	if(node.data.community_gwbw) {
	    var community_bw = "<font color='red'>" + node.data.community_gwbw + "</font>" ;
	    if(community_info) {
		community_info = community_info + community_bw ;
	    } else {
		community_info = "/" + community_bw ;
	    }
	}
	if(community_info) {
	    html = html + "<li>" + node.data.community_gw  + " (" + community_info + ")" + "</li>" ;
	} else {
	    html = html + "<li>" + node.data.community_gw + "</li>" ;
	}
	html = html + "</ul>";
	if(node.data.community_gwpath && node.data.community_gwpath.length > 0) {
	    html = html + "<h4>" + "Hops to community gateway</h4><ol>" ;
	    for (var i in node.data.community_gwpath) {
		html = html + "<li>" + node.data.community_gwpath[i] + "</li>" ;
	    }
	    html = html + "</ol>" ;
	}
    }
    if(node.data.internetbw || node.data.internetrtt) {
	html = html + "<h4>" + "Internet test</h4><ul>" ;
	if(node.data.internetrtt) {
	    html =  html + "<li><font color='green'>RTT[ms] " + node.data.internetrtt +
		"</font></li>" ;
	}
	if(node.data.internetbw) {
	    html =  html + "<li><font color='red'>Bw[Mbps] "+ node.data.internetbw +
		"</font></li>" ;
	}
	html = html + "</ul>" ;
    }
    if(node.data.ipv4 && node.data.ipv4.length > 0) {
	html = html + "<h4>" + "ipv4</h4><ul>" ;
	for (var i in node.data.ipv4) {
	    html = html + "<li>" + node.data.ipv4[i] + "</li>" ;
	}
	html = html + "</ul>";
    }
    if(node.data.ipv6gl && node.data.ipv6gl.length > 0) {
	html = html + "<h4>" + "ipv6gl</h4><ul>" ;
	for (var i in node.data.ipv6gl) {
	    html = html + "<li>" + node.data.ipv6gl[i] + "</li>" ;
	}
	html = html + "</ul>";
    }
    if(node.data.ipv6ll && node.data.ipv6ll.length > 0) {
	html = html + "<h4>" + "ipv6ll</h4><ul>" ;
	for (var i in node.data.ipv6ll) {
	    html = html + "<li>" + node.data.ipv6ll[i] + "</li>" ;
	}
	html = html + "</ul>";
    }
    if(node.data.gwguest && node.data.gwguest.length > 0) {
	html = html + "<h4>" + "Nodes using this gw</h4><ol>" ;
	for (var i in node.data.gwguest) {
	    html = html + "<li>" + node.data.gwguest[i] + "</li>" ;
	}
	html = html + "</ol>";
    }
    if(node.data.system) {
	html = html + "<h4>" + "System</h4><ul>" ;
	html = html + "<li>" + node.data.system + "</li>" ;
	html = html + "</ul>";
    }
    if(node.data.qmpversion) {
	html = html + "<h4>" + "qMp version</h4><ul>" ;
	html = html + "<li>" + node.data.qmpversion + "</li>" ;
	html = html + "</ul>";
    }
    if(node.data.date) {
	html = html + "<h4>" + "Capture date</h4><ul>" ;
	html = html + "<li>" + node.data.date + "</li>" ;
	html = html + "</ul>";
    }
    document.getElementById("right-container").style.backgroundImage = "url(jit/css/col2.png)";
    $jit.id('inner-details').innerHTML = html ;
    // var x = { pointsize: "3", FlotData[node.name]} ;
    var x = FlotData[node.name] ;
    function someFunc(ctx, x, y, radius, shadow) 
    {
	ctx.beginPath();
	ctx.arc(x, y, radius * 1.5, 0, shadow ? Math.PI : Math.PI * 2, true);
	ctx.closePath();
	ctx.fillStyle = "#c82124"; //red
	ctx.fill();
    }
    if(x) {
	x.points = { show: true, symbol: someFunc} ;
	x.color = "#c82124"; //red
    }
    if(ZoomRanges) {
	var d = getFlotData(ZoomRanges.xaxis.from, ZoomRanges.yaxis.from, ZoomRanges.xaxis.to ,ZoomRanges.yaxis.to) ;
	if(x) {
	    d = d.concat(x);
	}
	$.plot($("#placeholder2"), 
	       d,
               $.extend(true, {}, options, {
		   xaxis: { min: ZoomRanges.xaxis.from, max: ZoomRanges.xaxis.to },
		   yaxis: { min: ZoomRanges.yaxis.from, max: ZoomRanges.yaxis.to }
	       }));
    } else {
	if(x) {
	    $.plot($("#placeholder2"), FlotGraph.concat(x), options);
	}
    }
}

var jsSunburst = {} ;
var gNodeStyles = {} ;
function init(){
  jsSunburst = new $jit.Sunburst({
    //id container for the visualization
    injectInto: 'infovis',
    //Change node and edge styles such as
    //color, width, lineWidth and edge types
    Node: {
      overridable: true,
      type: useGradients? 'gradient-multipie' : 'multipie'
    },
    Edge: {
      overridable: true,
      type: 'hyperline',
      lineWidth: 2,
      color: '#777'
    },
    //Draw canvas text. Can also be
    //'HTML' or 'SVG' to draw DOM labels
    Label: {
      type: nativeTextSupport? 'Native' : 'SVG'
    },
    //Add animations when hovering and clicking nodes
    NodeStyles: {
      enable: true,
      type: 'Native',
      stylesClick: {
        'color': '#33dddd'
      },
      stylesHover: {
        'color': '#dd3333'
      },
      duration: 700
    },
    Events: {
      enable: true,
      type: 'Native',
      //List node connections onClick
      onClick: function(node, eventInfo, e) {
	  InfoNode(node, eventInfo, e) ;
      }
    },
    levelDistance: 190,
    // Only used when Label type is 'HTML' or 'SVG'
    // Add text to the labels. 
    // This method is only triggered on label creation
    onCreateLabel: function(domElement, node){
      var labels = jsSunburst.config.Label.type;
      if (labels === 'HTML') {
        domElement.innerHTML = node.name;
      } else if (labels === 'SVG') {
        domElement.firstChild.appendChild(document.createTextNode(node.name));
      }
    },
    // Only used when Label type is 'HTML' or 'SVG'
    // Change node styles when labels are placed
    // or moved.
    onPlaceLabel: function(domElement, node){
      var labels = jsSunburst.config.Label.type;
      if (labels === 'SVG') {
        var fch = domElement.firstChild;
        var style = fch.style;
        style.display = '';
        style.cursor = 'pointer';
        style.fontSize = "0.8em";
        fch.setAttribute('fill', "#fff");
      } else if (labels === 'HTML') {
        var style = domElement.style;
        style.display = '';
        style.cursor = 'pointer';
        if (node._depth <= 1) {
          style.fontSize = "0.8em";
          style.color = "#ddd";
        } 
        var left = parseInt(style.left);
        var w = domElement.offsetWidth;
        style.left = (left - w / 2) + 'px';
      }
    }
  });
  // load JSON data.
  jsSunburst.loadJSON(json);
  // compute positions and plot.
  jsSunburst.refresh();
  // Show info of the current selected node.
  if(document.input.currnode.value.length > 0) {
      callinfonode(document.input.currnode.value) ;
  }
  //end
}
