import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const EvaluationMenu = (props) =>
  <MenuSection
    {...omit(props, 'path', 'canEdit', 'contextType')}
    title={trans('evaluation', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'my-progression',
          type: LINK_BUTTON,
          label: trans('my_progression'),
          target: props.path+'/',
          exact: true,
          displayed: 'workspace' === props.contextType
        }, {
          name: 'users-progression',
          type: LINK_BUTTON,
          label: trans('users_progression', {}, 'evaluation'),
          target: props.path+'/users',
          displayed: props.canEdit
        }, {
          name: 'parameters',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-cog',
          label: trans('parameters'),
          target: props.path+'/parameters',
          displayed: props.canEdit && 'workspace' === props.contextType
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

EvaluationMenu.propTypes = {
  path: T.string,
  canEdit: T.bool.isRequired,
  contextType: T.string.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  EvaluationMenu
}
