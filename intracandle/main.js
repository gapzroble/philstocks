jQuery(function($){

    // globals
    var mainId = "intracandle",
        main = $("#"+mainId),
        sym = $("#txtSym");
    
    // check if symbol is found
    var getSymbol = function() 
    {
        if (sym.size() > 0) {
            return sym.val();
        }
        return false;
    };
    
    var updateChart = function(ohlc, volume) {
        // create the chart
        main.highcharts('StockChart', {
            chart: {
                animation: false
            },
            rangeSelector: {
                enabled: false
            },
            scrollbar: {
                enabled: false
            },
            navigator: {
                enabled: false
            },
            credits: {
                enabled: false
            },
            plotOptions: {
                candlestick: {
                    animation: false
                },
                series: {
                    animation: false
                }
            },
            title: {
                text: ''//getSymbol()
            },
            yAxis: [{
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: ''
                },
                height: '60%',
                lineWidth: 2
            }, {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: ''
                },
                top: '65%',
                height: '35%',
                offset: 0,
                lineWidth: 2
            }],

            series: [{
                type: 'candlestick',
                name: getSymbol()+'',
                data: ohlc
            }, {
                type: 'column',
                name: 'Volume',
                data: volume,
                yAxis: 1
            }]
        });
    };
    
    var chart = function() 
    {
        var d = new Date(new Date().toLocaleDateString()).valueOf();
        var r = $("#symQuoteDay").html().split(' - ');
        var h = parseFloat(r[1]);
        var l = parseFloat(r[0]);
        var o = parseFloat($("#symQuoteOpen").html());
        var c = parseFloat($("#symQuoteLPrice").html());
        var v = parseFloat($("#symQuoteVolume").html().replace(/,/g, ""));
        
        var ohlc = [[d, o, h, l, c]],
            volume = [[d, v]];
        try {
            updateChart(ohlc, volume);
        } catch(error) {}
    };
    
    var install = function() 
    {
        // check and install if missing
        if (main.size() == 0) {
            main = $('<div/>').attr("id", mainId);
            $('.consoleContainer').append(main);
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
        
        $("#symQuoteLPrice").each (function() {
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
