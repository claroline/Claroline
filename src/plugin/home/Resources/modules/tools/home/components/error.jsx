import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {PageContent, PageSection} from '#/main/app/page'
import {selectors as toolSelectors, ToolPage} from '#/main/core/tool'
import {ContentErrorNotFound} from '#/main/app/content/error/components/not-found'
import {ContentErrorDates} from '#/main/app/content/error/components/dates'
import {ContentErrorRights} from '#/main/app/content/error/components/rights'
import {ContentErrorCode} from '#/main/app/content/error/components/code'
import {ContentErrorUnknown} from '#/main/app/content/error/components/unknown'

import {actions, selectors} from '#/plugin/home/tools/home/store'
import {HomePage} from '#/plugin/home/tools/home/components/page'
import {selectors as contextSelectors} from '#/main/app/context'

/**
 * A component displayed when there is an error while opening a home tab.
 */
const HomeError = ({code, additional}) => {
  const dispatch = useDispatch()

  const contactEmail = useSelector(contextSelectors.contactEmail)
  const toolPath = useSelector(toolSelectors.path)
  const homeTab = useSelector(selectors.currentTab)
  const homeTabName = useSelector(selectors.currentTabTitle)

  const backAction = {
    type: LINK_BUTTON,
    label: trans('back_home', {}, 'actions'),
    target: toolPath,
    exact: true
  }

  let errorComponent
  switch (code) {
    case 'NOT_FOUND':
      errorComponent = (
        <ContentErrorNotFound backAction={backAction} contactEmail={contactEmail} />
      )
      break
    case 'NO_RIGHTS':
      errorComponent = (
        <ContentErrorRights
          contentName={homeTabName}
          backAction={backAction}
          contactEmail={contactEmail}
          onLogin={() => dispatch(actions.setTabLoaded(false))}
          onRegister={() => dispatch(actions.setTabLoaded(false))}
        />
      )
      break
    case 'INVALID_DATES':
      errorComponent = (
        <ContentErrorDates
          contentName={homeTabName}
          backAction={backAction}
          contactEmail={contactEmail}
          startDate={additional.startDate}
          endDate={additional.endDate}
        />
      )
      break
    case 'ACCESS_CODE':
      errorComponent = (
        <ContentErrorCode
          contentName={homeTabName}
          backAction={backAction}
          submitAccessCode={(code) => dispatch(actions.checkAccessCode(homeTab, code))}
          contactEmail={contactEmail}
        />
      )
      break
    case 'UNKNOWN_ERROR':
      errorComponent = (
        <ContentErrorUnknown backAction={backAction} contactEmail={contactEmail} />
      )
      break
  }

  return createElement(!isEmpty(homeTab) ? HomePage : ToolPage, null,
    <PageContent className="d-flex flex-column" poster={get(homeTab, 'poster')}>
      <PageSection className="py-5 my-auto">
        {errorComponent}
      </PageSection>
    </PageContent>
  )
}

HomeError.propTypes = {
  code: T.string.isRequired,
  message: T.string,
  additional: T.any
}

export {
  HomeError
}
