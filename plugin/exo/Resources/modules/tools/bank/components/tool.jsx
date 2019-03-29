import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {getPlainText} from '#/main/app/data/types/html/utils'

import {getItems} from '#/plugin/exo/items'
import {ItemList} from '#/plugin/exo/items/components/list'

const BankTool = props =>
  <ToolPage>
    <ItemList
      name="questions"
      actions={(rows) => [
        /*{
         icon: 'fa fa-fw fa-copy',
         label: trans('duplicate'),
         action: (rows) => props.duplicateQuestions(rows, false)
         }, {
         icon: 'fa fa-fw fa-clone',
         label: trans('duplicate_model'),
         action: (rows) => props.duplicateQuestions(rows, true)
         },*/ {
          // TODO : checks if the current user has the rights to share to enable the action
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-share',
          label: trans('share', {}, 'actions'),
          callback: () => props.shareQuestions(rows)
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          callback: () => props.removeQuestions(rows),
          dangerous: true
        }
      ]}
    />
  </ToolPage>

BankTool.propTypes = {
  removeQuestions: T.func.isRequired,
  duplicateQuestions: T.func.isRequired,
  shareQuestions: T.func.isRequired
}

export {
  BankTool
}
