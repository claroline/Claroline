import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (evaluations) => ({
  name: 'regenerate-certificate',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-arrows-rotate',
  label: evaluations.length > 1
    ? trans('regenerate_certificates', {}, 'actions')
    : trans('regenerate_certificate', {}, 'actions'),
  request: {
    url: ['apiv2_workspace_regenerate_certificate'],
    request: {
      method: 'POST',
      body: JSON.stringify(evaluations.map(evaluation => evaluation.id))
    }
  },
  scope: ['object', 'collection'],
  group: trans('transfer')
})
