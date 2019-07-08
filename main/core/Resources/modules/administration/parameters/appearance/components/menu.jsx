import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const AppearanceMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('appearance_settings', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'layout',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-layer-group',
          label: trans('layout'),
          target: props.path+'/layout',
          exact: true
        }, {
          name: 'icon',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-icons',
          label: trans('icons'),
          target: props.path+'/icons'
        }
      ]}
    />
  </MenuSection>

AppearanceMenu.propTypes = {
  path: T.string
}

export {
  AppearanceMenu
}
