import {trans} from '#/main/app/intl/translation'

export default {
  name: 'tokens',
  icon: 'fa fa-fw fa-coins',
  parameters: {
    definition: [
      {
        name: 'token',
        label: trans('token', {}, 'security'),
        type: 'string',
        primary: true,
        displayed: true
      }, {
        name: 'description',
        label: trans('description'),
        type: 'string',
        displayed: true,
        options: {
          long: true
        }
      }, {
        name: 'restrictions.locked',
        alias: 'locked',
        label: trans('locked'),
        type: 'boolean',
        displayed: true
      }
    ]
  }
}
