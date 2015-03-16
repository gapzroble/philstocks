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
        var d = new Date(new Date().toLocaleDateString());
        var r = $("#symQuoteDay").html().split(' - ');
        var h = parseFloat(r[1]);
        var l = parseFloat(r[0]);
        var o = parseFloat($("#symQuoteOpen").html());
        var c = parseFloat($("#symQuoteLPrice").html());
        var v = parseFloat($("#symQuoteVolume").html().replace(/,/g, ""));
		return {
			d:d.format('mm/dd/yyyy'), h:h, l:l, o:o, c:c, v:v
		};
        //return [ [d, h, l, o, c], [d, v] ];
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
        var toIndex = arr.length - 50;
        if (toIndex > 0) {
            return index >= toIndex;
        }
        return true;
    };
    
    var updateChart = function() 
    {
        //ohlcData.push(ohlc);
        //volumeData.push(volume);
        
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
            crosshairs: {
                enabled: true,
                hLine: false,
                vLine: { strokeStyle: '#00FFFF' },
            },
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
            //ohlc = current[0], volume = current[1];
        ohlcData = []; volumeData = [];
        
        $.getJSON("https://localhost/150/"+getSymbol(), current)
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
                //updateChart(ohlc, volume);
            })
            /*.fail(function() {
                updateChart(ohlc, volume);
            })*/
			;
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
/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */

var dateFormat = function () {
    var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
        timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
        timezoneClip = /[^-+\dA-Z]/g,
        pad = function (val, len) {
            val = String(val);
            len = len || 2;
            while (val.length < len) val = "0" + val;
            return val;
        };

    // Regexes and supporting functions are cached through closure
    return function (date, mask, utc) {
        var dF = dateFormat;

        // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
        if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
            mask = date;
            date = undefined;
        }

        // Passing date through Date applies Date.parse, if necessary
        date = date ? new Date(date) : new Date;
        if (isNaN(date)) throw SyntaxError("invalid date");

        mask = String(dF.masks[mask] || mask || dF.masks["default"]);

        // Allow setting the utc argument via the mask
        if (mask.slice(0, 4) == "UTC:") {
            mask = mask.slice(4);
            utc = true;
        }

        var _ = utc ? "getUTC" : "get",
            d = date[_ + "Date"](),
            D = date[_ + "Day"](),
            m = date[_ + "Month"](),
            y = date[_ + "FullYear"](),
            H = date[_ + "Hours"](),
            M = date[_ + "Minutes"](),
            s = date[_ + "Seconds"](),
            L = date[_ + "Milliseconds"](),
            o = utc ? 0 : date.getTimezoneOffset(),
            flags = {
                d:    d,
                dd:   pad(d),
                ddd:  dF.i18n.dayNames[D],
                dddd: dF.i18n.dayNames[D + 7],
                m:    m + 1,
                mm:   pad(m + 1),
                mmm:  dF.i18n.monthNames[m],
                mmmm: dF.i18n.monthNames[m + 12],
                yy:   String(y).slice(2),
                yyyy: y,
                h:    H % 12 || 12,
                hh:   pad(H % 12 || 12),
                H:    H,
                HH:   pad(H),
                M:    M,
                MM:   pad(M),
                s:    s,
                ss:   pad(s),
                l:    pad(L, 3),
                L:    pad(L > 99 ? Math.round(L / 10) : L),
                t:    H < 12 ? "a"  : "p",
                tt:   H < 12 ? "am" : "pm",
                T:    H < 12 ? "A"  : "P",
                TT:   H < 12 ? "AM" : "PM",
                Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
            };

        return mask.replace(token, function ($0) {
            return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
        });
    };
}();

// Some common format strings
dateFormat.masks = {
    "default":      "ddd mmm dd yyyy HH:MM:ss",
    shortDate:      "m/d/yy",
    mediumDate:     "mmm d, yyyy",
    longDate:       "mmmm d, yyyy",
    fullDate:       "dddd, mmmm d, yyyy",
    shortTime:      "h:MM TT",
    mediumTime:     "h:MM:ss TT",
    longTime:       "h:MM:ss TT Z",
    isoDate:        "yyyy-mm-dd",
    isoTime:        "HH:MM:ss",
    isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
    isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
    dayNames: [
        "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
        "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
    ],
    monthNames: [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
        "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
    ]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
    return dateFormat(this, mask, utc);
};
