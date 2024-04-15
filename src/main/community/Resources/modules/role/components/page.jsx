import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/main/community/role/utils'
import {Role as RoleTypes} from '#/main/community/role/prop-types'
import {ContentLoader} from '#/main/app/content/components/loader'

const Role = (props) =>
  <ToolPage
    className="role-page"
    meta={{
      title: trans('role_name', {name: trans(get(props.role, 'translationKey', 'loading'))}, 'community'),
      description: get(props.role, 'meta.description')
    }}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('roles', {}, 'community'),
        target: `${props.path}/roles`
      }
    ].concat(!isEmpty(props.role) ? props.breadcrumb : [])}
    title={trans(get(props.role, 'translationKey', 'loading'))}
    primaryAction="view-as"
    toolbar="edit more"
    actions={!isEmpty(props.role) ? getActions([props.role], {
      add: () => props.reload(props.role.id),
      update: () => props.reload(props.role.id),
      delete: () => props.reload(props.role.id)
    }, props.path, props.currentUser) : []}
  >
    {isEmpty(props.role) &&
      <ContentLoader
        size="lg"
        description={trans('role_loading', {}, 'community')}
      />
    }

    {!isEmpty(props.role) && props.children}
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
