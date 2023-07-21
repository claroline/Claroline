import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const LogsMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('logs', {}, 'tools')}
  >
    <Toolbar
      className="list-group list-group-flush"
      buttonName="list-group-item list-group-item-action"
      actions={[
        {
          name: 'logs',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-shield',
          label: trans('security'),
          target: props.path + '/security'
        }, {
          name: 'message',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-envelope',
          label: trans('message'),
          target: props.path + '/message'
        }, {
          name: 'functional',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-cogs',
          label: trans('functional'),
          target: props.path + '/functional'
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

LogsMenu.propTypes = {
  path: T.string,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  LogsMenu
}
