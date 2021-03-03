import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HomePage} from '#/plugin/home/tools/home/containers/page'
import {Tab as TabTypes} from '#/plugin/home/prop-types'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CourseList} from '#/plugin/cursus/course/components/list'

const CatalogList = (props) =>
  <HomePage
    tabs={props.tabs}
    currentTab={props.currentTab}
    title={props.title}
  >
    <CourseList
      path={props.path}
      name={selectors.LIST_NAME}
    />
  </HomePage>

CatalogList.propTypes = {
  path: T.string.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  title: T.string.isRequired,
  currentTab: T.shape(
    TabTypes.propTypes
  )
}

export {
  CatalogList
}
