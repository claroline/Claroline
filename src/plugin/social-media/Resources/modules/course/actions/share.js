
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_SHARE} from '#/plugin/social-media/modals/share'

export default (courses) => ({
  name: 'share',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-share-alt',
  label: trans('share', {}, 'actions'),
  modal: [MODAL_SHARE, {
    title: courses[0].name,
    url: url(['apiv2_cursus_course_share', {id: courses[0].id}, true])
  }],
  group: trans('community')
})
