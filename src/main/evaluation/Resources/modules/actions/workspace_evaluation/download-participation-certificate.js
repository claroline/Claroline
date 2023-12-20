import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {constants} from '#/main/evaluation/constants'
import get from 'lodash/get'

export default (evaluations) => ({
  name: 'download-participation-certificate',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-file-pdf',
  label: trans('download_participation_certificate', {}, 'actions'),
  displayed: -1 !== evaluations.findIndex(row => [
    constants.EVALUATION_STATUS_COMPLETED,
    constants.EVALUATION_STATUS_PARTICIPATED
  ].includes(get(row, 'status', constants.EVALUATION_STATUS_UNKNOWN))),
  request: {
    url: ['apiv2_workspace_download_participation_certificates'],
    request: {
      method: 'POST',
      body: JSON.stringify(evaluations.map(evaluation => evaluation.id))
    }
  },
  scope: ['object', 'collection'],
  group: trans('transfer')
})
