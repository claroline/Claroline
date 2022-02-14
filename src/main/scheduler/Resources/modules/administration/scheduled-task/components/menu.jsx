import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const ScheduledTaskMenu = (props) =>
  <MenuSection
    {...props}
    title={trans('scheduled_tasks', {}, 'tools')}
  />

ScheduledTaskMenu.propTypes = {
  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  ScheduledTaskMenu
}
