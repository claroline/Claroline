import React from 'react'

import {trans} from '#/main/core/translation'
import {
  RoutedPageContainer,
  RoutedPageContent
} from '#/main/core/layout/router'
import {PageHeader} from '#/main/core/layout/page'
import {ConfigurationForm} from '#/plugin/bibliography/configuration/components/form'

const BookReferenceConfiguration = () =>
  <RoutedPageContainer>
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
  </RoutedPageContainer>

export {
  BookReferenceConfiguration
}