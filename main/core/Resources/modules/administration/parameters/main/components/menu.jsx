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
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'info',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-info',
          label: trans('information'),
          target: props.path+'/',
          exact: true
        }, {
          name: 'home',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-home',
          label: trans('home'),
          target: props.path+'/home'
        }, {
          name: 'i18n',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-language',
          label: trans('language'),
          target: props.path+'/i18n'
        }, {
          name: 'plugins',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-cubes',
          label: trans('plugins'),
          target: props.path+'/plugins'
        }, {
          name: 'maintenance',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-wrench',
          label: trans('maintenance'),
          target: props.path+'/maintenance'
        }, {
          name: 'archive',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-book',
          label: trans('archive'),
          target: props.path+'/archives'
        }
      ]}
    />
  </MenuSection>

ParametersMenu.propTypes = {
  path: T.string
}

export {
  ParametersMenu
}
