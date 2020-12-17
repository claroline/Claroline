import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {PageFull} from '#/main/app/page/components/full'

import {ConfigurationForm} from '#/plugin/bibliography/configuration/components/form'

const BookReferenceConfiguration = () =>
  <PageFull
    title={trans('icap_book_reference_config', {}, 'icap_bibliography')}
  >
    <Routes
      routes={[
        {
          path: '/',
          component: ConfigurationForm,
          exact: true
        }
      ]}
    />
  </PageFull>

export {
  BookReferenceConfiguration
}
