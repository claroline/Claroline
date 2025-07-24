import React from 'react'
import {useSelector} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl'
import {MediaInfo} from '#/main/app/components/media-info'
import {PageContent, PageSection} from '#/main/app/page'
import {ResourcePage, selectors as resourceSelectors} from '#/main/core/resource'

import {YouTubePlayer} from '#/integration/youtube/components/player'
import {Video as VideoTypes} from '#/integration/youtube/prop-types'

const VideoPlayer = props => {
  let lastSaved = 0

  const embedded = useSelector(resourceSelectors.embedded)
  const showHeader = useSelector(resourceSelectors.showHeader)
  const resourceNode = useSelector(resourceSelectors.resourceNode)

  return (
    <ResourcePage>
      <PageContent>
        <PageSection size="xl" flush={embedded} className={classes({
          'mt-5': showHeader,
          'mb-5': !embedded
        })}>
          <YouTubePlayer
            className="rounded-4"
            video={props.video}
            progression={props.progression}
            onPlay={(currentTime, duration) => {
              if (props.currentUser) {
                props.updateProgression(props.video.id, currentTime, duration)
              }
            }}
            onPause={(currentTime, duration) => {
              if (props.currentUser) {
                props.updateProgression(props.video.id, currentTime, duration)
              }
            }}
            onTimeUpdate={(currentTime, duration) => {
              if (props.currentUser) {
                const interval = Math.round((duration / 100) * 5)
                const roundedTime = Math.round(currentTime)
                if (roundedTime > lastSaved && 0 === roundedTime % interval) {
                  props.updateProgression(props.video.id, currentTime, duration)
                  lastSaved = roundedTime
                }
              }
            }}
          />

          <MediaInfo
            title={resourceNode.name}
            description={get(resourceNode, 'meta.descriptionHtml')}
            embedded={embedded}
            meta={(
              <>
                {get(resourceNode, 'estimatedDuration') &&
                  <>
                    <div role="presentation" aria-label={trans('estimated_duration')}>
                      <span className="fa far fa-clock me-2" aria-hidden={true} />
                      {get(resourceNode, 'estimatedDuration') + ' ' + trans('minutes')}
                    </div>
                    <span role="presentation">-</span>
                  </>
                }
                {transChoice('display_views', get(resourceNode, 'meta.views', 0), {count: get(resourceNode, 'meta.views', 0)})}
              </>
            )}
          />
        </PageSection>
      </PageContent>
    </ResourcePage>
  )
}

VideoPlayer.propTypes = {
  video: T.shape( VideoTypes.propTypes ).isRequired,
  progression: T.number,
  updateProgression: T.func.isRequired,
  currentUser: T.object
}

export {
  VideoPlayer
}
