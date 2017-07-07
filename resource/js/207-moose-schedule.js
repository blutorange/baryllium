/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Schedule = function(window, Moose, undefined) {
    "use strict";
    var $ = Moose.Library.jQuery;
    var _ = Moose.Library.Lodash;
    var m = Moose.Library.Moment;
    var l = $.fullCalendar.locales[Moose.Environment.locale];
    
//    function updateSchedule($element) {
//        var data = {
//            action: 'update',
//        };
//        Moose.Util.ajaxServlet(Moose.Environment.paths.lessonServlet, 'PATCH', data, function(data) {
//            $element.fullCalendar('refetchEvents');
//        }, true, true);
//    }
    
    // http://jqueryui.com/themeroller/?scope=&folderName=custom-theme&bgImgOpacityError=95&bgImgOpacityHighlight=55&bgImgOpacityActive=100&bgImgOpacityHover=100&bgImgOpacityDefault=100&bgImgOpacityContent=100&bgImgOpacityHeader=100&cornerRadiusShadow=0px&offsetLeftShadow=0px&offsetTopShadow=4px&thicknessShadow=0px&opacityShadow=100&bgImgOpacityShadow=100&bgTextureShadow=flat&bgColorShadow=%23cccccc&opacityOverlay=30&bgImgOpacityOverlay=0&bgTextureOverlay=flat&bgColorOverlay=%23e4e4e4&iconColorError=%23cd0a0a&fcError=%23cd0a0a&borderColorError=%23cd0a0a&bgTextureError=flat&bgColorError=%23ffffff&iconColorHighlight=%230080ff&fcHighlight=%23363636&borderColorHighlight=%23fad42e&bgTextureHighlight=flat&bgColorHighlight=%23fbec88&iconColorActive=%23949494&fcActive=%230080ff&borderColorActive=%230080ff&bgTextureActive=flat&bgColorActive=%23e4e4e4&iconColorHover=%23217bc0&fcHover=%234e4e4e&borderColorHover=%230080ff&bgTextureHover=flat&bgColorHover=%23aaddff&iconColorDefault=%23e4e4e4&fcDefault=%23ffffff&borderColorDefault=%23aaddff&bgTextureDefault=flat&bgColorDefault=%230080ff&iconColorContent=%23aaddff&fcContent=%23333333&borderColorContent=%230080ff&bgTextureContent=flat&bgColorContent=%23ffffff&iconColorHeader=%23e4e4e4&fcHeader=%23ffffff&borderColorHeader=%23aaddff&bgTextureHeader=flat&bgColorHeader=%230080ff&cornerRadius=5px&fwDefault=bold&fsDefault=1.1em&ffDefault=Overpass%2C%20sans-serif
    function setupSchedule(element) {
        var $element = $(element);
        var headerRight = $element.data('headerRight') || 'listDay,agendaWeek,month,agendaFourDay';
        var headerCenter = $element.data('headerCenter') || 'title';
        var headerLeft = $element.data('headerLeft') || 'today prev,next';
        var activeView = $element.data('activeView') || 'agendaWeek';
        var height = $element.data('height') || null;
        var disableCache = !!$element.data('disableCache');
        var cache = {};
        $element.empty().fullCalendar({
            locale: window.Moose.Environment.locale,
            defaultView: activeView,
            weekends: true,
            theme: false,
            editable: false,
            height: height,
            allDaySlot: false,
            eventSources: [
                {
                    /**
                     * An event object looks like this:
                     *  {
                     *    title: 'Title of the event.',
                     *    start: '2010-01-09T12:30:00',
                     *    allDay: false,
                     *    end    : '2010-01-07'
                     *  }
                     *  For more options, see
                     *    https://fullcalendar.io/docs/event_data/Event_Object/
                     * @param {Moment} start Start time. Use start.unix() to get the UNIX timestamp in seconds.
                     * @param {Moment} end End time. Use end.unix() to get the UNIX timestamp in seconds.
                     * @param {string|boolean} timezone A string/boolean describing the calendar's current timezone. It is the exact value of the timezone option.
                     * @param {function} callback must be called when the custom event
                     * function has generated its events. It is the event function's
                     * responsibility to make sure callback is being called with an
                     * array of Event Objects.
                     * @returns {undefined}
                     */
                    events: function(start, end, timezone, callback) {
                        var cacheId = start.unix() + "-" + end.unix();
                        if (!disableCache && cache[cacheId]) {
                            callback(cache[cacheId]);
                            return;
                        }
                        Moose.Util.ajaxServlet({
                            url: Moose.Environment.paths.lessonServlet,
                            method: 'GET',
                            data: {
                                action: 'list',
                                request: {
                                    fields: {
                                        start: start.unix(),
                                        end: end.unix()
                                    }
                                }
                            },
                            onSuccess: function(data) {
                                var events = [];
                                _.each(data.entity, function(lesson){
                                    events.push({
                                        id: lesson.fields.id,
                                        title: lesson.fields.title,
                                        start: m(1000*lesson.fields.start),
                                        end: m(1000*lesson.fields.end)
                                    });                                
                                });
                                cache[cacheId] = events;
                                callback(events);
                            },
                            asJson: true,
                            showLoader: false
                        });
                    }
                }
            ],
            customButtons: {
//                update: {
//                    text: l.buttonText.update,
//                    click: function() {
//                        if (confirm(l.custom.updateConfirm)) {
//                            cache = {};
//                            updateSchedule($element);
//                        }
//                    }
//                }
            },
            header: {
                left:   headerLeft,
                center: headerCenter,
                right:  headerRight
            },
            views: {
                agendaWeek: {
                  axisWidth: '43px'  
                },
                agendaFourDay: {
                    type: 'agenda',
                    duration: { days: 4 },
                    buttonTextKey: 'fourday'
                }
            }
        });
    }
    
    function onDocumentReady() {
        $('.schedule-auto').eachValue(setupSchedule);
    }

    return {
        onDocumentReady: onDocumentReady,
        setupSchedule: setupSchedule
    };        
};
