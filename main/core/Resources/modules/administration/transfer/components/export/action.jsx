import React from 'react'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions.jsx'
import {t} from '#/main/core/translation'

const Action = () =>
  <PageActions>
    <PageAction
      id="execute-action"
      icon="fa fa-download"
      title={t('download')}
      action="#"
    />
  </PageActions>

export {Action}
