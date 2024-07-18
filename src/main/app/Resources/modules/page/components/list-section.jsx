import React from 'react'
import {PageSection} from '#/main/app/page/components/section'

const PageListSection = (props) =>
  <PageSection
    className="p-4 flex-fill"
    size="full"
    {...props}
  >
    {props.children}
  </PageSection>

export {
  PageListSection
}
