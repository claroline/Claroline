import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const ForumMenu = (props) =>
  <MenuSection
    {...props}
    title={trans('claroline_forum', {}, 'resource')}
  />

ForumMenu.propTypes = {
  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  ForumMenu
}
