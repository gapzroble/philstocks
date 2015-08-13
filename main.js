jQuery(function($){

    // globals
    var mainId = "intracandle", main = $("#"+mainId),
        sym = $("#txtSym"),
        ohlcData = [], volumeData = [];
    
    // check if symbol is found
    var getSymbol = function() 
    {
        if (sym.size() > 0) {
            return sym.val();
        }
        return false;
    };
    
    var getCurrent = function() {
        var d = new Date(); //new Date(new Date().toLocaleDateString());
        var r = $("#symQuoteDay").html().split(' - ');
        var h = parseFloat(r[1]);
        var l = parseFloat(r[0]);
        var o = parseFloat($("#symQuoteOpen").html());
        var c = parseFloat($("#symQuoteLPrice").html());
        var v = parseFloat($("#symQuoteVolume").html().replace(/,/g, ""));
        return {
            d:d.format('mm/dd/yyyy'), h:h, l:l, o:o, c:c, v:v
        };
    };
    
    var getMA = function(period)
    {
        var data = [];
        for (var i = 0; i < ohlcData.length; i++) {
            if (i < period) {
                data.push(null);
            } else {
                var sum = 0;
                for (var y = 0; y < period; y++){
                    sum += ohlcData[i-y][4];
                }
                data.push(sum/period);
            }
        }

        return data.filter(shrink);
    };
    
    var shrink = function (value, index, arr) {
        var toIndex = arr.length - (20*6); // 6 months
        if (toIndex > 0) {
            return index >= toIndex;
        }
        return true;
    };
    
    var updateChart = function() 
    {
        var showMA = (ohlcData.length > 50);
        
        $('#jqChart').jqChart({
            legend: { visible: false },
            border: { lineWidth: 0, padding: 0 },
            tooltips: { type: 'shared', disabled: true },
            crosshairs: {
                enabled: true,
                hLine: { strokeStyle: '#00FFFF' },
                vLine: { strokeStyle: '#00FFFF' },
                snapToDataPoints: false
            },
            animation: { enabled: false },
            axes: [
                { 
                    type: 'linear', 
                    location: 'left', 
                    width: 30 }
                ,{
                    type: 'dateTime',
                    location: 'bottom',
                    skipEmptyDays: true,
                    labels: { visible : false }
                }
                ,{
                    type: 'category',
                    location: 'bottom',
                    labels: { visible : false },
                    lineWidth: 0,
                    majorTickMarks: { lineWidth: 0 },
                    minorTickMarks: { lineWidth: 0 }
                }
            ],
            series: [
                {
                    title: '',
                    type: 'candlestick',
                    data: ohlcData.filter(shrink),
                    priceUpFillStyle: 'green',
                    priceDownFillStyle: 'red',
                    strokeStyle: 'black'
                }
                ,{
                    title: '',
                    type: 'spline',
                    data: getMA(20),
                    markers: { size: 0 },
                    lineWidth: 1,
                    strokeStyle: '#ff0000',
                    visible: showMA
                }
                ,{
                    title: '',
                    type: 'spline',
                    data: getMA(50),
                    markers: { size: 0 },
                    lineWidth: 1,
                    strokeStyle: '#ff00ff',
                    visible: showMA
                }
                ,{
                    title: '',
                    type: 'spline',
                    data: getMA(100),
                    markers: { size: 0 },
                    lineWidth: 1,
                    strokeStyle: '#0000ff',
                    visible: showMA
                }
            ]
        });

        $('#jqChartVolume').jqChart({
            legend: { visible: false },
            border: { lineWidth: 0, padding: 0 },
            tooltips: { type: 'shared', disabled: true },
            crosshairs: { enabled: true, hLine: false, vLine: { strokeStyle: '#00FFFF' } },
            animation: { enabled: false },
            axes: [
                { 
                    type: 'dateTime', 
                    location: 'bottom', 
                    skipEmptyDays: true 
                },
                {
                    type: 'linear', 
                    location: 'left', 
                    width: 30 
                }
            ],
            series: [{ type: 'column', data: volumeData.filter(shrink), fillStyle: 'black' }]
        });
        
        try {
            var data1 = $('#jqChart').jqChart('option', 'series')[0].data;
            $('#jqChart').jqChart('highlightData', [data1[data1.length-1]]);
            $('#jqChart').jqChart('highlightData', null);
            $('#jqChartVolume').jqChart('highlightData', null);
        } catch(err) {}
    };
    
    var chart = function() 
    {
        var current = getCurrent();
        ohlcData = []; volumeData = [];
        
        $.getJSON("https://localhost/250/"+getSymbol(), current)
            .done(function(data) {
                data.sort(function (a, b) {
                    if (a.Date < b.Date) return -1;
                    if (b.Date < a.Date) return 1;
                    return 0;
                });
                $.each(data, function(k,v) {
                    var d = new Date(v.Date);
                    ohlcData.push([d, v.High, v.Low, v.Open, v.Close]);
                    volumeData.push([d, v.Volume]);
                });
                updateChart();
            });
    };
    
    var install = function() 
    {
        // check and install if missing
        if (main.size() == 0) {
            main = $('<div/>').attr("id", mainId);
            $('.consoleContainer').append(main);
            $('<div class="info"><b>Open: </b><span id="open"></span>' +
                '<b>High: </b><span id="high"></span>' +
                '<b>Low: </b><span id="low"></span>' + 
                '<b>Close: </b><span id="close"></span>' +
                '<b>Volume: </b><span id="volume"></span>' +
                '<span id="date"></span></div>').appendTo(main);
            $('<div id="jqChart"></div>').appendTo(main);
            $('<div id="jqChartVolume"></div>').appendTo(main);
            
            var isHighlighting = false;

            $('#jqChart').bind('dataHighlighting', function (event, data) {
                if (data && typeof data[0] != "undefined" && data[0].open != "undefined") {
                    data = data[0];
                }

                if (!data || typeof data.open == "undefined") {
                    $('#jqChartVolume').jqChart('highlightData', null);
                    return;
                }

                $('#open').html(data.open);
                $('#high').html(data.high);
                $('#low').html(data.low);
                $('#close').html(data.close);

                var date = data.chart.stringFormat(data.x, "mmmm d, yyyy");

                $('#date').html(date);

                if (!isHighlighting) {

                    isHighlighting = true;

                    var index = $.inArray(data.dataItem, ohlcData);
                    $('#jqChartVolume').jqChart('highlightData', [volumeData[index]]);
                }

                isHighlighting = false;
            });

            $('#jqChartVolume').bind('dataHighlighting', function (event, data) {
                if (!data) {
                    $('#jqChart').jqChart('highlightData', null);
                    return;
                }

                $('#volume').html(data.y.toLocaleString());

                if (!isHighlighting) {

                    isHighlighting = true;

                    var index = $.inArray(data.dataItem, volumeData);
                    $('#jqChart').jqChart('highlightData', [ohlcData[index]]);
                }

                isHighlighting = false;
            });

            chart();
        }
    };
    
    var observeSymbol = function()
    {
        var mutationHandler = function (mutationRecords)
        {
            chart();
        };
        
        var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
        var observer = new MutationObserver(mutationHandler);
        var config = {childList: true, characterData: true, attributes: true, subtree: true};
        
        $(".pricesContainer").each (function() {
            observer.observe(this, config);
        });
    };
    
    var run = function() 
    {
        install();
        observeSymbol();
    };
    
    if (getSymbol())
    {
        run();
    }
    
});
