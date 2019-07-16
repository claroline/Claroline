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
          name: 'parameters',
          type: LINK_BUTTON,
          label: trans('tools'),
          target: props.path
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
