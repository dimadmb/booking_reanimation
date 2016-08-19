<?php
header("Content-Type: text/html;charset=utf-8");

ini_set('display_errors',1);

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";


$db = mysqli2::connect();

$turs = tur::getListTurs();

?>

<link rel="stylesheet" href="/bower_components/uikit/css/uikit.gradient.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/uikit-yellow.css">

<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.gradient.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.gradient.min.css">

<link rel="stylesheet" href="/css/findform.css">

<link rel="stylesheet" href="/bower_components/rcswitcher/css/rcswitcher.min.css">

<link rel="stylesheet" href="/bower_components/font-awesome/css/font-awesome.min.css">


<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/findform.php"; ?>

<div id="turs"></div>

<script id="tmplTurs" type="text/mustache">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . "/tmpl/tursAdmin.mustache";?>
</script>

<script id="tmplKautas" type="text/mustache">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . "/tmpl/kautas.mustache";?>
</script>




<script src="/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/bower_components/lodash/dist/lodash.min.js"></script>

<script src="/bower_components/mustache.js/mustache.min.js"></script>
<script src="/bower_components/uikit/js/uikit.min.js"></script>
<script src="/bower_components/uikit/js/components/datepicker.min.js"></script>


<script src="/bower_components/uikit/js/components/form-select.min.js"></script>

<script src="/bower_components/rcswitcher/js/rcswitcher.min.js"></script>

<script src="/bower_components/uikit/js/components/notify.min.js"></script>

<link rel="stylesheet" href="/bower_components/uikit/css/components/notify.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/notify.gradient.min.css">

<style type="text/css">
    .fa-refresh.fa-spin{

        font-size: 32px;
    }
</style>

<script>

    jQuery(function($) {
        AA = {};


        AA.turs = '<?=json_encode($turs)?>';
        AA.turs = $.parseJSON(AA.turs);

        AA.tmpl = {
            turs : document.getElementById('tmplTurs').innerHTML,
            kautas : document.getElementById('tmplKautas').innerHTML

        };

        var content = Mustache.render(AA.tmpl.turs, {turs : AA.turs } );
        $('#turs').html(content);

        $('#amountTur').html( AA.turs.length);


        $(document).on('click','[data-type-discount]',function(){
            var urlTur;
            var url = '/service/tur.php?action=switchType';
            var data = {
                id : $(this).closest('tr').data('id'),
                type : $(this).data('type-discount')
            };

            // выделяем визуально

            $(this).closest('.divGroup').find('label').removeClass('checked');
            $(this).closest('label').addClass('checked');



            $.post(url,data,function(response){

                if (response.success==1){
                    UIkit.notify({
                        message : 'Обновлено!',
                        status  : 'info',
                        timeout : 1000,
                        pos     : 'top-center'
                    });
                }

                if (data.type=='off') return false;


                $('.massKauta').html('<i class="fa fa-refresh fa-spin"></i>');


                url = "/service/tur.php?action=getListKauta";
                urlTur = "/service/tur.php?action=getTur";



                $.post(url, {id_tur:data.id},function(response){

                    $.post(urlTur, {id_tur:data.id},function(responseTur){
					//console.log(responseTur.id);

                        var content = Mustache.render(AA.tmpl.kautas,
                            
							{
                                id_tur : responseTur.id,
                                d1 : responseTur.d1,
                                d2 : responseTur.d2,
                                teplohod : responseTur.teplohod,
                                tur : responseTur.name,
                                kautas : response
                            } );

                        $('.massKauta').html(content);
                    });
                });

                var modal = UIkit.modal("#kautas");
                if ( modal.isActive() ) {
                    modal.hide();
                } else {
                    modal.show();
                }

            });





        });


        $(document).on('click', '.itemKauta',function(){


            var url = "/service/tur.php?action=doDiscount";

            var data = {
                id_tur : $('.id_tur').val()*1,
                num : $(this).data('num')*1};

            var self = this;


            $.post(url, data,function(response){

                if (response.action=='delete'){ $(self).removeClass('selectKauta');}
                else if (response.action=='insert'){ $(self).addClass('selectKauta');}
            });





        });




    });

</script>

<script src="/js/filter.js"> </script>
