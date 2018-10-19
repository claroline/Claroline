import {trans} from '#/main/app/intl/translation'

import widget from '#/main/core/tools/home/walkthroughs/widget'

export default (currentTab, update) => ({
  title: trans('home.create-widget-list.name', {}, 'walkthrough'),
  description: trans('home.create-widget-list.description', {}, 'walkthrough'),
  difficulty: 'hard',
  scenario: widget(currentTab, update, 'list', [
    // Data sources
    {
      content: {
        title: trans('home.create-widget-list.data_source.title', {}, 'walkthrough'),
        message: trans('home.create-widget-list.data_source.message', {}, 'walkthrough')
      }
    },
    // Select data sources
    {
      highlight: ['#source-type-resources'],
      content: {
        message: trans('home.create-widget-list.select_source.message', {}, 'walkthrough')
      },
      position: {
        target: '#source-type-resources',
        placement: 'top'
      },
      requiredInteraction: {
        type: 'click',
        target: '#source-type-resources',
        message:  trans('home.create-widget-list.select_source.action', {}, 'walkthrough')
      }
    },
    // List form
    {
      highlight: ['.modal .list-form'],
      content: {
        title:  trans('home.create-widget-list.list_form.title', {}, 'walkthrough'),
        message:  trans('home.create-widget-list.list_form.message', {}, 'walkthrough')
      },
      position: {
        target: '.modal .list-form',
        placement: 'top'
      }
    },
    // Open display section
    {
      highlight: ['.list-form a[href="#list-display"]'],
      content: {
        message: trans('home.create-widget-list.list_display.message', {}, 'walkthrough')
      },
      position: {
        placement: 'top',
        target: '.list-form a[href="#list-display"]'
      },
      requiredInteraction: {
        type: 'click',
        target: '.list-form a[href="#list-display"]',
        message: trans('home.create-widget-list.list_display.action', {}, 'walkthrough')
      }
    },
    // Choose a display mode
    {
      highlight: ['.modal .list-form #display'],
      content: {
        message: trans('home.create-widget-list.list_display_select.message', {}, 'walkthrough')
      },
      position: {
        placement: 'top',
        target: '.modal .list-form #display'
      },
      requiredInteraction: {
        type: 'change',
        target: '.modal .list-form #display',
        message: trans('home.create-widget-list.list_display_select.action', {}, 'walkthrough')
      }
    }
  ])
})
