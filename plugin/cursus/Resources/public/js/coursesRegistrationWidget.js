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
    
    var currentSearch = $('#courses-registration-widget-datas-box').data('search');
    var currentMax = $('#courses-registration-widget-datas-box').data('max');
    var currentOrderedBy = $('#courses-registration-widget-datas-box').data('ordered-by');
    var currentOrder = $('#courses-registration-widget-datas-box').data('order');
    var widgetInstanceId = $('#courses-registration-widget-datas-box').data('widget-instance-id');
    
    function refreshCoursesList()
    {
        var route = Routing.generate(
            'claro_cursus_courses_list_for_registration_widget',
            {
                'widgetInstance': widgetInstanceId,
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
                $('#courses-list').html(datas);
            }
        });
    }
    
    $('#courses-registration-widget').on('click', 'a', function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        var route = $(element).attr('href');

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#courses-list').html(datas);
            }
        });
    });

    $('#courses-registration-widget').on('click', '#search-course-btn', function () {
        currentSearch = $('#search-course-input').val();
        refreshCoursesList();
    });

    $('#courses-registration-widget').on('keypress', '#search-course-input', function(e) {
        
        if (e.keyCode === 13) {
            e.preventDefault();
            currentSearch = $(this).val();
            refreshCoursesList();
        }
    });

    $('#courses-registration-widget').on('click', '.session-register-btn', function () {
        var sessionId = $(this).data('session-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_session_self_register',
                {'session': sessionId}
            ),
            removeRegistrationBtn,
            sessionId,
            Translator.trans('session_self_registration_message', {}, 'platform'),
            Translator.trans('session_registration', {}, 'platform')
        );
    });

    $('#courses-registration-widget').on('click', '.course-queue-request-btn', function () {
        var courseId = $(this).data('course-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_queue_register',
                {'course': courseId}
            ),
            updateCourseQueueRequetBtn,
            courseId,
            Translator.trans('next_session_registration_request_message', {}, 'platform'),
            Translator.trans('next_session_registration_request', {}, 'platform')
        );
    });

    $('#courses-registration-widget').on('click', '.cancel-course-queue-request-btn', function () {
        var courseId = $(this).data('course-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_queue_cancel',
                {'course': courseId}
            ),
            updateCourseQueueRequetCancelBtn,
            courseId,
            Translator.trans('next_session_registration_request_cancel_message', {}, 'platform'),
            Translator.trans('next_session_registration_request_cancel', {}, 'platform')
        );
    });
    
    var removeRegistrationBtn = function (event, sessionId) {
        $('#session-registration-btn-' + sessionId).empty();
        var element = '<span class="label label-success"><i class="fa fa-check"></i></span>';
        $('#session-registration-btn-' + sessionId).html(element);
    };
    
    var updateCourseQueueRequetBtn = function (event, courseId) {
        var courseQueueBtn = $('#course-queue-btn-' + courseId);
        courseQueueBtn.removeClass('course-queue-request-btn');
        courseQueueBtn.addClass('cancel-course-queue-request-btn');
        courseQueueBtn.empty();
        var element = '<span class="label label-success">' +
            Translator.trans('request_done', {}, 'platform') +
            '</span>';
        courseQueueBtn.html(element);
    };
    
    var updateCourseQueueRequetCancelBtn = function (event, courseId) {
        var courseQueueBtn = $('#course-queue-btn-' + courseId);
        courseQueueBtn.removeClass('cancel-course-queue-request-btn');
        courseQueueBtn.addClass('course-queue-request-btn');
        courseQueueBtn.empty();
        var element = '<span class="label label-info">' +
            Translator.trans('next_session_registration_request', {}, 'platform') +
            '</span>';
        courseQueueBtn.html(element);
    };
})();