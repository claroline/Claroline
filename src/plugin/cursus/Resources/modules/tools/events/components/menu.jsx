import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const EventsMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('training_events', {}, 'tools')}
  >
    <Toolbar
      className="list-group list-group-flush"
      buttonName="list-group-item list-group-item-action"
      actions={[
        {
          name: 'about',
          type: LINK_BUTTON,
          label: trans('about', {}, 'platform'),
          target: props.path + '/about'
        },{
          name: 'registered',
          type: LINK_BUTTON,
          label: trans('my_events', {}, 'cursus'),
          target: props.path + '/registered'
        }, {
          name: 'public',
          type: LINK_BUTTON,
          label: trans('public_events', {}, 'cursus'),
          target: props.path + '/public'
        }, {
          name: 'all',
          type: LINK_BUTTON,
          label: trans('all_events', {}, 'cursus'),
          target: props.path + '/all',
          displayed: props.canEdit || props.canRegister
        }, {
          name: 'presences',
          type: LINK_BUTTON,
          label: (props.canEdit || props.canRegister) ? trans('presences', {}, 'cursus') : trans('my_presences', {}, 'cursus'),
          target: props.path + '/presences'
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

EventsMenu.propTypes = {
  canEdit: T.bool.isRequired,
  canRegister: T.bool.isRequired,

  // from menu
  path: T.string,
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  EventsMenu
}
