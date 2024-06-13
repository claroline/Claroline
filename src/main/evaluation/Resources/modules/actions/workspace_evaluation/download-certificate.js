import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (evaluations) => ({
  name: 'download-certificate',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw ' + (evaluations.length > 1 ? 'fa-file-zipper' : 'fa-file-pdf'),
  label: evaluations.length > 1
    ? trans('download_certificates', {}, 'actions')
    : trans('download_certificate', {}, 'actions'),
  displayed: evaluations.length > 0,
  request: {
    url: ['apiv2_workspace_download_certificate'],
    request: {
      method: 'POST',
      body: JSON.stringify(evaluations.map(evaluation => evaluation.id))
    }
  },
  scope: ['object', 'collection'],
  group: trans('transfer')
})
