import angular from 'angular/index'
import '../icap-tagcanvas/icap-tagcanvas.module'

import blogPanelCtrl from './panels.controller'
import archivesRenderer from './panel.archives.partial.html'
import calendarRenderer from './panel.calendar.partial.html'
import infobarRenderer from './panel.infobar.partial.html'
import redactorRenderer from './panel.redactor.partial.html'
import rssRenderer from './panel.rss.partial.html'
import searchRenderer from './panel.search.partial.html'
import tagcloudRenderer from './panel.tagcloud.partial.html'
import taglistRenderer from './taglist.partial.html'

angular
  .module('blog.panels', ['icap.tagcanvas'])
  .controller('blogPanelCtrl', blogPanelCtrl)
  .run(['$templateCache', ($templateCache) => {
    $templateCache.put('archives.panel.html', archivesRenderer)
    $templateCache.put('calendar.panel.html', calendarRenderer)
    $templateCache.put('search.panel.html', searchRenderer)
    $templateCache.put('infobar.panel.html', infobarRenderer)
    $templateCache.put('redactor.panel.html', redactorRenderer)
    $templateCache.put('rss.panel.html', rssRenderer)
    $templateCache.put('search.panel.html', searchRenderer)
    $templateCache.put('tagcloud.panel.html', tagcloudRenderer)
    $templateCache.put('taglist.html', taglistRenderer)
  }])
  .directive('blogPanels', () => {
    return {
      restrict: 'A',
      controller: 'blogPanelCtrl',
      controllerAs: 'panelsCtrl'
    }
  })