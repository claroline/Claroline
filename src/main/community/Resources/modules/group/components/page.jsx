import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/main/community/group/utils'
import {route} from '#/main/community/group/routing'
import {Group as GroupTypes} from '#/main/community/group/prop-types'

const Group = (props) =>
  <ToolPage
    className="group-page"
    meta={{
      title: trans('group_name', {name: get(props.group, 'name', trans('loading'))}, 'community'),
      description: get(props.group, 'meta.description')
    }}
    path={[
      {
        type: LINK_BUTTON,
        label: trans('groups', {}, 'community'),
        target: `${props.path}/groups`
      }, {
        type: LINK_BUTTON,
        label: get(props.group, 'name', trans('loading')),
        target: !isEmpty(props.group) ? route(props.group, props.path) : ''
      }
    ].concat(props.group ? props.breadcrumb : [])}
    subtitle={trans('group_name', {name: get(props.group, 'name', trans('loading'))}, 'community')}
    toolbar="edit | send-message | fullscreen more"
    poster={get(props.group, 'poster')}
    actions={!isEmpty(props.group) ? getActions([props.group], {
      add: props.reload,
      update: props.reload,
      delete: props.reload
    }, props.path, props.currentUser) : []}
  >
    {props.children}
  </ToolPage>

Group.propTypes = {
  path: T.string,
  breadcrumb: T.array,
  group: T.shape(
    GroupTypes.propTypes
  ),
  currentUser: T.object,
  children: T.any,
  reload: T.func
}

Group.defaultProps = {
  breadcrumb: []
}

const GroupPage = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(Group)

export {
  GroupPage
}
