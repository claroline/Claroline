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
      className="list-group"
      buttonName="list-group-item"
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
          displayed: props.canEdit
        }, {
          name: 'registered',
          type: LINK_BUTTON,
          label: trans('my_courses', {}, 'cursus'),
          target: `${props.path}/registered`
        }, {
          name: 'registered-events',
          type: LINK_BUTTON,
          label: trans('my_events', {}, 'cursus'),
          target: props.path + '/events/registered'
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

TrainingsMenu.propTypes = {
  path: T.string,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  TrainingsMenu
}
