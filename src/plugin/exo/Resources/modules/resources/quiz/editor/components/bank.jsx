import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans, transChoice} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {ContentTitle} from '#/main/app/content/components/title'

import {selectors} from '#/plugin/exo/resources/quiz/editor/store/selectors'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_USERS} from '#/main/community/modals/users'

import {ItemList} from '#/plugin/exo/items/components/list'
import {MODAL_ITEM_SHARING} from '#/plugin/exo/items/modals/sharing'

const EditorBank = (props) =>
  <Fragment>
    <ContentTitle
      level={3}
      displayLevel={2}
      title={trans('questions_bank', {}, 'quiz')}
    />

    <ItemList
      name={selectors.BANK_NAME}
      delete={{
        url: ['apiv2_quiz_questions_delete_bulk'],
        displayed: (rows) => -1 !== rows.findIndex(row => hasPermission('delete', row))
      }}
      actions={(rows) => [
        {
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-share',
          label: trans('share', {}, 'actions'),
          displayed: -1 !== rows.findIndex(row => hasPermission('edit', row)),
          modal: [MODAL_USERS, {
            icon: 'fa fa-fw fa-share',
            title: transChoice('share_items', rows.length, {count: rows.length}, 'quiz'),
            selectAction: (selected) => ({
              type: MODAL_BUTTON,
              modal: [MODAL_ITEM_SHARING, {
                questions: rows,
                users: selected,
                handleShare: (adminRights) => props.shareQuestions(rows, selected, adminRights)
              }]
            })
          }]
        }
      ]}
    />
  </Fragment>

EditorBank.propTypes = {
  shareQuestions: T.func.isRequired
}

export {
  EditorBank
}
