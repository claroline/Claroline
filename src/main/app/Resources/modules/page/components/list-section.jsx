import React from 'react'
import {PageSection} from '#/main/app/page/components/section'

const PageListSection = (props) =>
  <PageSection
    className="flex-fill d-flex flex-column"
    size="full"
    flush={true}
    {...props}
  >
    {props.children}
  </PageSection>

export {
  PageListSection
}
