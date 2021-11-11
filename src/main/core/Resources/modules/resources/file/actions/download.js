
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

export default (resourceNodes) => ({
  name: 'download',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-file-download',
  label: trans('download', {}, 'actions'),
  target: url(
    ['claro_resource_download'],
    {ids: resourceNodes.map(resourceNode => resourceNode.id)}
  )
})
