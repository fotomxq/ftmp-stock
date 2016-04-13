/**
 * 中心页面JS
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package center
 */
//历史收益图表
var historyChart = new Object;
//图表操作对象
historyChart.obj = '';
//数据
historyChart.data = [];
//时间轴
historyChart.now = +new Date(1997, 9, 3);
historyChart.oneDay = 24 * 3600 * 1000;
//随机化，注意这是事例，随后需要根据数据删除该部分内容
historyChart.value = Math.random() * 1000;
for (var i = 0; i < 1000; i++) {
    historyChart.data.push(randomData());
}
//初始化图表
historyChart.start = function () {
    $('#history-chart').width($('.container').width());
    historyChart.obj = echarts.init(document.getElementById('history-chart'));
    option = {
        title: {
            text: '收益和沪深指数对比'
        },
        tooltip: {
            trigger: 'axis',
            formatter: function (params) {
                params = params[0];
                var date = new Date(params.name);
                return date.getDate() + '/' + (date.getMonth() + 1) + '/' + date.getFullYear() + ' : ' + params.value[1];
            },
            axisPointer: {
                animation: false
            }
        },
        xAxis: {
            type: 'time',
            splitLine: {
                show: false
            }
        },
        yAxis: {
            type: 'value',
            boundaryGap: [0, '100%'],
            splitLine: {
                show: false
            }
        },
        series: [{
                name: '模拟数据',
                type: 'line',
                showSymbol: false,
                hoverAnimation: false,
                data: historyChart.data
            }]
    };
    historyChart.obj.setOption(option);
};

//随机化数据部分，以后需要删除
historyChart.timeTicket = setInterval(function () {
    for (var i = 0; i < 5; i++) {
        historyChart.data.shift();
        historyChart.data.push(randomData());
    }
    historyChart.obj.setOption({
        series: [{
                data: historyChart.data
            }]
    });
}, 1000);
function randomData() {
    historyChart.now = new Date(+historyChart.now + historyChart.oneDay);
    historyChart.value = historyChart.value + Math.random() * 21 - 10;
    return {
        name: historyChart.now.toString(),
        value: [
            [historyChart.now.getFullYear(), historyChart.now.getMonth() + 1, historyChart.now.getDate()].join('-'),
            Math.round(historyChart.value)
        ]
    }
}

//初始化
$(function () {
    //初始化提示框架
    $('div[name="app-list"]').popup({
        inline: true
    });
    //初始化历史收益情况图表
    historyChart.start();
});

