import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Thumbnail} from '#/main/app/components/thumbnail'
import {ToolPage} from '#/main/core/tool'
import {PageContent, PageHeading, PageToolbar, PageToolbarSkeleton, PageHeadingSkeleton} from '#/main/app/page'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/main/community/group/utils'
import {Group as GroupTypes} from '#/main/community/group/prop-types'

const Group = (props) =>
  <ToolPage
    title={trans('group_name', {name: get(props.group, 'name', trans('loading'))}, 'community')}
    description={get(props.group, 'meta.description')}
  >
    {isEmpty(props.group) &&
      <PageContent className="placeholder-glow">
        <PageToolbarSkeleton toolbar="edit more" />
        <PageHeadingSkeleton
          icon={true}
          description={true}
        />
      </PageContent>
    }

    {!isEmpty(props.group) &&
      <PageContent poster={get(props.group, 'poster')}>
        <PageToolbar
          toolbar="edit send-message more"
          actions={getActions([props.group], {
            add: () => props.reload(props.group.id),
            update: () => props.reload(props.group.id),
            delete: () => props.reload(props.group.id)
          }, props.path, props.currentUser)}
        />
        <PageHeading
          icon={
            <Thumbnail
              size="lg"
              thumbnail={get(props.group, 'thumbnail')}
              name={get(props.group, 'name')}
              square={true}
              border={true}
            />
          }
          title={get(props.group, 'name', trans('loading'))}
          description={get(props.group, 'meta.description')}
        />

        {props.children}
      </PageContent>
    }
  </ToolPage>

Group.propTypes = {
  path: T.string,
  group: T.shape(
    GroupTypes.propTypes
  ),
  currentUser: T.object,
  children: T.any,
  reload: T.func
}

const GroupPage = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(Group)

export {
  GroupPage
}
