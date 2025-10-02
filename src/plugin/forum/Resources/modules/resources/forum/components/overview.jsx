import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {PageSection} from '#/main/app/page'
import {ButtonSticky} from '#/main/app/button'

import {ResourceOverview, selectors as resourceSelectors} from '#/main/core/resource'

import {actions, selectors} from '#/plugin/forum/resources/forum/store'
import {Subjects} from '#/plugin/forum/resources/forum/components/subjects'
import {MODAL_SUBJECT} from '#/plugin/forum/resources/forum/modals/subject'

const ForumOverview = () => {
  const dispatch = useDispatch()
  const history = useHistory()

  const resourcePath = useSelector(resourceSelectors.path)
  const resourceNode = useSelector(resourceSelectors.resourceNode)
  const showHeader = useSelector(resourceSelectors.showHeader)
  const embedded = useSelector(resourceSelectors.embedded)

  const forumId = useSelector(selectors.forumId)

  return (
    <ResourceOverview>
      <PageSection
        className={classes({
          'pt-5': (!embedded || showHeader) && !get(resourceNode, 'meta.description')
        })}
      >
        <Subjects
          className="mb-5"
        />

        {hasPermission('contribute', resourceNode) &&
          <ButtonSticky
            {...{
              name: 'create-subject',
              label: trans('add_subject', {}, 'actions'),
              type: MODAL_BUTTON,
              modal: [MODAL_SUBJECT, {
                forumId: forumId,
                onSave: (subject) => {
                  dispatch(actions.invalidateSubjects())
                  history.push(`${resourcePath}/subjects/${subject.id}`)
                }
              }]
            }}
          />
        }
      </PageSection>
    </ResourceOverview>
  )
}

export {
  ForumOverview
}
