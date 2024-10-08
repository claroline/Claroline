import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/main/community/group/utils'
import {Group as GroupTypes} from '#/main/community/group/prop-types'
import {PageHeading} from '#/main/app/page/components/heading'
import {Thumbnail} from '#/main/app/components/thumbnail'

const Group = (props) =>
  <ToolPage
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('groups', {}, 'community'),
        target: `${props.path}/groups`
      }
    ].concat(props.group ? props.breadcrumb : [])}
    poster={get(props.group, 'poster')}
    title={trans('group_name', {name: get(props.group, 'name', trans('loading'))}, 'community')}
    description={get(props.group, 'meta.description')}
  >
    {isEmpty(props.group) &&
      <ContentLoader
        size="lg"
        description={trans('group_loading', {}, 'community')}
      />
    }

    {!isEmpty(props.group) &&
      <PageHeading
        size="md"
        icon={get(props.group, 'thumbnail') ?
          <Thumbnail
            size="xl"
            thumbnail={get(props.group, 'thumbnail')}
            name={get(props.group, 'name')}
            square={true}
          >
            <span className="fa fa-users" aria-hidden={true} />
          </Thumbnail> :
          undefined
        }
        title={get(props.group, 'name', trans('loading'))}
        primaryAction="edit"
        actions={!isEmpty(props.group) ? getActions([props.group], {
          add: () => props.reload(props.group.id),
          update: () => props.reload(props.group.id),
          delete: () => props.reload(props.group.id)
        }, props.path, props.currentUser) : []}
      />
    }

    {!isEmpty(props.group) && props.children}
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
