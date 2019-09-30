import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

export default (rows) => ({
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-compress',
  label: trans('merge_accounts'),
  target: rows.length === 2 ? `/admin/community/users/merge/${rows[0].id}/${rows[1].id}`: '',
  displayed: rows.length === 2,
  dangerous: true
})
