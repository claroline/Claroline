import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const AnnouncementMenu = props =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('claroline_announcement_aggregate', {}, 'resource')}
  />

AnnouncementMenu.propTypes = {
  path: T.string.isRequired,
  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

export {
  AnnouncementMenu
}
