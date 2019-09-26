import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'


const BadgeMenu = (props) =>
  <MenuSection
    {...omit(props, 'path', 'isAdmin')}
    title={trans('open-badge', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'my-badges',
          label: trans('my_badges', {}, 'badge'),
          target: props.path+'/my-badges',
          type: LINK_BUTTON
        }, {
          name: 'all-badges',
          label: trans('all_badges', {}, 'badge'),
          target: props.path+'/badges',
          type: LINK_BUTTON
        }, {
          name: 'parameters',
          icon: 'fa fa-fw fa-cog',
          label: trans('parameters'),
          type: LINK_BUTTON,
          target: props.path+'/parameters',
          displayed: props.isAdmin
        }
      ]}
    />
  </MenuSection>

BadgeMenu.propTypes = {
  path: T.string,
  isAdmin: T.bool.isRequired
}

export {
  BadgeMenu
}
