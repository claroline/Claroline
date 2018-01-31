import React from 'react'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

const Action = () =>
  <PageActions>
    <FormPageActionsContainer
      formName="import"
      target={['apiv2_transfer_execute']}
      opened={true}
    />
  </PageActions>

export {
  Action
}
