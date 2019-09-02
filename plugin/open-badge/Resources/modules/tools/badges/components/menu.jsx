import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

import {isAdmin as userIsAdmin} from '#/main/app/security/permissions'
import {currentUser} from '#/main/app/security'

const BadgeMenu = (props) =>
  <MenuSection
    {...omit(props, 'path', 'creatable')}
    title={trans('badges', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          icon: 'fa fa-user',
          label: trans('my_badges', {}, 'openbadge'),
          target: props.path+'/my-badges',
          type: LINK_BUTTON,
          displayed: props.currentContext.type === 'desktop'
        }, {
          icon: 'fa fa-book',
          label: trans('badges', {}, 'openbadge'),
          target: props.path+'/badges',
          type: LINK_BUTTON,
          displayed: props.currentContext.type !== 'profile'
        }, {
          icon: 'fa fa-cog',
          label: trans('parameters'),
          type: LINK_BUTTON,
          target: props.path+'/parameters',
          onlyIcon: true,
          displayed: userIsAdmin(currentUser())
        }, {
          icon: 'fa fa-book',
          label: trans('profile'),
          type: LINK_BUTTON,
          target: props.path+'/profile/:id',
          displayed: props.currentContext.type === 'profile'
        }
      ]}
    />
  </MenuSection>

BadgeMenu.propTypes = {
  path: T.string,
  currentUser: T.object.isRequired,
  authenticated: T.bool.isRequired,
  creatable: T.bool.isRequired,
  currentContext: T.object.isRequired
}

export {
  BadgeMenu
}
