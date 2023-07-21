import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {route as resourceRoute} from '#/main/core/resource/routing'

const ShortcutMenu = props =>
  <MenuSection
    {...omit(props)}
    title={trans('shortcut', {}, 'resource')}
  >
    <Toolbar
      className="list-group list-group-flush"
      buttonName="list-group-item list-group-item-action"
      actions={[
        {
          name: 'open',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-share',
          label: trans('open-resource', {}, 'actions'),
          target: props.target ? resourceRoute(props.target) : null,
          displayed: !!props.target,
          exact: true
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

ShortcutMenu.propTypes = {
  target: T.object,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  ShortcutMenu
}
