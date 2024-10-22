import React, { useState } from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {UserList} from '#/main/community/user/components/list'
import {selectors} from '#/main/community/tools/community/user/store/selectors'


const CommunityEditorReported = () => {
  const [isChecked, setIsChecked] = useState(false)

  return (
    <EditorPage
      title={trans('reporting', {}, 'editor')}
      help={trans('reporting_help', {}, 'editor')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'report_options',
              type: 'boolean',
              label: trans('report_options', {}, 'editor'),
              help: trans('report_options_help', {}, 'editor'),
              onChange: (enabled) => setIsChecked(enabled),
              linked: [
                {
                  name: 'notify_users',
                  type: 'user',
                  label: trans('notify_users', {}, 'editor'),
                  required: true,
                  displayed: isChecked,
                  options: {
                    picker: {
                      url: ['apiv2_user_list']
                    }
                  }
                }
              ]
            }
          ]
        }, {
          title: trans('user_reported', {}, 'editor'),
          primary: true,
          fields: [
            {
              name: 'report',
              label: trans('user_reported_help', {}, 'editor'),
              render: () => (
                <UserList
                  name={selectors.REPORT_LIST_NAME}
                  url={['apiv2_user_list_reported']}
                />
              )
            }
          ]
        }
      ]}
    />
  )
}


export {
  CommunityEditorReported
}
