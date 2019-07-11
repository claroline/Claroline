import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const WorkspacesMenu = (props) =>
  <MenuSection
    title={trans('parameters')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'parameters',
          type: LINK_BUTTON,
          label: trans('tools'),
          target: props.path+'/parameters'
        }
      ]}
    />
  </MenuSection>

WorkspacesMenu.propTypes = {
  path: T.string,
  creatable: T.bool.isRequired
}

export {
  WorkspacesMenu
}
