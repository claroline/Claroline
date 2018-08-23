import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'

const action = () => ({
  name: 'chapter',
  type: LINK_BUTTON,
  label: trans('chapter_creation', {}, 'icap_lesson'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: '/new'
})

export {
  action
}
