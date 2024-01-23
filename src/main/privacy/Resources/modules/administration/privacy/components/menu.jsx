import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const PrivacyMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('privacy', {}, 'tools')}
  />

PrivacyMenu.propTypes = {
  // from menu
  opened: T.bool,
  toggle: T.func,
  autoClose: T.func
}
export {
  PrivacyMenu
}
