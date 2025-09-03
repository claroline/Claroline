import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'
import {PageListSection} from '#/main/app/page'

import {constants} from '#/main/community/constants'
import {selectors} from '#/main/community/tools/community/role/store/selectors'
import {RoleList as BaseRoleList} from '#/main/community/role/components/list'
import {MODAL_ROLE_FORM} from '#/main/community/role/modals/form'

const RoleList = props =>
  <ToolPage
    title={trans('roles', {}, 'community')}
  >
    <PageListSection
      poster={props.poster}
      title={trans('roles', {}, 'community')}
      addAction={{
        name: 'add',
        type: MODAL_BUTTON,
        label: trans('add_role', {}, 'actions'),
        icon: 'fa fa-fw fa-plus',
        modal: [MODAL_ROLE_FORM, {
          isNew: true,
          role: {
            type: 'workspace' === props.contextType ? props.contextType : constants.ROLE_PLATFORM,
            workspace: props.contextData
          },
          onSave: props.invalidateList
        }],
        primary: true,
        displayed: props.canCreate
      }}
    >
      <BaseRoleList
        className="mb-5"
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={['apiv2_role_list', {
          roleType: 'workspace' === props.contextType ? props.contextType : constants.ROLE_PLATFORM,
          contextId: get(props.contextData, 'id')}
        ]}
      />
    </PageListSection>
  </ToolPage>

RoleList.propTypes = {
  path: T.string,
  poster: T.string,
  contextType: T.string.isRequired,
  contextData: T.object,
  canCreate: T.bool.isRequired,
  invalidateList: T.func.isRequired
}

export {
  RoleList
}
