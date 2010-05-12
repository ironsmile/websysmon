<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script type="text/javascript" src="prototype.js"></script>
    <script type="text/javascript">

        var POLL_INTERVAL = 500; // in ms
        var STEP = 5; // in px
        var CANVAS_WIDTH = 600;
        var CANVAS_HEIGHT = 150;
        
        var cpu_data = [];
        var mem_data = [];
        
        function draw_chart(data, where){
            var canvas = document.getElementById(where);
            canvas.height = CANVAS_HEIGHT;
            canvas.width = CANVAS_WIDTH;
            
            var draw = canvas.getContext('2d');
            draw.fillStyle = "rgb(255,255,255)";
            draw.fillRect(0, 0, canvas.width, canvas.height);
            draw.fillStyle = "rgb(255,0,0)";
            draw.strokeRect(0, 0, canvas.width, canvas.height);
            draw.fillStyle = "rgb(0,255,0)";
            
            if(data.length < 2) return;
            for(var i=1; i < data.length; i++){
                draw.beginPath();
                draw.moveTo((i-1)*STEP, ((100-data[i-1])/100.0)*canvas.height);
                draw.lineTo(i*STEP, ((100-data[i])/100.0)*canvas.height);
                draw.stroke();
            }
        }

        function update_info(){
            new Ajax.Request('/st/info.php', {
                method: 'get',
                onSuccess: function(transport) {
                    var info = eval('('+transport.responseText+')');
                    var max_length = Math.ceil(CANVAS_WIDTH / STEP)+1;
                    
                    // cpu stuff
                    cpu_data[cpu_data.length] = info['cpu'];
                    if(cpu_data.length > max_length){
                        cpu_data = cpu_data.slice(cpu_data.length - max_length);
                    }
                    $('cpu_load').update(info['cpu']);
                    draw_chart(cpu_data, 'cpu_canvas');

                    // mem stuff
                    info.mem_total = parseInt(info.mem_total, 10);
                    info.mem_used = parseInt(info.mem_used, 10);

                    var cpu_prc = (info.mem_used / info.mem_total) * 100;
                    mem_data[mem_data.length] = cpu_prc;
                    if(mem_data.length > max_length){
                        mem_data = mem_data.slice(mem_data.length - max_length);
                    }
                    draw_chart(mem_data, 'mem_canvas');
                    $('mem_used').update(info.mem_used+' MB ('+parseInt(cpu_prc)+' %) of '+info.mem_total+' MB');
                    
                    // load stuff
                    $('load_avg').update(info['load_avg']);
                    
                    // continue
                    setTimeout("update_info()", POLL_INTERVAL);
                }
            });
        }
        
        document.observe("dom:loaded", function() {
            update_info();
        });
        
    </script>

    <style type="text/css">
        canvas{
            display:block;
        }

        #cpu_canvas{
            margin-bottom: 20px;
        }

        #mem_info, #cpu_info{
            float: left;
        }

        #mem_info{
            margin-left: 30px;
        }

        .klear{
            clear: both;
        }
    </style>
</head>
<body>


<div id="cpu_info">
    <strong>CPU:</strong> <span id="cpu_load"></span>%
    <canvas id="cpu_canvas" width="600" height="150">No canvas support</canvas>
</div>

<div id="mem_info">
    <strong>Mem</strong> <span id="mem_used"></span>
    <canvas id="mem_canvas" width="600" height="150">No canvas support</canvas>
</div>

<div class="klear"></div>

<strong>LoadAvg:</strong> <span id="load_avg"><?= implode(", ", sys_getloadavg()) ?></span>

<!--<h3>Similarity Tool Controls</h3>

<input type="button" value="Start" />
<input type="button" value="Stop" />-->
</body>
</html>