import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {User as UserTypes} from '#/main/core/user/prop-types'

import {MenuMain} from '#/main/app/layout/menu/containers/main'

const HomeMenu = () =>
  <MenuMain
    title={trans('home')}
    actions={[]}
  />

HomeMenu.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ),
  section: T.string,
  changeSection: T.func.isRequired
}

HomeMenu.defaultProps = {

}

export {
  HomeMenu
}
