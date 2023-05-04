import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'
const ConnectionMessagesMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('connection_messages', {}, 'tools')}
  >
  </MenuSection>

ConnectionMessagesMenu.propTypes = {
  path: T.string
}

export {
  ConnectionMessagesMenu
}
