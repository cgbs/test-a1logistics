jQuery(function($){
    $('.datepicker').pikaday({
        firstDay: 1,
        format: 'YYYY-MM-DD',
        yearRange: [1997,2017],
        i18n: {
            previousMonth : 'Пред. месяц',
            nextMonth     : 'След. месяц',
            months        : ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            weekdays      : ['Воскресение','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'],
            weekdaysShort : ['Вс','Пн','Вт','Ср','Чт','Пт','Сб']
        }
    });
    $('.multi-select').multiselect({numberDisplayed: 8})
    var query_ajax_index = 0;
    var query_ajax_data = [];
    function make_ajax_query_start(actionURL){  //функция последовательного запуска цепочки асинхронных ajax запросов, чтобы не вешать интерфейс, но сохранить последовательность
        console.log(query_ajax_index+' : '+query_ajax_data.length);
        if(query_ajax_index<query_ajax_data.length){
            console.log('Loading date from '+query_ajax_data[query_ajax_index].date_from+' to '+query_ajax_data[query_ajax_index].date_to);
            $('.loader-info-text').html('Загрузка данных в диапазоне '+query_ajax_data[query_ajax_index].date_from+' - '+query_ajax_data[query_ajax_index].date_to);
            $.ajax({
                async: true,
                type: "POST",
                url: actionURL,
                data: query_ajax_data[query_ajax_index],
                success: function (response) {
                    console.log('success loadded');
                    query_ajax_index++;
                    make_ajax_query_start(actionURL);
                }
            });
        }else{
            window.location = '/';
        }
    }
    $('.form-loader').bind('submit',function(e){
        e.preventDefault();
        $('.loader-cover').show();
        var form = $(this);
        //Формируем пакетные запросы по месяцам чтобы не вешать сервер
        var date_format = 'YYYY-MM-DD';
        var start = new Date(form.find('input[name="date_from"]').val());
        var end = new Date(form.find('input[name="date_to"]').val());
        var current = start;
        var periods = [];
        while(current < end){

            periods.push(moment(current).format(date_format));
            current = moment(current).add(1,'months');
        }
        periods.push(moment(end).format(date_format));
        for(var i= 0; i<periods.length-1; i++){
            var period_start = periods[i];
            var period_end = periods[i+1];
            query_ajax_data.push({  //добавление периодов по месяцу для старта загрузки в цепочке ajax
                'date_from' :   period_start,
                'date_to'   :   period_end,
                '_token'    :   form.find('input[name="_token"]').val()
            });
        }
        make_ajax_query_start(form.attr('action'));
    });
    $('form.chart-constructor').bind('submit',function(e){  //сбор данных с формы и отправка запроса на сервер
        e.preventDefault();
        var actionURL = $(this).attr('action');
        var post_data = {};
        $(this).find('input[type="text"], input[type="hidden"],select').each(function(index,el){
            if($(el).is('select')){
                if($(el).attr("multiple")){
                    var vals = [];
                    $(el).find('option:selected').each(function(index,value){
                        vals.push($(value).val());
                    });
                    post_data[$(el).attr('name')] = vals;
                }else{
                    post_data[$(el).attr('name')] = $(el).find(':selected').val();
                }
            }else{
                post_data[$(el).attr('name')] = $(el).val();
            }
        });
        if(!post_data.currency.length){
            alert('Выберите валюту');
            return false;
        }
        $('.loader-cover').show()
        $.post(actionURL,post_data,function(resp){  //передача ответа сервера в Google Charts, рисование
            $('.loader-cover').hide()
            var data = google.visualization.arrayToDataTable(resp.data);
            var options = resp.options;
            window.chart.draw(data, options);
            $(window).scrollTop($(document).height());
        });
    });
});