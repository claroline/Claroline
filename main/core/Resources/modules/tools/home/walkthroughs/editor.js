import {trans} from '#/main/core/translation'

import widget from '#/main/core/tools/home/walkthroughs/widget'
import widgetList from '#/main/core/tools/home/walkthroughs/widget-list'
import widgetResource from '#/main/core/tools/home/walkthroughs/widget-resource'
import widgetSimple from '#/main/core/tools/home/walkthroughs/widget-simple'

export default {
  title: trans('home.editor.name', {}, 'walkthrough'),
  description: trans('home.editor.description', {}, 'walkthrough'),
  difficulty: 'easy',

  additional: [
    widget,
    widgetSimple,
    widgetList,
    widgetResource
  ],

  scenario: [
    // Intro
    {
      content: {
        title: trans('home.editor.intro.title', {}, 'walkthrough'),
        message: trans('home.editor.intro.message', {}, 'walkthrough')
      }
    },
    // Tabs
    {
      highlight: ['.tool-nav'],
      content: {
        title: trans('home.editor.tabs.title', {}, 'walkthrough'),
        message: trans('home.editor.tabs.message', {}, 'walkthrough')
      },
      position: {
        target: '.tool-nav',
        placement: 'bottom'
      }
    },
    // Opened tab
    {
      highlight: ['.tool-nav .nav-tab.active'],
      content: {
        message: trans('home.editor.opened_tab.message', {}, 'walkthrough')
      },
      position: {
        target: '.tool-nav .nav-tab.active',
        placement: 'bottom'
      }
    },
    // Create a new tab
    {
      highlight: ['.tool-nav .nav-add-tab'],
      content: {
        icon: 'fa fa-plus',
        title: trans('action', {actionName: trans('add_tab', {}, 'home')}, 'walkthrough'),
        message: trans('home.editor.add_tab.message', {}, 'walkthrough')
      },
      position: {
        target: '.tool-nav .nav-add-tab',
        placement: 'bottom'
      },
      requiredInteraction: {
        type: 'click',
        target: '.tool-nav .nav-add-tab',
        message: trans('home.editor.add_tab.action', {}, 'walkthrough')
      }
    },
    // New tab intro
    {
      highlight: ['.home-tool > .page-header'], // strict selector to avoid impacting widgets which embed pages (eg. resource).
      content: {
        message: trans('home.editor.created_tab.message', {}, 'walkthrough')
      },
      position: {
        target: '.home-tool > .page-header',
        placement: 'bottom'
      }
    },
    // Tab form
    {
      highlight: [
        '.form-primary-section',
        '.form-sections'
      ],
      content: {
        title: trans('home.editor.tab_form.title', {}, 'walkthrough'),
        message: trans('home.editor.tab_form.message', {}, 'walkthrough')
      },
      position: {
        target: '.data-form',
        placement: 'top'
      }
    },
    // Tab sections
    {
      highlight: ['.widgets-grid'],
      content: {
        title: trans('home.editor.tab_sections.title', {}, 'walkthrough'),
        message: trans('home.editor.tab_sections.message', {}, 'walkthrough')
      },
      position: {
        target: '.widgets-grid',
        placement: 'top'
      }
    },
    // Create a new tab section
    {
      highlight: ['.btn-add-section'],
      content: {
        message: trans('home.editor.add_section.message', {}, 'walkthrough')
      },
      position: {
        target: '.btn-add-section',
        placement: 'top'
      },
      requiredInteraction: {
        type: 'click',
        target: '.btn-add-section',
        message: trans('home.editor.add_section.action', {}, 'walkthrough')
      }
    },
    // Section
    {
      content: {
        title: trans('home.editor.section_form.title', {}, 'walkthrough'),
        message: trans('home.editor.section_form.message', {}, 'walkthrough')
      }
    },
    // Select section layout
    {
      highlight: ['#layout-cols-1-1-1'],
      content: {
        message: trans('home.editor.select_layout.message', {}, 'walkthrough')
      },
      position: {
        target: '#layout-cols-1-1-1',
        placement: 'top'
      },
      requiredInteraction: {
        type: 'click',
        target: '#layout-cols-1-1-1',
        message: trans('home.editor.select_layout.action', {}, 'walkthrough')
      }
    },
    // Configure section
    {
      highlight: ['.widget-section-form'],
      content: {
        message: trans('home.editor.section_configure.message', {}, 'walkthrough')
      },
      position: {
        target: '.widget-section-form',
        placement: 'top'
      }
    },
    // Save modifications
    {
      highlight: ['#widget-section-form-save'],
      content: {
        message: trans('home.editor.section_save.message', {}, 'walkthrough')
      },
      position: {
        target: '#widget-section-form-save',
        placement: 'top'
      },
      requiredInteraction: {
        type: 'click',
        target: '#widget-section-form-save',
        message: trans('home.editor.section_save.action', {}, 'walkthrough')
      }
    },
    // Created section
    {
      highlight: ['.widgets-grid .widget-container:last-of-type'],
      content: {
        message: trans('home.editor.created_section.message', {}, 'walkthrough')
      },
      position: {
        target: '.widgets-grid .widget-container:last-of-type',
        placement: 'top'
      }
    },
    // Save modifications
    {
      highlight: ['.form-toolbar .btn-save'],
      content: {
        icon: 'fa fa-save',
        message: trans('home.editor.save.message', {}, 'walkthrough')
      },
      position: {
        target: '.form-toolbar .btn-save',
        placement: 'top'
      }
    },
    // Exit editor
    {
      highlight: ['.form-toolbar .btn-exit'],
      content: {
        icon: 'fa fa-sign-out-alt',
        message: trans('home.editor.exit.message', {}, 'walkthrough')
      },
      position: {
        target: '.form-toolbar .btn-exit',
        placement: 'top'
      }
    }
  ]
}
