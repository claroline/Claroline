import {trans} from '#/main/core/translation'

const tabFormSections = [
  {
    icon: 'fa fa-fw fa-plus',
    title: trans('general'),
    primary: true,
    fields: [{
      name: 'title',
      type: 'string',
      label: trans('menu_title'),
      options: {
        maxLength: 20
      },
      required: true
    }, {
      name: 'longTitle',
      type: 'string',
      label: trans('title'),
      required: true
    }]
  },
  {
    icon: 'fa fa-fw fa-desktop',
    title: trans('display_parameters'),
    fields: [{
      name: 'icon',
      type: 'string',
      label: trans('icon')
    },
    {
      name: 'position',
      type: 'number',
      label: trans('position')
    },
    {
      name: 'poster',
      label: trans('poster'),
      type: 'file',
      options: {
        ratio: '3:1'
      }
    }]
  }
]


export {
  tabFormSections
}
