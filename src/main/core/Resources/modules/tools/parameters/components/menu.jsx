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
    title={trans('parameters', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'tools',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-tools',
          label: trans('tools'),
          target: props.path,
          exact: true
        }, {
          name: 'external',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-globe',
          label: trans('external', {}, 'integration'),
          target: props.path + '/external',
          displayed: false
        }, {
          name: 'tokens',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-coins',
          label: trans('tokens', {}, 'integration'),
          target: props.path + '/tokens'
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
