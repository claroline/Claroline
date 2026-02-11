import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {PageContent} from '#/main/app/page'
import {ToolPage} from '#/main/core/tool'

import {selectors} from '#/plugin/home/tools/home/store'

const HomePageSkeleton = () =>
  <ToolPage>
    <PageContent className="placeholder-glow container-fluid mt-4 gap-4">
      <div className="row px-2 mb-4">
        <div className="col-12 d-flex">
          <span className="placeholder rounded-3 flex-fill" style={{minHeight: '14rem'}} />
        </div>
      </div>
      <div className="row px-2 mb-4">
        <div className="col-6 d-flex">
          <span className="placeholder rounded-3 flex-fill" style={{minHeight: '14rem'}} />
        </div>
        <div className="col-6 d-flex">
          <span className="placeholder rounded-3 flex-fill" style={{minHeight: '14rem'}} />
        </div>
      </div>
      <div className="row px-2 mb-4">
        <div className="col-4 d-flex">
          <span className="placeholder rounded-3 flex-fill" style={{minHeight: '14rem'}} />
        </div>
        <div className="col-8 d-flex">
          <span className="placeholder rounded-3 flex-fill" style={{minHeight: '14rem'}} />
        </div>
      </div>
      <div className="row px-2 mb-4">
        <div className="col-8 d-flex">
          <span className="placeholder rounded-3 flex-fill" style={{minHeight: '14rem'}} />
        </div>
        <div className="col-4 d-flex">
          <span className="placeholder rounded-3 flex-fill" style={{minHeight: '14rem'}} />
        </div>
      </div>
    </PageContent>
  </ToolPage>

const HomePage = ({children}) => {
  const currentTab = useSelector(selectors.currentTab)
  const title = useSelector(selectors.currentTabTitle)

  return (
    <ToolPage
      title={title}
    >
      <PageContent poster={get(currentTab, 'poster')}>
        {children}
      </PageContent>
    </ToolPage>
  )
}

HomePage.propTypes = {
  children: T.any
}

export {
  HomePage,
  HomePageSkeleton
}
