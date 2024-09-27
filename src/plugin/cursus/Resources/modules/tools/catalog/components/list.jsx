import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CourseList} from '#/plugin/cursus/course/components/list'

import {ToolPage} from '#/main/core/tool'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {withReducer} from '#/main/app/store/components/withReducer'
import {reducer, selectors} from '#/plugin/cursus/tools/catalog/store'
import {PageListSection} from '#/main/app/page'

const CatalogListComponent = (props) =>
  <ToolPage
    title={trans('catalog', {}, 'cursus')}
  >
    <PageListSection>
      <CourseList
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={['apiv2_cursus_course_list_public']}
      />
    </PageListSection>
  </ToolPage>

CatalogListComponent.propTypes = {
  path: T.string.isRequired
}

const CatalogList = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    })
  )(CatalogListComponent)
)

export {
  CatalogList
}
