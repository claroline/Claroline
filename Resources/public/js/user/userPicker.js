/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';
    
    var currentSearch = $('#user-picker-datas-box').data('search');
//    var currentPage = $('#user-picker-datas-box').data('page');
    var currentMax = $('#user-picker-datas-box').data('max');
    var currentOrderedBy = $('#user-picker-datas-box').data('ordered-by');
    var currentOrder = $('#user-picker-datas-box').data('order');
    var filterType = 'none';
    var secondFilterValue = 'none';
    var thirdFilterValue = 'none';
    
    function displaySecondFilter()
    {
        if (filterType === 'none') {
            resetFilters(true, true, true);
        } else {
            $('#box-filter-level-2').show('slow', function () {
                secondFilterValue = 'none';
                thirdFilterValue = 'none';
                $('#filter-level-2').val('none');
                $('#filter-level-3').val('none');
                
                $.ajax({
                    url: Routing.generate(
                        'claro_filters_list_for_user_picker',
                        {'filterType': filterType}
                    ),
                    type: 'GET',
                    success: function (datas) {
                        $('#filter-level-2').empty();
                        var option = '<option value="none">--- ' +
                            Translator.trans('select_a_' + filterType, {}, 'platform') +
                            ' ---</option>';
                        $('#filter-level-2').append(option);
                        
                        for (var i = 0; i < datas.length; i++) {
                            option = '<option value="' +
                                datas[i]['id'] +
                                '">' +
                                datas[i]['name']
                                '</option>';
                            $('#filter-level-2').append(option);
                        }
                        $('#box-filter-level-2').show('slow', function () {
                            $(this).removeClass('hidden');
                        });
                    }
                });
            });
        }
    }
    
    function displayThirdFilter()
    {
        if (filterType === 'workspace' && secondFilterValue !== 'none') {
            thirdFilterValue = 'none';
            
            $.ajax({
                url: Routing.generate(
                    'claro_workspace_roles_list_for_user_picker',
                    {'workspace': secondFilterValue}
                ),
                type: 'GET',
                success: function (datas) {
                    $('#filter-level-3').empty();
                    var option = '<option value="none">' +
                        Translator.trans('all_roles', {}, 'platform') +
                        '</option>';
                    $('#filter-level-3').append(option);

                    for (var i = 0; i < datas.length; i++) {
                        option = '<option value="' +
                            datas[i]['id'] +
                            '">' +
                            datas[i]['name']
                            '</option>';
                        $('#filter-level-3').append(option);
                    }
                    $('#box-filter-level-3').show('slow', function () {
                        $(this).removeClass('hidden');
                    });
                }
            });
        } else {
            resetFilters(false, false, true);
        }
    }
    
    function displayFilterCreateButton()
    {
        if (filterType !== 'none' && secondFilterValue !== 'none') {
            $('#box-filter-create-btn').show('slow', function () {
                $(this).removeClass('hidden');
            });
        } else {
            $('#box-filter-create-btn').hide('slow');
        }
    }
    
    function resetFilters(first, second, third)
    {
        if (first) {
            $('#filter-level-1').val('none');
            filterType = 'none';
        }
        
        if (second) {
            $('#box-filter-level-2').hide('slow', function () {
                $('#filter-level-2').val('none');
                $('#filter-level-2').empty();
                secondFilterValue = 'none';
            });
        }
        
        if (third) {
            $('#box-filter-level-3').hide('slow', function () {
                $('#filter-level-3').val('none');
                $('#filter-level-3').empty();
                thirdFilterValue = 'none';
            });
        }
        displayFilterCreateButton();
    }
    
    $('#user-picker-modal').on('click', 'a', function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        var url = $(element).attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            success: function (datas) {
                $('#user-picker-body').html(datas);
            }
        });
    });

    $('#user-picker-modal').on('click', '#search-user-btn', function () {
        currentSearch = $('#search-user-input').val();
        var route = Routing.generate(
            'claro_users_list_for_user_picker',
            {
                'search': currentSearch,
                'max': currentMax,
                'orderedBy': currentOrderedBy,
                'order': currentOrder
            }
        );

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#user-picker-body').html(datas);
            }
        });
    });

    $('#user-picker-modal').on('keypress', '#search-user-input', function(e) {
        
        if (e.keyCode === 13) {
            event.preventDefault();
            currentSearch = $(this).val();
            var route = Routing.generate(
                'claro_users_list_for_user_picker',
                {
                    'search': currentSearch,
                    'max': currentMax,
                    'orderedBy': currentOrderedBy,
                    'order': currentOrder
                }
            );

            $.ajax({
                url: route,
                type: 'GET',
                success: function (datas) {
                    $('#user-picker-body').html(datas);
                }
            });
        }
    });
    
    $('#user-picker-modal').on('change', '#max-select', function () {
        currentMax = $(this).val();
        var route = Routing.generate(
            'claro_users_list_for_user_picker',
            {
                'search': currentSearch,
                'max': currentMax,
                'orderedBy': currentOrderedBy,
                'order': currentOrder
            }
        );

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#user-picker-body').html(datas);
            }
        });
    });
    
    $('#user-picker-modal').on('change', '#filter-level-1', function () {
        filterType = $(this).val();
        displaySecondFilter();
        displayThirdFilter();
        displayFilterCreateButton();
    });
    
    $('#user-picker-modal').on('change', '#filter-level-2', function () {
        secondFilterValue = $(this).val();
        displayThirdFilter();
        displayFilterCreateButton();
    });
    
    $('#user-picker-modal').on('change', '#filter-level-3', function () {
        thirdFilterValue = $(this).val();
    });
})();