import React from 'react'
import {PropTypes as T} from 'prop-types'

//import {trans} from '#/main/app/intl/translation'

//import {HomePage} from '#/plugin/home/tools/home/containers/page'
import {Tab as TabTypes} from '#/plugin/home/prop-types'

import {CatalogMain} from '#/plugin/cursus/tools/trainings/catalog/containers/main'

const CatalogTab = props =>
  <CatalogMain path={props.path} />
  /*<HomePage
    tabs={props.tabs}
    currentTab={props.currentTab}
    currentTabTitle={props.currentTabTitle}
  >
    Catalog
  </HomePage>*/

CatalogTab.propTypes = {
  path: T.string.isRequired,
  currentContext: T.object,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTabTitle: T.string.isRequired,
  currentTab: T.shape(
    TabTypes.propTypes
  )
}

export {
  CatalogTab
}
