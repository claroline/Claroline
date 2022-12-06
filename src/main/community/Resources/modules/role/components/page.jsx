import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {route} from '#/main/community/role/routing'
import {getActions} from '#/main/community/role/utils'
import {Role as RoleTypes} from '#/main/community/role/prop-types'

const Role = (props) =>
  <ToolPage
    className="role-page"
    meta={{
      title: trans('role_name', {name: trans(get(props.role, 'translationKey', 'loading'))}, 'community'),
      description: get(props.role, 'meta.description')
    }}
    path={[
      {
        type: LINK_BUTTON,
        label: trans('roles', {}, 'community'),
        target: `${props.path}/roles`
      }, {
        type: LINK_BUTTON,
        label: trans(get(props.role, 'translationKey', 'loading')),
        target: props.role ? route(props.role, props.path) : ''
      }
    ].concat(!isEmpty(props.role) ? props.breadcrumb : [])}
    subtitle={trans('role_name', {name: trans(get(props.role, 'translationKey', 'loading'))}, 'community')}
    toolbar="edit | fullscreen more"
    actions={!isEmpty(props.role) ? getActions([props.role], {
      add: props.reload,
      update: props.reload,
      delete: props.reload
    }, props.path, props.currentUser) : []}
  >
    {props.children}
  </ToolPage>

Role.propTypes = {
  path: T.string,
  breadcrumb: T.array,
  role: T.shape(
    RoleTypes.propTypes
  ),
  currentUser: T.object,
  children: T.any,
  reload: T.func
}

Role.defaultProps = {
  breadcrumb: []
}

const RolePage = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(Role)

export {
  RolePage
}
