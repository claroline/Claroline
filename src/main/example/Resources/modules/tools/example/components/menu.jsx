import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const ExampleMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('example', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'crud',
          type: LINK_BUTTON,
          label: 'Simple CRUD',
          target: props.path+'/crud'
        }, {
          name: 'forms',
          type: LINK_BUTTON,
          label: 'Forms',
          target: props.path+'/forms'
        }, {
          name: 'components',
          type: LINK_BUTTON,
          label: 'Components',
          target: props.path+'/components'
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

ExampleMenu.propTypes = {
  path: T.string,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  ExampleMenu
}
