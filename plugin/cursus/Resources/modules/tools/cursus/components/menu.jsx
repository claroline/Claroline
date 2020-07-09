import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const CursusMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('cursus', {}, 'tools')}
  >
    {false &&
      <Toolbar
        className="list-group"
        buttonName="list-group-item"
        actions={[
          {
            name: 'courses',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-tasks',
            label: trans('courses', {}, 'cursus'),
            target: props.path + '/courses'
          }, {
            name: 'sessions',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-cubes',
            label: trans('sessions', {}, 'cursus'),
            target: props.path + '/sessions'
          }, {
            name: 'session_events',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-clock-o',
            label: trans('session_events', {}, 'cursus'),
            target: props.path + '/events'
          }, {
            name: 'cursus',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-database',
            label: trans('cursus', {}, 'cursus'),
            target: props.path + '/cursus'
          }, {
            name: 'queues',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-hourglass-o',
            label: trans('pending_for_validation', {}, 'cursus'),
            target: props.path + '/queues'
          }, {
            name: 'parameters',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-cog',
            label: trans('parameters'),
            target: props.path + '/parameters'
          }
        ]}
        onClick={props.autoClose}
      />
    }
  </MenuSection>

CursusMenu.propTypes = {
  path: T.string,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  CursusMenu
}
