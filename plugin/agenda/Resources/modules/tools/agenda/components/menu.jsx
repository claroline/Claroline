import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Calendar} from '#/main/core/layout/calendar/components/calendar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

import {route} from '#/plugin/agenda/tools/agenda/routing'

const AgendaMenu = props =>
  <MenuSection
    {...omit(props, 'history', 'path', 'view', 'selected')}
    className="agenda-menu"
    title={trans('agenda', {}, 'tools')}
  >
    <Calendar
      light={true}
      selected={props.selected}
      onChange={(selected) => props.history.push(
        route(props.path, props.view, selected)
      )}
      time={false}
      showCurrent={false}
    />
  </MenuSection>

AgendaMenu.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,

  path: T.string.isRequired,
  view: T.oneOf([
    'day',
    'week',
    'month',
    'year',
    'schedule'
  ]).isRequired,
  selected: T.string.isRequired
}

export {
  AgendaMenu
}
