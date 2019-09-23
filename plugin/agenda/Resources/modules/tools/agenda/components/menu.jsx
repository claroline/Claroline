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

    <div className="list-group">
      <label className="list-group-item">
        <input
          type="checkbox"
          checked={-1 !== props.types.indexOf('event')}
          onChange={e => {
            let newTypes = [].concat(props.types)
            if (e.target.checked) {
              newTypes.push('event')
            } else {
              newTypes.splice(newTypes.indexOf('event'), 1)
            }

            props.changeTypes(newTypes)
          }}
        />

        {trans('event', {}, 'agenda')}
      </label>

      <label className="list-group-item">
        <input
          type="checkbox"
          checked={-1 !== props.types.indexOf('task')}
          onChange={e => {
            let newTypes = [].concat(props.types)
            if (e.target.checked) {
              newTypes.push('task')
            } else {
              newTypes.splice(newTypes.indexOf('task'), 1)
            }

            props.changeTypes(newTypes)
          }}
        />

        {trans('task', {}, 'agenda')}
      </label>
    </div>
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
  types: T.arrayOf(T.oneOf(['event', 'task'])).isRequired,
  changeTypes: T.func.isRequired,
  selected: T.string.isRequired
}

export {
  AgendaMenu
}
