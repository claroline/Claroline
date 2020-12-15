import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'

export default (resourceNodes, nodesRefresher, path) => ({
  name: 'chapter',
  type: LINK_BUTTON,
  label: trans('chapter_creation', {}, 'lesson'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: `${path}/${resourceNodes[0].slug}/new`
})
