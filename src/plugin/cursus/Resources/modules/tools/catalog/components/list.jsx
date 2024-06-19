import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ToolPage} from '#/main/core/tool'
import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/cursus/tools/catalog/store'
import {CourseList} from '#/plugin/cursus/course/components/list'

const CatalogList = (props) =>
  <ToolPage
    title={trans('catalog', {}, 'cursus')}
  >
    <CourseList
      path={props.path}
      name={selectors.LIST_NAME}
      url={['apiv2_cursus_course_list']}
    />
  </ToolPage>

CatalogList.propTypes = {
  path: T.string.isRequired
}

export {
  CatalogList
}
