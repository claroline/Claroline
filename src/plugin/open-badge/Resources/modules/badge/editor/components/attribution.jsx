import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {selectors as toolSelectors} from '#/main/core/tool'
import {useDispatch, useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'
import {actions} from '#/plugin/open-badge/badge/editor/store'

const isAutoIssuing = (badge) => badge._autoIssuing || !isEmpty(badge.rules)

const BadgeEditorAttribution = () => {
  const dispatch = useDispatch()

  const contextType = useSelector(toolSelectors.contextType)
  const contextId = useSelector(toolSelectors.contextId)

  return (
    <EditorPage
      title={trans('award_rules', {}, 'badge')}
      definition={[
        {
          icon: 'fa fa-fw fa-certificate',
          title: trans('award_rules', {}, 'badge'),
          fields: [
            {
              name: 'issuer',
              label: trans('issuer', {}, 'badge'),
              help: trans('issuer_help', {}, 'badge'),
              type: 'organization',
              required: true,
              options: {multiple: false}
            }, {
              name: 'issuingPeer',
              type: 'boolean',
              label: trans('enable_manual_issuing', {}, 'badge')
            }, {
              name: 'notifyGrant',
              type: 'boolean',
              label: trans('notify_grant', {}, 'badge')
            }, {
              name: '_autoIssuing',
              type: 'boolean',
              label: trans('enable_auto_issuing', {}, 'badge'),
              help: [
                trans('enable_auto_issuing_help', {}, 'badge'),
                trans('enable_auto_issuing_help_manual', {}, 'badge')
              ],
              calculated: isAutoIssuing,
              onChange: (enabled) => {
                if (!enabled) {
                  dispatch(actions.update([], 'rules'))
                }
              },
              linked: [
                {
                  name: 'rules',
                  label: trans('rules', {}, 'badge'),
                  type: 'badge-rules',
                  displayed: isAutoIssuing,
                  required: true,
                  options: {
                    contextType: contextType,
                    contextId: contextId
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

export {
  BadgeEditorAttribution
}
