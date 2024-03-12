
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (resourceNodes) => ({
  name: 'download',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-file-download',
  label: trans('download', {}, 'actions'),
  request: {
    url: url(
      ['claro_resource_download'],
      {ids: resourceNodes.map(resourceNode => resourceNode.id)}
    )
  }
})
