import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors} from '#/main/community/tools/community/group/store'
import {UserShow as UserShowComponent} from '#/main/community/tools/community/user/components/show'

const UserShow = connect(
  state => ({
    path: toolSelectors.path(state),
    group: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  })
)(UserShowComponent)

export {
  UserShow
}
