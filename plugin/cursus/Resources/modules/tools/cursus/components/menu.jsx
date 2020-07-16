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
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'catalog',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-list',
          label: trans('catalog', {}, 'cursus'),
          target: props.path + '/catalog'
        }, {
          name: 'sessions',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-calendar-week',
          label: trans('sessions', {}, 'cursus'),
          target: props.path + '/sessions'
        }, {
          name: 'session_events',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-clock',
          label: trans('session_events', {}, 'cursus'),
          target: props.path + '/events'
        }, {
          name: 'pending',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-user-plus',
          label: trans('pending_registrations'),
          target: props.path + '/pending'
        }
      ]}
      onClick={props.autoClose}
    />
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
