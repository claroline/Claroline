import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {UserList} from '#/main/community/user/components/list'
import {selectors} from '#/main/community/tools/community/user/store/selectors'

const CommunityEditorReported = () =>
  <EditorPage
    title={trans('user_reported', {}, 'community')}
    help={trans('user_reported_help', {}, 'community')}
  >
    <UserList
      name={selectors.REPORT_LIST_NAME}
      url={['apiv2_user_list_reported']}
    />
  </EditorPage>

export {
  CommunityEditorReported
}
