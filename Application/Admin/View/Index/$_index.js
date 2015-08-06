$(function() {
    if (!$('body').hasClass("body-index")) {
        return false;
    }

    var $universal = $('#universal').data('universal');


    // //用户增长曲线图
    // var mychart = new Chart($("#mychart").get(0).getContext("2d")).Line({
    //     labels: {
    //         $universal.user_reg_date
    //     },
    //     datasets: [{
    //         fillColor: "#337ab7",
    //         strokeColor: "#337ab7",
    //         pointColor: "#337ab7",
    //         pointStrokeColor: "#fff",
    //         data: {
    //             $universal.user_reg_count,
    //         }
    //     }]
    // }, {
    //     scaleLineColor: "rgba(0,0,0,.1)", //X/Y轴的颜色
    //     scaleLineWidth: 1 //X/Y轴的宽度
    // });

    // //图表时间段选择
    // $('#daterange_set').daterangepicker({
    //         startDate: moment().subtract(29, 'days'),
    //         endDate: moment(),
    //         minDate: '01/01/2015',
    //         maxDate: '12/31/2100',
    //         dateLimit: {
    //             days: 360
    //         },
    //         showDropdowns: true,
    //         showWeekNumbers: true,
    //         timePicker: false,
    //         timePickerIncrement: 1,
    //         timePicker12Hour: true,
    //         ranges: {
    //             '最近7天': [moment().subtract(6, 'days'), moment()],
    //             '这个月': [moment().startOf('month'), moment().endOf('month')],
    //             '上个月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    //         },
    //         opens: 'left',
    //         buttonClasses: ['btn btn-default'],
    //         applyClass: 'btn-sm btn-primary',
    //         cancelClass: 'btn-sm',
    //         format: 'MM/DD/YYYY',
    //         separator: ' to ',
    //         locale: {
    //             applyLabel: '确定',
    //             cancelLabel: '取消',
    //             fromLabel: '开始',
    //             toLabel: '结束',
    //             customRangeLabel: '自定义',
    //             daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
    //             monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
    //             firstDay: 1
    //         }
    //     },

    //     function(start, end, label) {
    //         var url = $universal.index_url;
    //         var query = 'start_date=' + start + '&end_date=' + end;
    //         if (url.indexOf('?') > 0) {
    //             url += '&' + query;
    //         } else {
    //             url += '?' + query;
    //         }
    //         window.location.href = url;
    //     }
    // );

    //检测更新
    $.get($universal.check_version_url).success(function(data) {
        if (data.status == 1) {
            $('.update').html(data.info);
        } else {
            alertMessager(data.info, 'danger');
        }
    });

});