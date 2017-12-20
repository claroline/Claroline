import React from 'react'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions.jsx'
import {makeSaveAction} from '#/main/core/data/form/containers/form-save.jsx'

const ImportAction = makeSaveAction('import', () => {
  return {
    create: ['apiv2_transfer_execute'],
    update: ['apiv2_transfer_execute']
  }
})(PageAction)

const Action = () =>
  <PageActions>
    <ImportAction/>
  </PageActions>

export {Action}
