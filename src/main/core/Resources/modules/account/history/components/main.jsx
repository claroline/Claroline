import React from 'react'
import {useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors as editorSelectors} from '#/main/community/user/editor'
import {ContextHistory} from '#/main/app/context/components/history'

const AccountHistory = () => {
  const authenticatedUserId = useSelector(securitySelectors.currentUserId)
  const user = useSelector(editorSelectors.user)

  let me = false
  if (user && user.id === authenticatedUserId) {
    me = true
  }

  return (
    <EditorPage
      title={trans('history', {}, 'history')}
      help={trans('history_desc', {}, 'history')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'userPreferences.history',
              type: 'boolean',
              label: trans('enable_history', {}, 'history'),
              help: trans('enable_history_help', {}, 'history'),
              calculated: () => true,
              disabled: true
            }
          ]
        }
      ]}
    >
      <hr className="m-0" aria-hidden="true" />

      {me &&
        <ContextHistory size="sm" delete={true} />
      }

      {!isEmpty(user) && !me &&
        <div className="text-center" role="presentation">
          <p className="lead mb-1">{trans('history_unavailable', {}, 'history')}</p>
          <p className="mb-0 text-body-secondary">{trans('history_unavailable_help', {}, 'history')}</p>
        </div>
      }
    </EditorPage>
  )
}

export {
  AccountHistory
}
