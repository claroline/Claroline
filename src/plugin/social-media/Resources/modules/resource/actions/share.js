
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_SHARE} from '#/plugin/social-media/modals/share'
import {param} from '#/main/app/config'


export default (resourceNodes) => ({
  name: 'share',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-share-alt',
  label: trans('share', {}, 'actions'),
  modal: [MODAL_SHARE, {
    title: resourceNodes[0].name,
    url: `${param('serverUrl')}/${url(['claro_resource_share', {id: resourceNodes[0].id}])}`
  }]
})
