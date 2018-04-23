import React from 'react'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions.jsx'
import {t} from '#/main/core/translation'

const Action = () =>
  <PageActions>
    <PageAction
      type="callback"
      icon="fa fa-download"
      label={t('download')}
      callback={() => true}
    />
  </PageActions>

export {Action}
