import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const ProgressionMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('progression', {}, 'tools')}
  />

ProgressionMenu.propTypes = {
  path: T.string,
  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  ProgressionMenu
}
