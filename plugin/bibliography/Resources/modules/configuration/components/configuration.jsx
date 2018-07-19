import React from 'react'

import {trans} from '#/main/core/translation'
import {
  RoutedPageContent
} from '#/main/core/layout/router'
import {PageContainer, PageHeader} from '#/main/core/layout/page'
import {ConfigurationForm} from '#/plugin/bibliography/configuration/components/form'

const BookReferenceConfiguration = () =>
  <PageContainer>
    <PageHeader title={trans('icap_book_reference_config', {}, 'icap_bibliography')}/>
    <RoutedPageContent
      routes={[
        {
          path: '/',
          component: ConfigurationForm,
          exact: true
        }
      ]}
    />
  </PageContainer>

export {
  BookReferenceConfiguration
}