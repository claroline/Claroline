import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'

import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

// commons steps between all widgets
export default (currentTab, update, widgetType, customSteps = []) => [
  {
    before: [{
      type: 'callback',
      action: () => update('widgets', [].concat(currentTab.widgets, [merge({id: makeId()}, WidgetContainerTypes.defaultProps)]))
    }],
    highlight: ['.widgets-grid .widget-container:last-of-type'],
    content: {
      title: trans(`home.create-widget-${widgetType}.intro.title`, {}, 'walkthrough'),
      message: trans(`home.create-widget-${widgetType}.intro.message`, {}, 'walkthrough')
    },
    position: {
      placement: 'top',
      target: '.widgets-grid .widget-container:last-of-type'
    }
  }, {
    highlight: ['.widgets-grid .widget-container:last-of-type .btn-add-widget'],
    content: {
      message: trans('home.create-widget.add_widget.message', {}, 'walkthrough'),
      info: trans('home.create-widget.add_widget.info', {}, 'walkthrough')
    },
    position: {
      placement: 'top',
      target: '.widgets-grid .widget-container:last-of-type .btn-add-widget'
    },
    requiredInteraction: {
      type: 'click',
      target: '.widgets-grid .widget-container:last-of-type .btn-add-widget',
      message: trans('home.create-widget.add_widget.action', {}, 'walkthrough')
    }
  }, {
    content: {
      title: trans('home.create-widget.widget_type.title', {}, 'walkthrough'),
      message: trans('home.create-widget.widget_type.message', {}, 'walkthrough')
    }
  }, {
    highlight: [`#widget-type-${widgetType}`],
    content: {
      message: trans('home.create-widget.select_type.message', {widgetType: trans(widgetType, {}, 'widget')}, 'walkthrough')
    },
    position: {
      target: `#widget-type-${widgetType}`,
      placement: 'bottom'
    },
    requiredInteraction: {
      type: 'click',
      target: `#widget-type-${widgetType}`,
      message: trans('home.create-widget.select_type.action', {widgetType: trans(widgetType, {}, 'widget')}, 'walkthrough')
    }
  }
].concat(customSteps, [
  {
    highlight: ['.modal-btn'],
    content: {
      message: trans('home.create-widget.save.message', {}, 'walkthrough')
    },
    requiredInteraction: {
      type: 'click',
      target: '.modal-btn',
      message: trans('home.create-widget.save.action', {}, 'walkthrough')
    },
    position: {
      placement: 'top',
      target: '.modal-btn'
    }
  }, {
    highlight: ['.widgets-grid .widget-container:last-of-type .widget-col'],
    content: {
      message: trans('home.create-widget.created_widget.message', {}, 'walkthrough')
    },
    position: {
      placement: 'top',
      target: '.widgets-grid .widget-container:last-of-type .widget-col'
    }
  }
])
