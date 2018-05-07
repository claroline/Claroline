import React from 'react'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions.jsx'
import {t} from '#/main/core/translation'

const Action = () =>
  <PageActions>
    <PageAction
      id="history-action"
      icon="fa fa-books"
      label={t('history')}
      target="#"
      type="url"
    />
  </PageActions>

export {Action}
/*
const actions = {
  id: 'my-action',
  type: 'url',
  label: 'An awesome action',
  target: 'http://www.claroline.com'
}*/
