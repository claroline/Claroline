import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const ParametersMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('main_settings', {}, 'tools')}
  >
    <Toolbar
      id="parameters-menu"
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'general',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-info',
          label: trans('general'),
          target: props.path+'/',
          exact: true
        }, {
          name: 'privacy',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-shield',
          label: trans('privacy'),
          target: props.path+'/privacy'
        }, {
          name: 'appearance',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-drafting-compass',
          label: trans('appearance'),
          target: props.path+'/appearance'
        }, {
          name: 'technical',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-wrench',
          label: trans('technical'),
          target: props.path+'/technical'
        }, {
          name: 'messages',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-comment-dots',
          label: trans('connection_messages'),
          target: props.path+'/messages'
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

ParametersMenu.propTypes = {
  path: T.string,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  ParametersMenu
}
