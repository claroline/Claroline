import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const TrainingsMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('trainings', {}, 'tools')}
  >
    <Toolbar
      className="list-group list-group-flush"
      buttonName="list-group-item list-group-item-action"
      actions={[
        {
          name: 'catalog',
          type: LINK_BUTTON,
          label: trans('catalog', {}, 'cursus'),
          target: `${props.path}/catalog`
        }, {
          name: 'public',
          type: LINK_BUTTON,
          label: trans('public_events', {}, 'cursus'),
          target: props.path + '/events/public'
        }, {
          name: 'all',
          type: LINK_BUTTON,
          label: trans('all_events', {}, 'cursus'),
          target: props.path + '/events/all',
          displayed: props.authenticated && (props.canEdit || props.canRegister)
        }, {
          name: 'registered',
          type: LINK_BUTTON,
          label: trans('my_sessions', {}, 'cursus'),
          target: `${props.path}/registered`,
          displayed: props.authenticated
        }, {
          name: 'registered-events',
          type: LINK_BUTTON,
          label: trans('my_events', {}, 'cursus'),
          target: props.path + '/events/registered',
          displayed: props.authenticated
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

TrainingsMenu.propTypes = {
  path: T.string,
  canEdit: T.bool.isRequired,
  canRegister: T.bool.isRequired,
  authenticated: T.bool.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  TrainingsMenu
}
