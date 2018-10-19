import {trans} from '#/main/app/intl/translation'

import widget from '#/main/core/tools/home/walkthroughs/widget'

export default (currentTab, update) => ({
  title: trans('home.create-widget-simple.name', {}, 'walkthrough'),
  description: trans('home.create-widget-simple.description', {}, 'walkthrough'),
  difficulty: 'easy',
  scenario: widget(currentTab, update, 'simple', [
    {
      highlight: ['#parameters-content-container'],
      content: {
        message: trans('home.create-widget-simple.create_content.message', {}, 'walkthrough')
      },
      position: {
        target: '#parameters-content-container',
        placement: 'top'
      }
    }
  ])
})
