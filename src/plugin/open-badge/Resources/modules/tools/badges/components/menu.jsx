import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {constants as toolConstants} from '#/main/core/tool/constants'

const BadgeMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('badges', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'my-badges',
          label: trans('my_badges', {}, 'badge'),
          target: props.path+'/my-badges',
          type: LINK_BUTTON,
          displayed: props.contextType === toolConstants.TOOL_WORKSPACE && !get(props.workspace, 'meta.model')
        }, {
          name: 'all-badges',
          label: trans('all_badges', {}, 'badge'),
          target: props.path+'/badges',
          type: LINK_BUTTON
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

BadgeMenu.propTypes = {
  path: T.string,
  contextType: T.string,
  workspace: T.object,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  BadgeMenu
}
