

    function filterTur(){

        console.log('doFilter');

        var filter_turs = _.clone(AA.turs);


        // по чекбоксам
        var happy = 0;
        if ( jQuery('#checkHappy').prop('checked')){
            happy = 1;
        }
        var special = 0;
        if ( jQuery('#checkSpecial').prop('checked')){
            special=1;
        }

        console.log(happy);
        console.log(special);

        if ( happy + special > 0) {
            filter_turs = _.filter(filter_turs, function (item) {
                var out = false;
                if  (item.is_happy == happy && happy ==1) {out=true;}
                if  (item.is_special >0 && special==1) {out = true;}

                return out;
            });
        }



        var ship = jQuery('#ship').val();

        if (ship > 0) {
            filter_turs = _.filter(  filter_turs, {'id_teplohod' : ship});
        }

        var city = jQuery('#city').val();

        if (city >0 ){
            filter_turs = _.filter(filter_turs, {'city1' : city});
        }

        //фильтруем по датам

        var lasting1 = jQuery('#lasting1').val() * 1;
        var lasting2 = jQuery('#lasting2').val() * 1;

        // фильтр по длительности
        filter_turs = _.filter(filter_turs, function(item){
            var days = item.days * 1;
            return  ( days >= lasting1 && days <= lasting2 );
        });


        // фильтр по датам
        var d1 = jQuery('#d1').val();
        var d2 = jQuery('#d2').val();
        var mass1 = d1.split('.');
        var mass2 = d2.split('.');
        var date1  = new Date( mass1[2] +  '-' + mass1[1] + '-' + mass1[0]);
        var date2  = new Date( mass2[2] +  '-' + mass2[1] + '-' + mass2[0]);

        filter_turs = _.filter(filter_turs, function(item){
            var mass0 = item['date_start2'].split('.');
            var date0  = new Date( '20' +mass0[2] +  '-' + mass0[1] + '-' + mass0[0]);
            return  ( date0 >= date1 && date0 <= date2 );
        });



        var content = Mustache.render(AA.tmpl.turs, {turs : filter_turs } );
        jQuery('#turs').html(content);
        jQuery('#amountTur').html( filter_turs.length);

        if ( filter_turs.length == AA.turs.length){
            jQuery('#resetFilter').hide();
        }else{
            jQuery('#resetFilter').show();

            if (filter_turs.length==0){
                jQuery('#resetFilter').addClass('warning');
            } else{
                jQuery('#resetFilter').removeClass('warning');
            }
        }





    }

jQuery(function($){

    $('#ship').change(function(){ filterTur(); });
    $('#city').change(function(){ filterTur(); });

    $('.filterCheck').click(function(){ filterTur(); });

    $('#lasting1').change(function(){

        // защита от дурака
        var d1 = $('#lasting1').val()*1;
        var d2 = $('#lasting2').val()*1;

        if (d1>d2) { $('#lasting2').val(d1);}

        filterTur(); });
    $('#lasting2').change(function(){

        // защита от дурака
        var d1 = $('#lasting1').val()*1;
        var d2 = $('#lasting2').val()*1;
        if (d2 <d1) { $('#lasting1').val(d2);}

        filterTur(); });


    $('#resetFilter').click(function(){

        $(this).fadeOut(500).removeClass('warning');

        $('#ship').val(0);
        $('#city').val(0);

        $('#lasting1').val(AA.lasting1);
        $('#lasting2').val(AA.lasting2);

        $('#d1').val(AA.min_out_day);
        $('#d2').val(AA.max_out_day);


        $('.filterCheck').removeAttr('checked');

        filterTur();





    });


    $('#d1').on('hide.uk.datepicker', function(){ filterTur(); });

    $('#d2').on('hide.uk.datepicker', function(){ filterTur(); });
});
