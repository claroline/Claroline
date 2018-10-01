import {trans} from '#/main/core/translation'

import widget from '#/main/core/tools/home/walkthroughs/widget'

export default (currentTab, update) => ({
  title: trans('home.create-widget-resource.name', {}, 'walkthrough'),
  description: trans('home.create-widget-resource.description', {}, 'walkthrough'),
  difficulty: 'easy',
  scenario: widget(currentTab, update, 'resource', [
    {
      highlight: ['#parameters-resource'],
      content: {
        message: trans('home.create-widget-resource.select_resource.message', {}, 'walkthrough')
      },
      position: {
        target: '#parameters-resource',
        placement: 'top'
      }
    }
  ])
})
