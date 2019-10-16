import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const TechnicalMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('technical_settings', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'domain',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-internet-explorer',
          label: trans('internet'),
          target: props.path+'/domain'
        }, {
          name: 'limits',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-database',
          label: trans('limits'),
          target: props.path+'/limits'
        }, {
          name: 'security',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-user-shield',
          label: trans('security'),
          target: props.path+'/security'
        }, {
          name: 'mailing',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-envelope',
          label: trans('email'),
          target: props.path+'/mailing'
        }, {
          name: 'sessions',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-sign-out-alt',
          label: trans('sessions'),
          target: props.path+'/sessions'
        }, {
          name: 'javascripts',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-file',
          label: trans('javascripts'),
          target: props.path+'/javascripts'
        }
      ]}
    />
  </MenuSection>

TechnicalMenu.propTypes = {
  path: T.string
}

export {
  TechnicalMenu
}
