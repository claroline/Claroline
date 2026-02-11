import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {actions as fetchActions} from '#/main/app/api/fetch'
import {PageContent, PageSection} from '#/main/app/page'
import {selectors as contextSelectors} from '#/main/app/context'
import {selectors as toolSelectors, ToolPage} from '#/main/core/tool'
import {ContentErrorNotFound} from '#/main/app/content/error/components/not-found'
import {ContentErrorPublication} from '#/main/app/content/error/components/publication'
import {ContentErrorDates} from '#/main/app/content/error/components/dates'
import {ContentErrorRights} from '#/main/app/content/error/components/rights'
import {ContentErrorCode} from '#/main/app/content/error/components/code'
import {ContentErrorUnknown} from '#/main/app/content/error/components/unknown'

import {actions, selectors} from '#/main/evaluation/sequence/store'
import {SequencePage} from '#/main/evaluation/sequence/components/page'

/**
 * A component displayed when there is an error while opening a sequence.
 */
const SequenceError = ({code, additional}) => {
  const dispatch = useDispatch()

  const contextPath = useSelector(contextSelectors.path)
  const contactEmail = useSelector(contextSelectors.contactEmail)
  const toolPath = useSelector(toolSelectors.path)
  const toolData = useSelector(toolSelectors.tool)
  const sequence = useSelector(selectors.sequence)
  const sequenceName = useSelector(selectors.name)

  const backAction = {
    type: LINK_BUTTON,
    label: trans('back_home', {}, 'actions'),
    target: contextPath,
    exact: true
  }

  const browseAction = {
    type: LINK_BUTTON,
    label: trans('browse-sequences', {}, 'actions'),
    target: toolPath,
    displayed: hasPermission('open', toolData),
    exact: true
  }

  let errorComponent
  switch (code) {
    case 'NOT_FOUND':
      errorComponent = (
        <ContentErrorNotFound primaryAction={browseAction} backAction={backAction} contactEmail={contactEmail} />
      )
      break
    case 'NO_RIGHTS':
      errorComponent = (
        <ContentErrorRights
          contentName={sequenceName}
          primaryAction={browseAction}
          backAction={backAction}
          contactEmail={contactEmail}
          onLogin={() => dispatch(fetchActions.invalidate(selectors.STORE_NAME))}
          onRegister={() => dispatch(fetchActions.invalidate(selectors.STORE_NAME))}
        />
      )
      break
    case 'NOT_PUBLISHED':
      errorComponent = (
        <ContentErrorPublication
          contentName={sequenceName}
          primaryAction={browseAction}
          backAction={backAction}
          contactEmail={contactEmail}
          archived={additional.archived}
        />
      )
      break
    case 'INVALID_DATES':
      errorComponent = (
        <ContentErrorDates
          contentName={sequenceName}
          primaryAction={browseAction}
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
          contentName={sequenceName}
          backAction={backAction}
          contactEmail={contactEmail}
          submitAccessCode={(code) => dispatch(actions.checkAccessCode(sequence, code))}
        />
      )
      break
    case 'UNKNOWN_ERROR':
      errorComponent = (
        <ContentErrorUnknown primaryAction={browseAction} backAction={backAction} contactEmail={contactEmail} />
      )
      break
  }

  return createElement(!isEmpty(sequence) ? SequencePage : ToolPage, null,
    <PageContent className="d-flex flex-column" poster={get(sequence, 'poster')}>
      <PageSection className="py-5 my-auto">
        {errorComponent}
      </PageSection>
    </PageContent>
  )
}

SequenceError.propTypes = {
  code: T.string.isRequired,
  message: T.string,
  additional: T.any
}

export {
  SequenceError
}
